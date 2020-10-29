<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderMagento extends Model
{
    protected $table = 'orders_magento';

    public $primaryKey = 'id';
    protected $fillable = ['customer_id', 'entity_id', 'increment_id', 'customer_firstname', 'order_date'];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function orderproduct()
    {
        return $this->hasMany('App\OrderProductMagento');
    }
}


