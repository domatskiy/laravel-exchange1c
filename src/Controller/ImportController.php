<?php

namespace Domatskiy\Exchange1C\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ImportController extends Controller {

    public function request(Request $request)
    {
        # get mode: checkauth init file&filename= import
        $mode = $request->get('mode');
        $type = $request->get('type');

		\Log::debug('exchange_1c: $mode='.$mode.', session_id='.session()->getId());
		
        try {

            if($type == 'catalog')
            {
                if(!class_exists(\Domatskiy\Exchange1C\Catalog::class))
                    throw new \Exception('not correct request, class ExchangeCML not found');

                if(!method_exists(\Domatskiy\Exchange1C\Catalog::class, $mode))
                    throw new \Exception('not correct request, mode='.$mode);

                $responce = call_user_func([\Domatskiy\Exchange1C\Catalog::class, $mode], $request);
                \Log::debug('exchange_1c: $responce='."\n".$responce);

                echo $responce;
            }

        }
        catch (\Exception $e)
        {
			\Log::error("exchange_1c: failure \n".$e->getMessage()."\n".$e->getFile()."\n".$e->getLine()."\n");
			
            echo "failure\n";
            echo $e->getMessage()."\n";
            echo $e->getFile()."\n";
            echo $e->getLine()."\n";
        }
    }
}

