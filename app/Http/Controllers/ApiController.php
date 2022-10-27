<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
class ApiController extends Controller
{
    //
    public function show(Request $request){
         $data = Product::where('product_code', $request->product_code)->first();

         if(isset($data->id)){
            $data->image = "http://".$_SERVER["HTTP_HOST"].$data->image;

            return $data;
         }else{
            // $return = array(
            //     'status'=> 200,
            //     'data'=> "Not Found",
            // );
            return abort(404, "NotFOUND");
         }

    }
}
