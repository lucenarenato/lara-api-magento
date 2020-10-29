<?php

use Illuminate\Http\Request;

Route::middleware('cors')->post('magento/product/update-prices', 'MagentoProductController@updatePrice');

Route::middleware(['auth:api', 'cors', 'statistics'])->group(function () {
    Route::prefix('hooks')->group(function () {
        Route::prefix('magento')->group(function () {
            Route::prefix('product')->group(function () {
                Route::post('update', 'PassportController@details');
                Route::post('deleted', 'PassportController@details');
            });
        });
    });
    Route::post('products/putproductmagento', 'ProductController@putproductmagento');
});
