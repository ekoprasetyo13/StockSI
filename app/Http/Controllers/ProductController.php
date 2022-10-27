<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin,staff');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::orderBy('name','ASC')
            ->get(['name','id']);

        $producs = Product::all();
        return view('products.index', compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request , [
            'nama'          => 'required|string',
            'harga'         => 'required',
            'qty'           => 'required',
            // 'image'         => 'required',
            // 'link'         => 'required',
            'category_id'   => 'required',
            // 'description'   => 'required',
        ]);

        $input = $request->all();
        $get_category = Category::orderBy('name','ASC')
        ->where('id', $input["category_id"])->first();
        $input['image'] = null;
        $input['product_code'] = strtoupper(substr($get_category->name, 0, 1)).strtoupper(substr($get_category->name, 1, 2)).date('Y').date('m').date('d').strtotime("now");
        if ($request->hasFile('image')){
            $input['image'] = '/upload/products/'.Str::slug($input['nama'], '-').strtotime('now').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/upload/products/'), $input['image']);
        }

        $product_eks = Product::create($input);
        ActivityLog::create(['user_id'=> Auth::user()->id, 'activity_status'=> 1, 'product_id'=> $product_eks->id]);
        return response()->json([
            'success' => true,
            'message' => 'Products Created'
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::orderBy('name','ASC')
            ->get()
            ->pluck('name','id');
        $product = Product::find($id);
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::orderBy('name','ASC')
            ->get()
            ->pluck('name','id');

        $this->validate($request , [
            'nama'          => 'required|string',
            'harga'         => 'required',
            'qty'           => 'required',
            // 'link'           => 'required',
//            'image'         => 'required',
            'category_id'   => 'required',
        ]);

        $input = $request->all();
        $produk = Product::findOrFail($id);

        $input['image'] = $produk->image;

        if ($request->hasFile('image')){
            if (!$produk->image == NULL){
                if(file_exists(public_path($produk->image))){
                    unlink(public_path($produk->image));
                }
                // unlink(public_path($produk->image));
            }
            $input['image'] = '/upload/products/'.Str::slug($input['nama'], '-').strtotime('now').'.'.$request->image->getClientOriginalExtension();
            $request->image->move(public_path('/upload/products/'), $input['image']);
        }
        if($request->qty != $produk->qty){
            ActivityLog::create(['user_id'=> Auth::user()->id, 'activity_status'=> 6, 'product_id'=> $id]);
        }else{
            ActivityLog::create(['user_id'=> Auth::user()->id, 'activity_status'=> 2, 'product_id'=> $id]);
        }
        $produk->update($input);

        return response()->json([
            'success' => true,
            'message' => 'Products Update'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if (!$product->image == NULL){

            if(file_exists(public_path($product->image))){
                unlink(public_path($product->image));
            }
        }

        Product::destroy($id);
        ActivityLog::create(['user_id'=> Auth::user()->id, 'activity_status'=> 3, 'product_id'=> $id]);
        return response()->json([
            'success' => true,
            'message' => 'Products Deleted'
        ]);
    }

    public function apiProducts(){
        //$product = Product::join('activity_log', 'activity_log.product_id', '=', 'products.id', 'left outer')->orderBy('products.id', 'desc')->get();
        $product = Product::all();
        //dd($product);
        // dd(DNS1D::getBarcodeHTML("1982924", 'PHARMA'));
        return DataTables::of($product)
            ->addColumn('category_name', function ($product){
                return $product->category->name;
            })
            ->addColumn('product_code', function ($product){
                return DNS1D::getBarcodeHTML($product->product_code, 'C128', true)."<br>"."<p align='justify'>($product->product_code)</p>";
            })->escapeColumns([])
            ->addColumn('show_photo', function($product){
                if ($product->image == NULL){
                    return 'No Image';
                }
                return '<img class="rounded-square" width="50" height="50" src="'. url($product->image) .'" alt="">';
            })
            // ->addColumn('desc_product', function($product){
            //     return '<span style="width: 70px;position: relative;word-break: break-all;">'.$product->description.'</span>';
            // })
            // ->addColumn('link', function($product){
            //     if(!empty($product->link)){
            //     return '<a target="_blank" href="'.$product->link.'">Online Shop Link</a>';
            // }
            // })
            ->addColumn('activity_status', function($product){
                $activ = ActivityLog::where('product_id', $product->id)->orderBy('id_activity', 'desc')->first();
                if(isset($activ)){
                    $user = User::find($activ->user_id);
                    if ($activ->activity_status == 1){
                        $message = "Last Input Product by $user->name";
                    }else if($activ->activity_status == 2){
                        $message = "Last Edit Product by $user->name";
                    }else if($activ->activity_status == 3){
                        $message = "Last Hapus Product by $user->name";
                    }else if($activ->activity_status == 4){
                        $message = "Last Hapus PK by $user->name";
                    }else if($activ->activity_status == 5){
                        $message = "Last Hapus PM by $user->name";
                    }else if($activ->activity_status == 6){
                        $message = "Last Edit QTY by $user->name";
                    }else if($activ->activity_status == 7){
                        $message = "Last Add Product Out by $user->name";
                    }else if($activ->activity_status == 8){
                        $message = "Last Add Product In by $user->name";
                    }else if($activ->activity_status == 9){
                        $message = "Last Edit Product Out by $user->name";
                    }else if($activ->activity_status == 10){
                        $message = "Last Edit Product In by $user->name";
                    }else if($activ->activity_status == 11){
                        $message = "Last Edit QTY Product Out by $user->name";
                    }else if($activ->activity_status == 12){
                        $message = "Last Edit QTY Product In by $user->name";
                    }
                }else{
                    $message = "Nothing";
                }
                return '<span class="badge badge-warning">'.$message.'</span>';
            })
            ->addColumn('action', function($product){
                return '<a href="#" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-eye-open"></i> Show</a> ' .
                    '<a onclick="editForm('. $product->id .')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
                    '<a onclick="deleteData('. $product->id .')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
            })
            ->rawColumns(['category_name','show_photo','action'])->make(true);

    }
}
