<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'store' => [
                    'name' => config('pos.store.name'),
                    'address' => config('pos.store.address'),
                    'phone' => config('pos.store.phone'),
                    'tin' => config('pos.store.tin'),
                    'footer' => config('pos.store.footer'),
                ],
                'currency' => config('pos.currency'),
                'loyalty' => config('pos.loyalty'),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'store.name' => ['sometimes', 'string', 'max:255'],
            'store.address' => ['nullable', 'string', 'max:255'],
            'store.phone' => ['nullable', 'string', 'max:64'],
            'store.tin' => ['nullable', 'string', 'max:64'],
            'store.footer' => ['nullable', 'string', 'max:255'],
            'currency' => ['sometimes', 'string', 'max:8'],
            'loyalty.points_per_currency' => ['sometimes', 'numeric', 'min:1'],
        ]);

        $path = config_path('pos.php');
        $config = require $path;

        $config['store']['name'] = $request->input('store.name', $config['store']['name']);
        $config['store']['address'] = $request->input('store.address', $config['store']['address']);
        $config['store']['phone'] = $request->input('store.phone', $config['store']['phone']);
        $config['store']['tin'] = $request->input('store.tin', $config['store']['tin']);
        $config['store']['footer'] = $request->input('store.footer', $config['store']['footer']);
        $config['currency'] = $request->input('currency', $config['currency']);
        $config['loyalty']['points_per_currency'] = (float) $request->input('loyalty.points_per_currency', $config['loyalty']['points_per_currency']);

        $export = '<?php return '.var_export($config, true).';';
        file_put_contents($path, $export);

        Cache::forget('pos_settings');

        return response()->json(['message' => 'Settings saved.']);
    }
}
