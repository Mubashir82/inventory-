<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Auth;

class SupplierController extends Controller
{
    public function SupplierAll(){
        //$suppliers = Supplier::all();
        $suppliers = Supplier::latest()->get();
        return view('backend.supplier.supplier_all',compact('suppliers'));
    } // End Method


    public function SupplierAdd(){
        return view('backend.supplier.supplier_add');
    } // End Method


    public function SupplierStore(Request $request){

        Supplier::insert([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'created_by' => Auth::user()->id,
            'created_at' => carbon::now(),
        ]);

        $notification = array(
            'message' => 'Supplier Inserted Successfully', 
            'alert-type' => 'success'
        );

        return redirect()->route('supplier.all')->with($notification);
    } // End Method


    public function SupplierEdit($id){

        $supplier = Supplier::findOrFail($id);
        return view('backend.supplier.supplier_edit',compact('supplier'));
    } // End Method


    public function SupplierUpdate(Request $request){

        $supplier_id = $request->id;
        
        Supplier::findOrFail($supplier_id)->update([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'created_by' => Auth::user()->id,
            'created_at' => carbon::now(),
        ]);

        $notification = array(
            'message' => 'Supplier Updated Successfully', 
            'alert-type' => 'success'
        );

        return redirect()->route('supplier.all')->with($notification);

    } // End Method


    public function SupplierDelete($id){

        Supplier::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Supplier Delete Successfully', 
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    } // End Method
}
