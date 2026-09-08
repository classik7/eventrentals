<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OwnerPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AiPlannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Owner\WithdrawalController as OwnerWithdrawalController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Owner\WalletController as OwnerWalletController;
use App\Http\Controllers\Owner\KycController as OwnerKycController;
use App\Http\Controllers\Admin\KycController as AdminKycController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\VendorFollowController;
use App\Http\Controllers\SupportController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/image/{filename}', function ($filename) {

    $path = storage_path('app/public/items/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
    ]);

});

Route::middleware('auth')->group(function () {

    Route::get('/kyc/tier1', [ProfileController::class, 'tier1'])->name('kyc.tier1');
    Route::get('/kyc/tier2', [ProfileController::class, 'tier2'])->name('kyc.tier2');
    Route::get('/kyc/tier3', [ProfileController::class, 'tier3'])->name('kyc.tier3');

});

Route::view('/terms', 'terms')->name('terms');

/*		TIER*/
Route::post('/kyc/tier1', [ProfileController::class, 'submitTier1'])->name('kyc.tier1.submit');

Route::post('/kyc/tier2', [ProfileController::class, 'submitTier2'])->name('kyc.tier2.submit');

Route::post('/kyc/tier3', [ProfileController::class, 'submitTier3'])->name('kyc.tier3.submit');

	
Route::get('/notifications', function () {
    return auth()->user()->notifications()->latest()->take(10)->get();
})->middleware('auth');

Route::post('/notifications/read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return response()->json(['success' => true]);
})->middleware('auth');


Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::post('/ai-plan', [AiPlannerController::class, 'generate'])->name('ai.plan');

Route::get('/owners/{user}', [OwnerPageController::class, 'show'])->name('owners.show');

Route::get('/items/create', [ItemController::class, 'create'])
    ->middleware('auth')
    ->name('items.create');

Route::get('/items/{item}', [ItemController::class, 'show'])
    ->name('items.show');
	
Route::post('/paystack/webhook', [\App\Http\Controllers\PaymentController::class, 'handleWebhook']);


	Route::post('/rental/{id}/dispute', [DisputeController::class, 'store'])
    ->middleware('auth')
    ->name('dispute.store');
	
	Route::get('/disputes/{id}', [DisputeController::class, 'show'])
    ->middleware('auth')
    ->name('disputes.show');
	
	// dispute message
	Route::post('/admin/disputes/{id}/message', [DisputeController::class, 'sendMessage'])
    ->middleware('auth')
    ->name('admin.disputes.message');
	
	Route::get('/disputes/{id}/fetch',
    [App\Http\Controllers\DisputeController::class, 'fetch']
)->name('disputes.fetch');

Route::post('/disputes/{id}/typing',
    [App\Http\Controllers\DisputeController::class, 'typing']
)->name('disputes.typing');


Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

Route::get('/owner/wallet', [OwnerWalletController::class, 'index'])
    ->name('owner.wallet');

Route::get('/owner/wallet/export', [OwnerWalletController::class, 'export'])
    ->name('owner.wallet.export');

Route::get('/owner/wallet/pdf', [OwnerWalletController::class, 'exportPdf'])
    ->name('owner.wallet.pdf');

Route::get('/owner/kyc', [OwnerKycController::class, 'index'])->name('owner.kyc');
Route::post('/owner/kyc', [OwnerKycController::class, 'submit']);

Route::post('/vendor/{id}/follow', [VendorFollowController::class, 'toggle'])
    ->middleware('auth')
    ->name('vendor.follow');

	Route::get('/notifications/fetch', function () {
    return auth()->user()->unreadNotifications;
})->middleware('auth');

Route::post('/notifications/read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return response()->json(['success' => true]);
})->middleware('auth');



Route::middleware(['auth'])->group(function () {

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');

    Route::get('/support/{conversation}', [SupportController::class, 'show'])->name('support.show');

    Route::post('/support/send', [SupportController::class, 'send'])->name('support.send');

});
	
	Route::get('/notifications/read', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return back();
});
});
/*
|--------------------------------------------------------------------------
| Payment Callback (NO AUTH)
|--------------------------------------------------------------------------
*/

Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->name('payment.callback');

Route::delete('/cart/clear', [CartController::class, 'clear'])
    ->name('cart.clear')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rentals
    |--------------------------------------------------------------------------
    */
	Route::get('/disputes/{id}/messages', [DisputeController::class, 'fetchMessages'])
    ->name('disputes.fetchMessages');

    Route::get('/rentals', [RentalController::class, 'requests'])
        ->name('rentals.index');

    Route::get('/rentals/my', [RentalController::class, 'myRentals'])
        ->name('rentals.my');

    Route::match(['get','post'], '/rentals/{item}/summary', [RentalController::class, 'summary'])
        ->name('rentals.summary');

    Route::post('/rentals/{item}', [RentalController::class, 'store'])
        ->name('rentals.store');

    Route::get('/rental-requests', [RentalController::class, 'requests'])
        ->name('rentals.requests');

    Route::post('/rentals/{rental}/approve', [RentalController::class, 'approve'])
        ->name('rentals.approve');

    Route::post('/rentals/{rental}/reject', [RentalController::class, 'reject'])
        ->name('rentals.reject');

    Route::post('/ai/add-to-cart', [AiPlannerController::class, 'addToCart'])
        ->name('ai.addToCart');
	
	
	Route::post('/disputes/{id}/message', 
    [\App\Http\Controllers\DisputeController::class, 'sendMessage']
	)->name('disputes.message');
	
	Route::post('/disputes/{id}/close',
    [DisputeController::class, 'close']
)->name('disputes.close');
    /*
    |--------------------------------------------------------------------------
    | Owner Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/owner/dashboard', [\App\Http\Controllers\OwnerDashboardController::class, 'index'])
        ->name('owner.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Owner Withdrawal
    |--------------------------------------------------------------------------
    */

    Route::get('/owner/withdraw', function () {
        $owner = auth()->user();
        return view('owners.withdraw', [
            'walletBalance' => $owner->wallet_balance
        ]);
    })->name('owner.withdraw.page');

