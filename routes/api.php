<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Item;
use App\Http\Controllers\CheckoutController;

Route::get('/verify/{reference}', [CheckoutController::class, 'verify']);

Route::get('/items', function (Request $request) {

    $baseUrl = $request->getSchemeAndHttpHost();

    $items = Item::latest()->get()->map(function ($item) use ($baseUrl) {
        $item->image_url = $item->image
            ? $baseUrl . '/storage/' . $item->image
            : null;
        return $item;
    });

    return response()->json($items);
});