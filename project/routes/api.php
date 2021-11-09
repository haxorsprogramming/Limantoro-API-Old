<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware('cors')->group(function(){

  Route::prefix('user')->group(function () {
    Route::get('/', 'Users@show');
    Route::post('/', 'Users@store');
    Route::put('/', 'Users@update');


    Route::post('/login', 'Users@login');
    Route::post('/logout', 'Users@logout');
    Route::get('/getUser', 'Users@getUser');
  });
  Route::prefix('users')->group(function () {
    // Route::post('/register', 'Users@register');
    // Route::post('/register/verify', 'Users@sendVerifyEmail');
    // Route::get('/register/verify/{email_token}', 'Users@verify');
    Route::get('/', 'Users@index');
    Route::get('/notUsedByEmployee', 'Users@notUsedByEmployee');

  });

  Route::prefix('employee')->group(function () {
    Route::get('/cetak', 'Employees@cetak');
    Route::get('/', 'Employees@show');
    Route::post('/', 'Employees@store');
    Route::put('/', 'Employees@update');
  });

  Route::prefix('employees')->group(function () {
    Route::get('/', 'Employees@index');
  });

  Route::prefix('chart_of_accounts')->group(function () {
    // Route::get('/cetak', 'ChartOfAccounts@cetak');
    Route::get('/', 'ChartOfAccounts@index');
    Route::get('/{id}', 'ChartOfAccounts@show');
    Route::post('/', 'ChartOfAccounts@store');
    Route::put('/', 'ChartOfAccounts@update');
  });

  Route::prefix('journals')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Journals@index');
    Route::post('/', 'Journals@store');
    // Route::put('/', 'Journals@update');
  });


  Route::prefix('journal_details')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'JournalDetails@index');
    Route::get('/{id}', 'JournalDetails@show');
    Route::post('/', 'JournalDetails@store');
    Route::put('/', 'JournalDetails@update');
  });

  Route::prefix('roles')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Roles@index');
    // Route::get('/{id}', 'JournalDetails@show');
    // Route::post('/', 'JournalDetails@store');
    // Route::put('/', 'JournalDetails@update');
  });

  Route::prefix('project')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Projects@show');
    Route::post('/', 'Projects@store');
    Route::put('/', 'Projects@update');
    Route::get('/cetak', 'Projects@cetak');
  });

  Route::prefix('projects')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Projects@index');
  });

  Route::prefix('material_controls')->group(function () {
    Route::get('/', 'MaterialControls@index');
    Route::post('/', 'MaterialControls@store');

    // Route::get('/cetak', 'MaterialControls@cetak');
    // Route::get('/cetak_approve', 'MaterialControls@cetak_approve');
  });


  Route::prefix('units')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Units@index');
    Route::get('/{id}', 'Units@show');
    Route::post('/', 'Units@store');
    Route::put('/', 'Units@update');
  });


  Route::prefix('material')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Materials@show');
    Route::post('/', 'Materials@store');
    Route::put('/', 'Materials@update');
    Route::delete('/', 'Materials@delete');
  });

  Route::prefix('materials')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Materials@index');
    Route::get('/getDataByVendor', 'Materials@getDataByVendor');
  });

  Route::prefix('supplier')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Suppliers@show');
    Route::post('/', 'Suppliers@store');
    Route::put('/', 'Suppliers@update');
  });

  Route::prefix('suppliers')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Suppliers@index');
  });

  Route::prefix('cash')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Cashs@show');
    Route::post('/', 'Cashs@store');
    Route::put('/', 'Cashs@update');
  });

  Route::prefix('cashs')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Cashs@index');
  });

  Route::prefix('customer')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Customers@show');
    Route::post('/', 'Customers@store');
    Route::put('/', 'Customers@update');
  });

  Route::prefix('customers')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'Customers@index');
  });

  Route::prefix('purchase_request')->group(function () {
    Route::get('/', 'PurchaseRequests@show');
    Route::post('/', 'PurchaseRequests@store');
    Route::put('/', 'PurchaseRequests@update');
    Route::put('/setApprovedQty', 'PurchaseRequests@setApprovedQty');
    Route::get('/getAvailableQty', 'PurchaseRequests@getAvailableQty');
  });

  Route::prefix('purchase_requests')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'PurchaseRequests@index');
    Route::get('/cetak', 'PurchaseRequests@cetak');
    Route::get('/cetak_approve', 'PurchaseRequests@cetak_approve');
    // Route::put('/updateStatus', 'PurchaseRequests@updateStatus');

  });

  Route::prefix('purchase_order')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'PurchaseOrders@show');
    Route::post('/', 'PurchaseOrders@store');
    Route::put('/', 'PurchaseOrders@update');
    Route::put('/setApprove', 'PurchaseOrders@setApprove');

    Route::get('/getAvailableQty', 'PurchaseOrders@getAvailableQty');
    Route::get('/getQtyInfoReturn', 'PurchaseOrders@getQtyInfoReturn');
    Route::post('/generateProofOfExpenditure', 'PurchaseOrders@generateProofOfExpenditure');
    Route::put('/locking', 'PurchaseOrders@locking');

  });

  Route::prefix('purchase_orders')->group(function () {
    Route::get('/', 'PurchaseOrders@index');
    Route::get('/cetak', 'PurchaseOrders@cetak');

  });

  Route::prefix('goods_receipts')->group(function () {
    Route::get('/', 'GoodsReceipts@index');
    Route::get('/cetak', 'GoodsReceipts@cetak');
  });

  Route::prefix('goods_receipt')->group(function () {
    Route::get('/', 'GoodsReceipts@show');
    Route::post('/', 'GoodsReceipts@store');
    Route::put('/', 'GoodsReceipts@update');
    Route::put('/setCheck', 'GoodsReceipts@setCheck');
  });

  Route::prefix('goods_returns')->group(function () {
    Route::get('/', 'GoodsReturns@index');
    Route::get('/cetak', 'GoodsReturns@cetak');

  });

  Route::prefix('goods_return')->group(function () {
    Route::get('/', 'GoodsReturns@show');
    Route::post('/', 'GoodsReturns@store');
    Route::put('/', 'GoodsReturns@update');
    Route::put('/setCheck', 'GoodsReturns@setCheck');
  });

  Route::prefix('purchase_returns')->group(function () {
    Route::get('/', 'PurchaseReturns@index');
    Route::get('/cetak', 'PurchaseReturns@cetak');

  });

  Route::prefix('purchase_return')->group(function () {
    Route::get('/', 'PurchaseReturns@show');
    Route::post('/', 'PurchaseReturns@store');
    Route::put('/', 'PurchaseReturns@update');
    Route::put('/setCheck', 'PurchaseReturns@setCheck');
  });

  Route::prefix('proof_of_expenditure')->group(function () {
    // Route::get('/cetak', 'Journals@cetak');
    Route::get('/', 'ProofOfExpenditures@show');
    Route::post('/', 'ProofOfExpenditures@store');
    Route::put('/', 'ProofOfExpenditures@update');
    Route::get('/getDescriptions', 'ProofOfExpenditures@getDescriptions');
    Route::get('/cetak', 'ProofOfExpenditures@cetak');
    Route::post('/getDescriptionReturn', 'ProofOfExpenditures@getDescriptionReturn');
  });

  Route::prefix('proof_of_expenditures')->group(function () {
    Route::get('/', 'ProofOfExpenditures@index');
  });

  Route::post('/change_password', 'Users@change_password');


});
