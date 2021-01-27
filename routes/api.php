<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserCollection;
use App\Http\Resources\User as UserResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::middleware('auth:sanctum')->post('auth/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        'success' => true,
        'message' => 'Logout was successful'
    ], 200);
});

Route::group(['prefix' => 'auth'], function () {
    Route::Post('register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
    Route::Post('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
    Route::Post('forgot-password', [\App\Http\Controllers\AuthController::class, 'forgot_password'])->name('forgot password');
});

Route::group([
    'prefix' => 'customer',
    'middleware' => 'auth:sanctum'
], function () {
    Route::get('', [\App\Http\Controllers\UserController::class, 'handler_customers'])->name('My customers');
    Route::put('{id}', [\App\Http\Controllers\UserController::class, 'edit'])->name('edit customer');
    Route::get('{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('get single customer');
    Route::delete('{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('delete customer');
    Route::put('bar/{id}', [\App\Http\Controllers\UserController::class, 'bar'])->name('bar customer');
    Route::put('unbar/{id}', [\App\Http\Controllers\UserController::class, 'unbar'])->name('unbar customer');

    Route::group(['prefix' => '{customerId}'], function () {
        Route::get('cards', [\App\Http\Controllers\CardController::class, 'index'])->name('Customer Cards');
        Route::post('card', [\App\Http\Controllers\CardController::class, 'store'])->name('Customer Create Card');
    });

    Route::group(['prefix' => '{customerId}/transactions'], function () {
        Route::get('', [\App\Http\Controllers\TransHeaderController::class, 'index'])->name('get transactions');
    });
});

Route::group([
    'prefix' => 'users',
    'middleware' => 'auth:sanctum'
], function () {
    Route::get('{type}', [\App\Http\Controllers\UserController::class, 'index'])->name('list users');
    Route::put('{id}', [\App\Http\Controllers\UserController::class, 'edit'])->name('edit user');
    Route::get('{id}', [\App\Http\Controllers\UserController::class, 'show'])->name('get single user');
    Route::delete('{id}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('delete user');
    Route::put('bar/{id}', [\App\Http\Controllers\UserController::class, 'bar'])->name('bar user');
    Route::put('unbar/{id}', [\App\Http\Controllers\UserController::class, 'unbar'])->name('unbar user');
});

Route::group([
    'prefix' => 'handler',
    'middleware' => 'auth:sanctum'
], function () {
    Route::get('{agentId}/customers', [\App\Http\Controllers\UserController::class, 'handler_customers'])->name('handler customers');
    Route::get('customer/cards', [\App\Http\Controllers\CardController::class, 'get_handler_customer_cards'])->name('Handler Customer Card');
});

Route::group([
    'prefix' => 'card',
    'middleware' => 'auth:sanctum'
], function () {

    Route::put('{id}', [\App\Http\Controllers\CardController::class, 'update'])->name('Customer Edit Card');
    Route::post('{cardId}/reset', [\App\Http\Controllers\CardController::class, 'reset'])->name('Customer Reset Card');
    Route::group(['prefix' => '{cardId}/transaction'], function () {
        Route::get('', [\App\Http\Controllers\TransHeaderController::class, 'cards'])->name('get card transactions');
        Route::post('', [\App\Http\Controllers\TransHeaderController::class, 'store_deposit'])->name('deposit transaction');
    });

});

Route::group([
    'prefix' => 'transactions',
    'middleware' => 'auth:sanctum'
], function () {
    Route::post('/withdraw', [\App\Http\Controllers\TransHeaderController::class, 'store_withdrawal'])->name('create withdrawal transaction');
    Route::get('/pending', [\App\Http\Controllers\TransHeaderController::class, 'my_pending_withdrawals'])->name('Get agent pending withdrawals');
    Route::get('/all-pending', [\App\Http\Controllers\TransHeaderController::class, 'show_pending_withdrawals'])->name('Get all pending withdrawals');
    Route::get('/all', [\App\Http\Controllers\TransHeaderController::class, 'all_transactions'])->name('[Admin] Get all transactions');
    Route::get('/detail/{id}', [\App\Http\Controllers\TransHeaderController::class, 'show'])->name('Get transaction details');
    Route::put('/{id}', [\App\Http\Controllers\TransHeaderController::class, 'mark_as_completed'])->name('Mark transaction as completed');
    Route::delete('/{id}', [\App\Http\Controllers\TransHeaderController::class, 'destroy'])->name('Delete transaction');
    Route::get('/', [\App\Http\Controllers\TransHeaderController::class, 'index'])->name('Get my transactions');
});


Route::group([
    'prefix' => 'user-group',
    'middleware' => 'auth:sanctum'
], function () {
    Route::post('', [\App\Http\Controllers\UserGroupController::class, 'store'])->name('Create User Group');
    Route::get('', [\App\Http\Controllers\UserGroupController::class, 'index'])->name('Get User Groups');
    Route::put('{user_group_id}', [\App\Http\Controllers\UserGroupController::class, 'update'])->name('Update User Group');
    Route::delete('{user_group_id}', [\App\Http\Controllers\UserGroupController::class, 'destroy'])->name('Delete User Group');
    Route::put('default/{user_group_id}', [\App\Http\Controllers\UserGroupController::class, 'set_default'])->name('Set User Group as default');
});


Route::group([
    'middleware' => 'auth:sanctum'
], function () {
    Route::get('sync', [\App\Http\Controllers\SyncController::class, 'sync_update'])->name('Perform sync update');
});

Route::group([
    'prefix' => '',
    'middleware' => 'auth:sanctum'
], function () {
    Route::post('{type}', [\App\Http\Controllers\UserController::class, 'create'])->name('create customer');
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'get_stat'])->name('dashboard stats');
//    Route::get('sysdash', [\App\Http\Controllers\DashboardController::class, 'get_system_stats'])->name('sysdash stats');
//    Route::get('custdash', [\App\Http\Controllers\DashboardController::class, 'get_customer_stats'])->name('customer dashboard stats');
});


//Free no guard
Route::post('{type}', [\App\Http\Controllers\UserController::class, 'create'])->name('create user')->middleware('cors');
