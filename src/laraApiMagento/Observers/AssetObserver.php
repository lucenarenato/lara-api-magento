<?php

namespace App\Observers;

use App\Models\Asset;
use App\Jobs\MagentoDisableProductJob;
use App\Jobs\MagentoUpdateProductImageJob;
use App\Jobs\MagentoUpdateProductJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AssetObserver
{

    use \App\Traits\MagentoObserverTrait;
    //Possible Events to Hook

    //retrieved, creating, created, updating, updated, saving, saved,  deleting, deleted, restoring, restored.
    // private $safeFields = [];
    
    public function restored(Asset $model)
    {
    }

    public function created(Asset $model)
    {
        if ($this->isUpdateAssetOnMagento($model)) {
            dispatch(new MagentoUpdateProductJob($model->_id));
        }
    }

    public function updated(Asset $model)
    {
        if ($this->isUpdateAssetOnMagento($model) && $this->fieldsChanged($model, ['name', 'small_description'])) {
            dispatch(new MagentoUpdateProductJob($model->_id));
        }

        if ($this->isUpdateAssetThumbOnMagento($model)) {
            dispatch(new MagentoUpdateProductImageJob($model->_id));
        }
    }

    public function deleted(Asset $model)
    {
        if ($this->isUpdateAssetOnMagento($model)) {
            dispatch(new MagentoDisableProductJob($model->_id));
        }
    }
}
