<?php

namespace App\Http\Controllers;

use App\Jobs\MagentoUpdateProductPricesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MagentoProductController extends Controller
{
    public function updatePrice(Request $request)
    {
        Log::debug('retorno webhook');

        $params = $request->all();

        Log::debug($params);

        if (env('MAGENTO_ACTIVE_SYNC')) {
            $this->dispatch(new MagentoUpdateProductPricesJob($params['sku']));
            return response()->json(['success' => 200], 200);
        }

        return response()->json(['error' => 503], 503);
    }
}
