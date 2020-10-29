<?php

namespace App\Traits;

trait MagentoObserverTrait
{

    public static function isUpdateAssetOnMagento($model)
    {
        return (self::isMarketPlace($model) && self::isMagentoSyncActive());
    }

    public static function isUpdateAssetThumbOnMagento($model)
    {
        if (self::isUpdateAssetOnMagento($model)) {
            return isset($model->getChanges()['files']['thumb']['path']);
        }
    }

    private static function isMagentoSyncActive()
    {
        return env('MAGENTO_ACTIVE_SYNC');
    }

    private static function isMarketPlace($model)
    {
        return $model['flags']['marketplace'];
    }
}
