<?php

namespace Domatskiy\ExchangeCML\TmpTable;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = '1c_tmp_product';

    protected $fillable = [

        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

        ];
}
