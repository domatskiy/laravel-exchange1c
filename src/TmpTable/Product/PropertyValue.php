<?php

namespace Domatskiy\ExchangeCML\TmpTable\Product;

use Illuminate\Database\Eloquent\Model;

class PropertyValue extends Model
{
    protected $table = '1c_tmp_product_property_value';

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
