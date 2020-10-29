<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProductMagento extends Model
{
    protected $table = 'order_product_magento';
    public $primaryKey = 'id';

    public $timestamps = true;

    public function order()
    {
        return $this->belongsTo('App\OrderMagento');
    }
    public function product()
    {
        return $this->hasMany('App\ProductMagento');
    }
}
