<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductMagento extends Model
{
    protected $table = 'products_magento';

    public $primaryKey = 'id';

    public $timestamps =true;

    public function orderproduct()
    {
        return $this->belongsTo('App\OrderProductMagento');

    }
}
