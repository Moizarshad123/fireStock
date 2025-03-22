<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function payments(Request $request) {
    
    
        $payments = Payment::where('user_id', auth()->user()->id)->where('status', "Clear")->orderByDESC('id')->get();
        $pending_payments = Payment::where('user_id', auth()->user()->id)->where('status', "Pending")->orderByDESC('id')->get();

        $arr["pending"] = $pending_payments;
        $arr["clear"]   = $payments;

        return $this->success($arr);
    }

    public function addPayment(Request $request) {
        try {
            
            if($request->id == null) {
                $validator = Validator::make($request->all(), [
                    'title' => 'required',
                    'price' => 'required',
                ]);
                if ($validator->fails()){
                    return $this->error('Validation Error', 429, [], $validator->errors());
                }
                $image = "";
                if ($request->has('image') && $request->image != null) {
    
                    $dir      = "uploads/payments/";
                    $file     = $request->file('image');
                    $fileName = time().'-payments.'.$file->getClientOriginalExtension();
                    $file->move($dir, $fileName);
                    $fileName = $dir.$fileName;
                    $image    = asset($fileName);
                }
    
                Payment::create([
                    "user_id"=>auth()->user()->id,
                    "title"=>$request->title,
                    "description"=>$request->description,
                    "price"=>$request->price,
                    "image"=>$image,
                    "status"=>$request->status
                ]);
    
                return $this->success([], "Payment Created");

            } else {

                $payment = Payment::find($request->id);
                if(!$payment) {
                    return $this->error("No Record Found");
                }

                if ($request->has('image') && $request->image != null) {
    
                    $dir      = "uploads/payments/";
                    $file     = $request->file('image');
                    $fileName = time().'-payments.'.$file->getClientOriginalExtension();
                    $file->move($dir, $fileName);
                    $fileName = $dir.$fileName;

                    $payment->image=$image;
                }
    
              
                $payment->title=$request->title;
                $payment->description=$request->description;
                $payment->price=$request->price;
                $payment->status=$request->status;
                $payment->save();
    
                return $this->success($payment, "Payment Updated");
            }

        } catch (\Exception $e) {
           return $this->error($e->getMessage());
        }
    }

    public function updatePayment(Request $request) {
        try {
            
            $validator = Validator::make($request->all(), [
                'payment_id' => 'required',
            ]);
            if ($validator->fails()){
                return $this->error('Validation Error', 429, [], $validator->errors());
            }
            $image = "";
            if ($request->has('image') && $request->image != null) {

                $dir      = "uploads/payments/";
                $file     = $request->file('image');
                $fileName = time().'-payments.'.$file->getClientOriginalExtension();
                $file->move($dir, $fileName);
                $fileName = $dir.$fileName;
                $image    = asset($fileName);
            }

            $payment = Payment::find($request->payment_id);
            $payment->title=$request->title;
            $payment->description=$request->description;
            $payment->price=$request->price;
            $payment->image=$image;
            $payment->status=$request->status;
            $payment->save();

            return $this->success($payment, "Payment Updated");

        } catch (\Exception $e) {
           return $this->error($e->getMessage());
        }
    }
}
