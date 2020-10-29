<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationMagento extends Model
{    
    protected $table = 'organizations_magento';
    protected $primaryKey = 'customer_id';
    protected $fillable = ['customer_id', 'organization_id'];
    public $timestamps = false;

}
