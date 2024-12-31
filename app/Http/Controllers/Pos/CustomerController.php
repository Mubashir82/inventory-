<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Auth;
use Illuminate\Support\Carbon;
use Image;

class CustomerController extends Controller
{
    public function CustomerAll(){
		$customers = Customer::latest()->get();
    	return view('backend.customer.customer_all', compact('customers'));    	
    }//End Method

    public function CustomerAdd(){
		return view('backend.customer.customer_add');    	
    }//End Method

    public function CustomerStore(Request $request){
    	$image = $request->file('customer_image');
    	$name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
    	Image::make($image)->resize(200,200)->save('uploads/customer/'.$name_gen);
    	$save_url = $name_gen;
    	Customer::insert([
    		'name' => $request->name,
    		'mobile_no' => $request->mobile_no,
    		'email' => $request->email,
    		'address' => $request->address,
    		'customer_image' => $save_url,
            'created_by' => Auth::user()->id,
            'created_at' => Carbon::now(),
    	]);
    	$notification = array(
    		'message' => 'Customer Inserted Successfully',
    		'alert-type' => 'success'
    	);

        return redirect()->route('customer.all')->with($notification);
    }//End Method

    public function CustomerEdit($id){
        $customer = Customer::findorFail($id);
        return view('backend.customer.customer_edit',compact('customer'));
    }//End Method

    public function CustomerUpdate(Request $request){
        $customer_id = $request->id;
        if ($request->file('customer_image')) {
            $image = $request->file('customer_image');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize(200,200)->save('uploads/customer/'.$name_gen);
        $save_url = $name_gen;
        Customer::findorFail($customer_id)->update([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'customer_image' => $save_url,
            'updated_by' => Auth::user()->id,
            'updated_at' => Carbon::now(),
            ]);
        $notification = array(
            'message' => 'Customer Updated With Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('customer.all')->with($notification);
        } else {
            Customer::findorFail($customer_id)->update([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'email' => $request->email,
            'address' => $request->address,
            'updated_by' => Auth::user()->id,
            'updated_at' => Carbon::now(),
            ]);
        $notification = array(
            'message' => 'Customer Updated Without Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('customer.all')->with($notification);
        }//End Else
    }//End Method

    public function CustomerDelete($id){
        $customer = Customer::findorFail($id);
        $img = $customer->customer_image;
        $filePath = public_path('uploads/customer/'.$img);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $notification = array(
            'message' => 'Customer Deleted Successfully',
            'alert-type' => 'success'
        );
        Customer::FindOrFail($id)->delete();

        return redirect()->back()->with($notification);
    }//End Method

    public function CreditCustomer()
    {
        $allData = Payment::wherein('paid_status',['full_due','partial_paid'])->get();
        return view('backend.customer.customer_credit',compact('allData'));
    }//End Method

    public function CreditCustomerPrintPdf(Request $request)
    {
        $allData = Payment::wherein('paid_status',['full_due','partial_paid'])->get();
        return view('backend.pdf.customer_credit_pdf',compact('allData'));
    }//End Method

    public function CustomerEditInvoice($invoice_id){
        $payment = Payment::where('invoice_id',$invoice_id)->first();
        return view('backend.customer.edit_customer_invoice',compact('payment'));
    }//End Method

    public function CustomerUpdateInvoice(Request $request,$invoice_id)
    {
        if ($request->new_paid_amount < $request->paid_amount) {
          $notification = array(
            'message' => 'Sorry You Paid A Maximum Value',
            'alert-type' => 'error'
        );  
          return redirect()->back()->with($notification);
        }else{
            $payment = Payment::where('invoice_id',$invoice_id)->first();
            $payment_details = new PaymentDetail();
            $payment->paid_status = $request->paid_status;
            if ($request->paid_status == 'full_paid') {
                $payment->paid_amount = Payment::where('invoice_id',$invoice_id)->first()['paid_amount']+$request->new_paid_amount;
                $payment->due_amount = '0';
                $payment_details->current_paid_amount = $request->new_paid_amount; 
            }elseif ($request->paid_status == 'partial_paid') {
                $payment->paid_amount = Payment::where('invoice_id',$invoice_id)->first()['paid_amount']+$request->paid_amount;
                $payment->due_amount = Payment::where('invoice_id',$invoice_id)->first()['paid_amount']-$request->paid_amount;
                $payment_details->current_paid_amount = $request->paid_amount;                 
            }
            $payment->save();
            $payment_details->invoice_id = $invoice_id;
            $payment_details->date = date('Y-m-d',strtotime($request->date));
            $payment_details->updated_by = Auth::user()->id;
            $payment_details->save();

            $notification = array(
                'message' => 'Invoice Updated Successfully',
                'alert-type' => 'success'
            );  
          return redirect()->route('credit.customer')->with($notification);
        }
    }//End Method

    public function CustomerInvoiceDetailsPdf($invoice_id)
    {
        $payment = Payment::where('invoice_id',$invoice_id)->first();
        return view('backend.pdf.invoice_details_pdf',compact('payment'));    
    }//End Method

    public function PaidCustomer(){
        $allData = Payment::where('paid_status','!=','full_due')->get();
        return view('backend.customer.customer_paid',compact('allData'));
    }//End Method

    public function PaidCustomerPrintPdf(){
        $allData = Payment::where('paid_status','!=','full_due')->get();
        return view('backend.pdf.customer_paid_pdf',compact('allData'));
    }//End Method

    public function CustomerWiseReport()
    {
        $customers = Customer::all();
        return view('backend.customer.customer_wise_report',compact('customers'));
    }//End Method

    public function CustomerWiseCreditReport(Request $request)
    {
        $allData = Payment::where('customer_id',$request->customer_id)->whereIn('paid_status',['full_due','partial_paid'])->get();
        return view('backend.pdf.customer_wise_credit_pdf',compact('allData'));
    }//End Method

    public function CustomerWisePaidReport(Request $request)
    {
        $allData = Payment::where('customer_id',$request->customer_id)->where('paid_status','!=','full_due')->get();
        return view('backend.pdf.customer_wise_paid_pdf',compact('allData'));
    }//End Method
}