Route::post('/owner/withdraw', [OwnerWithdrawalController::class, 'store'])
    ->name('owner.withdraw.store');
	
	
	
	Route::post('/typing', function (\Illuminate\Http\Request $request) {
    broadcast(new \App\Events\UserTyping(
        $request->conversation_id,
        auth()->id()
    ))->toOthers();

    return response()->json();
});
 /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile/update', [ProfileController::class, 'update'])
        ->name('profile.update');

	// ✅ THEN
	Route::get('/profile', [ProfileController::class, 'show'])
    ->name('profile.show');

    /*
    |--------------------------------------------------------------------------
    | Support/customer service 
    |--------------------------------------------------------------------------
    */
	Route::get('/support-admin', [SupportController::class, 'admin'])
    ->middleware('auth')
    ->name('support.admin');
	
	Route::post('/support/typing', function (\Illuminate\Http\Request $request) {
    broadcast(new \App\Events\UserTyping(
        $request->conversation_id,
        auth()->id()
    ))->toOthers();

    return response()->json(['status' => 'ok']);
})->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::post('/payment/{rental}', [PaymentController::class, 'pay'])
        ->name('payment.pay');

    /*
    |--------------------------------------------------------------------------
    | Items (Owner)
    |--------------------------------------------------------------------------
    */

    Route::get('/my-items', [ItemController::class, 'myItems'])
        ->name('items.my');

    Route::post('/items', [ItemController::class, 'store'])
        ->name('items.store');

    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])
        ->name('items.edit');

    Route::put('/items/{item}', [ItemController::class, 'update'])
        ->name('items.update');

    Route::post('/items/{item}/toggle', [ItemController::class, 'toggleStatus'])
        ->name('items.toggle');

    Route::delete('/items/{item}', [ItemController::class, 'destroy'])
        ->name('items.destroy');

   /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    Route::post('/cart/add/{item}', [CartController::class, 'add'])
        ->name('cart.add');

    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::post('/cart/checkout', [CartController::class, 'checkout'])
        ->name('cart.checkout');

    Route::get('/payment/verify/{reference}', [PaymentController::class, 'verify'])
        ->name('payment.verify');

    Route::get('/payment/success', function () {
        return view('payments.success');
    })->name('payment.success');

    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */

    Route::get('/items/{item}/review', [\App\Http\Controllers\ReviewController::class, 'create'])
        ->name('reviews.create');

    Route::post('/items/{item}/review', [\App\Http\Controllers\ReviewController::class, 'store'])
        ->name('reviews.store');

    /*
    |--------------------------------------------------------------------------
    | Wishlist
    |--------------------------------------------------------------------------
    */

    Route::post('/wishlist/{item}', [WishlistController::class, 'toggle'])
        ->name('wishlist.toggle');

    Route::get('/wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index');
});

/*
|--------------------------------------------------------------------------
| Public AJAX
|--------------------------------------------------------------------------
*/

Route::post('/check-availability/{item}', [RentalController::class, 'checkAvailability'])
    ->name('rentals.checkAvailability');

Route::get('/rentals/{item}/blocked-dates', [RentalController::class, 'blockedDates'])
    ->name('rentals.blockedDates');

/*
|--------------------------------------------------------------------------
| Admin Routes (PROTECTED)
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/withdrawals', [AdminWithdrawalController::class, 'index'])
        ->name('admin.withdrawals.index');

    Route::get('/admin/finance', [\App\Http\Controllers\Admin\FinanceController::class, 'index'])
        ->name('admin.finance');

    Route::get('/admin/finance/export', [\App\Http\Controllers\Admin\FinanceController::class, 'export'])
        ->name('admin.finance.export');

    Route::post('/admin/disputes/{id}/log-call', [AdminDisputeController::class, 'logCall'])
        ->name('admin.disputes.logCall');

    Route::get('/admin/disputes', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'index'])
        ->name('admin.disputes.index');

    Route::get('/admin/disputes/{id}', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'show'])
        ->name('admin.disputes.show');

    Route::post('/admin/disputes/{id}/update', [\App\Http\Controllers\Admin\AdminDisputeController::class, 'update'])
        ->name('admin.disputes.update');

    Route::post('/admin/commission/check', [AdminFinanceController::class, 'checkCommission'])
        ->name('admin.commission.check');

    Route::post('/admin/withdrawals/bulk', [AdminWithdrawalController::class, 'bulk'])
        ->name('admin.withdrawals.bulk');

    Route::post('/admin/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])
        ->name('admin.withdrawals.approve');

    Route::post('/admin/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])
        ->name('admin.withdrawals.reject');

    Route::get('/admin/withdrawals/export', [AdminWithdrawalController::class, 'export'])
        ->name('admin.withdrawals.export');

    // 🔥 KYC ROUTES (FIXED)
    Route::get('/admin/kyc', [AdminKycController::class, 'index'])->name('admin.kyc');

    Route::post('/admin/kyc/{id}/approve', [AdminKycController::class, 'approve'])->name('admin.kyc.approve');

    Route::post('/admin/kyc/{id}/reject', [AdminKycController::class, 'reject'])->name('admin.kyc.reject');
	
	
	
	Route::post('/paystack/webhook', [\App\Http\Controllers\PaystackWebhookController::class, 'handle']);
	
	
});

require __DIR__.'/auth.php';