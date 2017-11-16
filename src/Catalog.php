<?php

namespace Domatskiy\Exchange1C;

use Domatskiy\Exchange1C\Events;
use Domatskiy\ExchangeCML\TmpTable\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class Catalog
{
    const SESSION_KEY = 'CMLIMPORT';
    public static function checkauth(Request $request)
    {
        $config_login = config('1c_ut_exchange.login', 'admin');
        $config_password = config('1c_ut_exchange.password', 'password');

        if($_SERVER['PHP_AUTH_USER'] == $config_login && $_SERVER['PHP_AUTH_PW'] == $config_password)
        {
			$request->session()->save();

            $response = "success\n";
            $response .= "laravel_session\n";
            $response .= session()->getId()."\n";
            $response .= "timestamp=".time();

            \Session::put(self::SESSION_KEY.'_auth', $config_login);
        }
        else
        {
            $response = "failure\n";
        }
		
		return $response;
    }

    public static function init(Request $request)
    {
        $config_login = config('1c_ut_exchange.login', 'admin');
        $user = \Session::get(self::SESSION_KEY.'_auth', null);

        if(!$user || $user != $config_login)
            throw new \Exception('auth error');

        $zip = config('1c_ut_exchange.zip', true);
        $file_part = config('1c_ut_exchange.file_part', 0);

        $zip_enable = function_exists("zip_open") && $zip;
		\Log::debug('exchange_1c: zip_open='.function_exists("zip_open"));

        $arParams = [
            "zip" => $zip_enable,
            "TEMP_DIR" => '/1c_exchange_tmp',
            "IMPORT" => array(
                "STEP" => 0,
            ),
            "SECTION_MAP" => false,
            "PRICES_MAP" => false,
            ];

        \Session::put(self::SESSION_KEY, $arParams);
		
		session()->save();
		
        #session(self::SESSION_KEY, $arParams);
        #var_dump(\Session::get(self::SESSION_KEY));

        $disk = config('1c_ut_exchange.disk', 'local');
        $dir = config('1c_ut_exchange.import_dir', '1c_exchange');
        $storage = \Storage::disk($disk);

        if($storage->exists($dir))
            $storage->deleteDirectory($dir);

        $storage->makeDirectory($dir);

        $response = "zip=".($zip_enable ? "yes": "no")."\n";
        $response .= "file_limit=".$file_part;

		return $response;		
    }

    public static function file(Request $request)
    {
        $config_login = config('1c_ut_exchange.login', 'admin');
        $user = \Session::get(self::SESSION_KEY.'_auth', null);

        if(!$user || $user != $config_login)
            throw new \Exception('auth error');

        $filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $request->get('filename'));
        $filename = trim(str_replace("\\", "/", trim($filename)), "/");

        if(!$filename)
            throw new \Exception('not correct filename');

        $CML2_IMPORT = \Session::get(self::SESSION_KEY, array());

        $disk = config('1c_ut_exchange.disk', 'local');
        $dir = config('1c_ut_exchange.import_dir', '1c_exchange');
        $file = str_replace('//', '/', $dir.'/'.$filename);
        #$abs_path = config('filesystems.disks.'.$disk.'.root', '');

        $file = '/'.trim($file, '/');
        $dir = trim($dir, '/');

        $storage = \Storage::disk($disk);

        if(!$storage->has($dir))
            throw new \ErrorException($dir.' not exits');

        $data = file_get_contents('php://input');
        $len = mb_strlen($data);

        if(isset($data) && $data !== false)
        {
            $result = $storage->append($file, $data, '');
            #file_put_contents($abs_path.$file , $data);

            \Log::debug('exchange_1c: import, $len='.$len.'/'.$result);

            if($CML2_IMPORT["zip"])
                $CML2_IMPORT["zip"] = $file;

            \Session::put(self::SESSION_KEY, $CML2_IMPORT);

            return "success\n";
        }

        return "failure\n";
    }

    public static function import (Request $request)
    {
        $config_login = config('1c_ut_exchange.login', 'admin');
        $user = \Session::get(self::SESSION_KEY.'_auth', null);

        if(!$user || $user != $config_login)
            throw new \Exception('auth error');

        $dir = config('1c_ut_exchange.import_dir', '1c_exchange');
        $disk = config('1c_ut_exchange.disk', 'local');
        $abs_path = config('filesystems.disks.'.$disk.'.root', '');
        $filename = $request->get('filename');

        $dir = trim($dir, '/');

		\Log::debug('exchange_1c: import, $abs_path='.$abs_path);
		
        $CML2_IMPORT = \Session::get(self::SESSION_KEY, array());

        if(!array_key_exists('IMPORT', $CML2_IMPORT))
            $CML2_IMPORT["IMPORT"] = array(
                'STEP' => 0
                );

        #-----------------------------------------------------------------------
        # UNZIP FILE
        #-----------------------------------------------------------------------
        if(isset($CML2_IMPORT['zip']) && $CML2_IMPORT['zip'])
        {
            $file = $CML2_IMPORT['zip'];

            if(!file_exists($abs_path.$file))
                throw new \Exception('file not exits: '.$abs_path.$file);

            $storage = \Storage::disk($disk);
            $path = $storage->path(trim($file, '/'));

            \Log::debug('exchange_1c: unzip file '.$path);

            // работаем с zip
            $zip = new \ZipArchive();

            // open zip
            $res = $zip->open($path);

            if($res !== TRUE)
            {
                switch ($res)
                {
                    case \ZipArchive::ER_NOZIP:
                        throw new \Exception('not correct zip file: '.$path);
                        break;

                    default:
                        throw new \Exception('zip no open file: '.$path.', error: '.$res);
                        break;
                }
            }

            \Log::debug('exchange_1c: extractTo '.$abs_path.'/'.$dir);
            $zip->extractTo($abs_path.'/'.$dir);
            $zip->close();

            #@unlink($abs_path.$file);

            $CML2_IMPORT["zip"] = false;
            \Session::put(self::SESSION_KEY, $CML2_IMPORT);

            return "progress\n";
            exit;

        }

        $strError = "";
        $strMessage = "";

        #-----------------------------------------------------------------------
        # CHECK USAGE TMP TALES
        #-----------------------------------------------------------------------
        if($CML2_IMPORT["IMPORT"]["STEP"] < 1)
        {
            $use_tmp_table = config('1c_ut_exchange.use_tmp_table', false);
            $CML2_IMPORT["IMPORT"]["STEP"] = $use_tmp_table ? 1 : 10;
        }

        #-----------------------------------------------------------------------
        # PROCESS FILE
        #-----------------------------------------------------------------------
        if ($CML2_IMPORT["IMPORT"]["STEP"] == 1)
        {
            #Drop Temporary Tables

            Schema::dropIfExists('1c_tmp_product_property_value');
            Schema::dropIfExists('1c_tmp_product_property_enum');
            Schema::dropIfExists('1c_tmp_product_property');
            Schema::dropIfExists('1c_tmp_product');
            Schema::dropIfExists('1c_tmp_sections');

            $CML2_IMPORT["IMPORT"]["STEP"] = 1;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 2)
        {
            // Create Tmp Tables

            Schema::create('1c_tmp_sections', function (Blueprint $table) {
                $table->increments('id');
                $table->string('catalog_id', 150);
                $table->string('name', 150);
                $table->integer('parent_id')->unsigned()->nullable()->default(null)->comment('Родитель');
                $table->timestamps();
                });
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 3)
        {
            $storage = \Storage::disk($disk);
            $files = $storage->files($dir);

            if(!$CML2_IMPORT["IMPORT"]["STEP"])
            {

            }

            foreach ($files as $file)
            {
                $xml = new \XMLReader();
                $xml->open($file);
            }

            // ReadXMLToDatabase
            $CML2_IMPORT["IMPORT"]["STEP"] = 4;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 4)
        {
            // IndexTemporaryTables
            $CML2_IMPORT["IMPORT"]["STEP"] = 5;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 5)
        {
            //CIBlockCMLImport
            $CML2_IMPORT["IMPORT"]["STEP"] = 6;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 7)
        {
            $CML2_IMPORT["IMPORT"]["STEP"] = 8;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 8)
        {
            $CML2_IMPORT["IMPORT"]["STEP"] = 9;
        }
        elseif ($CML2_IMPORT["IMPORT"]["STEP"] == 9)
        {
            # DeactivateElement
        }
        else
        {
            $type = $request->get('type');
            \Event::fire(new \Domatskiy\Exchange1C\Events\ImportComplate($type, $abs_path.'/'.$dir, $filename));
        }

        \Session::put(self::SESSION_KEY, $CML2_IMPORT);

        if($strError)
        {
            $response = "failure\n";
            $response .= str_replace("<br>", "", $strError);
        }
        else
        {
			$response = "success\n";
            $response .= "laravel_session\n";
            $response .= session()->getId()."\n";
            $response .= "timestamp=".time();
        }
		
		return $response;
    }

    public static function deactivate (Request $request)
    {

    }
}