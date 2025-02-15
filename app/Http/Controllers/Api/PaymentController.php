<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{

    public function payments(Request $request) {
        $validator = Validator::make($request->all(), [
            'type'  => 'required',
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }
        if($request->type == "Pending") {
            $payments = Payment::where('user_id', auth()->user()->id)->where('status', "Pending")->orderByDESC('id')->get();
        } else {
            $payments = Payment::where('user_id', auth()->user()->id)->where('status', "Completed")->orderByDESC('id')->get();

        }

        return $this->success($payments);
    }
    public function addPayment(Request $request) {
        try {
            //code...
            
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'price' => 'required',
            ]);
            if ($validator->fails()){
                return $this->error('Validation Error', 429, [], $validator->errors());
            }
            $image = "";
            if ($request->has('image')) {

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
                "image"=>$image
            ]);

            return $this->success([], "Payment Created");

        } catch (\Exception $e) {
           return $this->error($e->getMessage());
        }
    }
}
