<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Unit;
use Auth;
use Illuminate\support\Carbon;
class ProductController extends Controller
{
    public function ProductAll(){
    	$product = Product::latest()->get();
    	return view('backend.product.product_all', compact('product'));
    }//End Method

    public function ProductAdd(){
    	$supplier = supplier::all();
    	$category = Category::all();
    	$unit = Unit::all();
    	return view('backend.product.product_add',compact('supplier','category','unit'));
    }//End Method

    public function ProductStore(Request $request){
    	Product::insert([
    		'name' => $request->name,
    		'supplier_id' => $request->supplier_id,
    		'category_id' => $request->category_id,
    		'unit_id' => $request->unit_id,
    		'quantity' => '0',
    		'created_by' => Auth::user()->id,
    		'created_at' => Carbon::now(),
    	]);
    	$notification = array(
    		'message' => 'Product Inserted Successfully',
    		'alert-type' => 'success'
    	);

        return redirect()->route('product.all')->with($notification);
    }//End Method

    public function ProductEdit($id){
    	$supplier = supplier::all();
    	$category = Category::all();
    	$unit = Unit::all();
		$product = Product::findorFail($id);
		return view('backend.product.product_edit', compact('product','supplier','category','unit'));
    }//End Method

    public function ProductUpdate(Request $request){
    	$product_id = $request->id;
		Product::findorFail($product_id)->update([
    		'name' => $request->name,
    		'supplier_id' => $request->supplier_id,
    		'category_id' => $request->category_id,
    		'unit_id' => $request->unit_id,
    		'updated_by' => Auth::user()->id,
    		'updated_at' => Carbon::now(),
    	]);
    	$notification = array(
    		'message' => 'Product Updated Successfully',
    		'alert-type' => 'success'
    	);

        return redirect()->route('product.all')->with($notification);
	}//End Method

	public function ProductDelete($id){
		Product::findorFail($id)->delete();
		$notification = array(
    		'message' => 'Product Deleted Successfully',
    		'alert-type' => 'success'
    	);

        return redirect()->back()->with($notification);
    }//End Method
}
