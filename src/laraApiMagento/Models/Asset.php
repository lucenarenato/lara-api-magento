<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Log;

class Asset extends Eloquent
{

    //use \App\Traits\DataTableTrait;
    //use \App\Traits\AssetTrait;

    protected $collection = 'assets';
    protected $connection = 'mongodb';
    protected $guarded = array();

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new OrganizationScope(Auth::user()));
    }

}
