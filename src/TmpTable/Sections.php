<?php

namespace Domatskiy\ExchangeCML\TmpTable;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    protected $table = '1c_tmp_sections';

    //Добавляем в выдачу вычисляемое поле
    protected $appends = array('cut');

    //Делаем поля доступными для автозаполнения
    protected $fillable = array('name', 'xml_id', 'parent_id');

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

        ];

    //правила валидиции
    public static $rules = array(
        'header' => 'required|max:256',
        'link' => 'required|between:2,32|unique',
        'article' => 'required'
    );

    public function getCutAttribute(){

    }
}
