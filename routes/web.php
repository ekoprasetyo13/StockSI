<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductKeluarController;
use App\Http\Controllers\ProductMasukController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;
use Yajra\DataTables\Facades\DataTables;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/dashboard', function () {
    // return Inertia::render('Dashboard');
    return view('layouts.master');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('homes');

// Route::get('dashboard', function () {
// 	return view('layouts.master');
// });

Route::group(['middleware' => 'auth'], function () {
	Route::resource('categories', CategoryController::class);
	Route::get('/apiCategories', [CategoryController::class, 'apiCategories'])->name('api.categories');
	Route::get('/exportCategoriesAll', [CategoryController::class, 'exportCategoriesAll'])->name('exportPDF.categoriesAll');
	Route::get('/exportCategoriesAllExcel', [CategoryController::class, 'exportExcel'])->name('exportExcel.categoriesAll');

	Route::resource('customers', CustomerController::class);
	Route::get('/apiCustomers', [CustomerController::class, 'apiCustomers'])->name('api.customers');
	Route::post('/importCustomers', [CustomerController::class, 'ImportExcel'])->name('import.customers');
	Route::get('/exportCustomersAll', [CustomerController::class, 'exportCustomersAll'])->name('exportPDF.customersAll');
	Route::get('/exportCustomersAllExcel', [CustomerController::class, 'exportExcel'])->name('exportExcel.customersAll');

	Route::resource('sales', SaleController::class);
	Route::get('/apiSales', [SaleController::class, 'apiSales'])->name('api.sales');
	Route::post('/importSales', [SaleController::class, 'ImportExcel'])->name('import.sales');
	Route::get('/exportSalesAll', [SaleController::class, 'exportSalesAll'])->name('exportPDF.salesAll');
	Route::get('/exportSalesAllExcel', [SaleController::class, 'exportExcel'])->name('exportExcel.salesAll');

	Route::resource('suppliers', SupplierController::class);
	Route::get('/apiSuppliers', [SupplierController::class, 'apiSuppliers'])->name('api.suppliers');
	Route::post('/importSuppliers', [SupplierController::class, 'ImportExcel'])->name('import.suppliers');
	Route::get('/exportSupplierssAll', [SupplierController::class, 'exportSuppliersAll'])->name('exportPDF.suppliersAll');
	Route::get('/exportSuppliersAllExcel', [SupplierController::class, 'exportExcel'])->name('exportExcel.suppliersAll');

	Route::resource('products', ProductController::class);
	Route::get('/apiProducts', [ProductController::class, 'apiProducts'])->name('api.products');

	Route::resource('productsOut', ProductKeluarController::class);
	Route::get('/apiProductsOut', [ProductKeluarController::class, 'apiProductsOut'])->name('api.productsOut');
	Route::get('/exportProductKeluarAll', [ProductKeluarController::class, 'apiProductsOut'])->name('exportPDF.productKeluarAll');
	Route::get('/exportProductKeluarAllExcel', [ProductKeluarController::class, 'apiProductsOut'])->name('exportExcel.productKeluarAll');
	Route::get('/exportProductKeluar/{id}', [ProductKeluarController::class, 'apiProductsOut'])->name('exportPDF.productKeluar');

	Route::resource('productsIn', ProductMasukController::class);
	Route::get('/apiProductsIn', [ProductMasukController::class, 'apiProductsIn'])->name('api.productsIn');
	Route::get('/exportProductMasukAll', [ProductMasukController::class, 'exportProductMasukAll'])->name('exportPDF.productMasukAll');
	Route::get('/exportProductMasukAllExcel', [ProductMasukController::class, 'exportExcel'])->name('exportExcel.productMasukAll');
	Route::get('/exportProductMasuk/{id}', [ProductMasukController::class, 'exportProductMasuk'])->name('exportPDF.productMasuk');

	Route::resource('user', UserController::class);
	Route::get('/apiUser', [UserController::class, 'apiUsers'])->name('api.users');

	Route::get('api/print/barcode', function(Request $request){

		$product = Product::all();
		return DataTables::of($product)

            ->addColumn('product_code', function ($product){
            	return DNS1D::getBarcodeHTML($product->product_code, 'C128');
            })->escapeColumns([])
			->addColumn('pcode', function ($product){
                return $product->product_code;
            })
            ->rawColumns(['action'])->make(true);

	})->name('api.print.barcode');
	Route::get('print/barcode', function(Request $request){
		// set_time_limit(3000);
		ini_set("memory_limit", "999M");
		ini_set("max_execution_time", "999");
		$product = Product::get();
		$pdf = Pdf::loadView('products.barcode', ['product' => $product])->setOptions(['defaultFont' => 'sans-serif']);
		//dd($pdf);
		if($request->download){
			//return view('products.barcode')->with('product', $product);
			return $pdf->download('product_'.date('Y-m-dHis').'.pdf');
		}

        //
		return view('products.barcode')->with('product', $product);
	});


});

Route::get('barcode/allfireman', function(Request $request){
	// set_time_limit(3000);
	ini_set("memory_limit", "999M");
	ini_set("max_execution_time", "999");
	// $product = Product::get();
	$file = file_get_contents(public_path('file.txt'));
	$product = explode('\n', $file);



	// $product = file_get_contents();
	$pdf = Pdf::loadView('products.bardcode_fireman', ['product' => $product])->setOptions(['defaultFont' => 'sans-serif']);
	//dd($pdf);
	if($request->download){
		//return view('products.barcode')->with('product', $product);
		return $pdf->download('product_'.date('Y-m-dHis').'.pdf');
	}

	//
	//return view('products.bardcode_fireman')->with('product', $product);
});

require __DIR__.'/auth.php';
