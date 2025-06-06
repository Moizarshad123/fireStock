<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Support;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Mail, DB;

class AuthController extends Controller
{

    public function support(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ]);
            if ($validator->fails()){
                return $this->error('Validation Error', 200, [], $validator->errors());
            }
            DB::beginTransaction();
            Support::create([
                "user_id" => auth()->user()->id,
                "name"    => $request->name,
                "email"   => $request->email,
                "subject" => $request->subject,
                "message" => $request->message
            ]);
            DB::commit();
            return $this->success([], "form submitted");

        } catch (\Exception $e) {
            DB::rollback();
            return $this->error($e->getMessage());
        }
    }

    public function login(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'email'    => 'required',
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 200, [], $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                Auth::login($user);
            } else {
                return $this->error("Invalid Credentials");
            }
        } else {
            return $this->error("Invalid Credentials");
        }

        $user->api_token =  auth()->user()->createToken('API Token')->plainTextToken;
        $user->save();
        
        // $arr = [
        //         "id"    => $user->id,
        //         "name"  => $user->name,
        //         "email" => $user->email,
        //         "phone" => $user->phone,
        //         "otp"   => (int)$user->otp,
        //         "image" => $user->image,
        //         "station_name"  => $user->station_name,
        //         "station_image" => $user->station_image,
        //         "api_token"     => $user->api_token,
        //         "fcm_token"     => $user->fcm_token,
        //         "is_push_notification" => (int)$user->is_push_notification,
        //         "status"      => (int)$user->status,
        //         "is_verified" => (int)$user->is_verified
        //     ];
        return $this->success($user);
    }

    public function register(Request $request) {
        try {
           
            $check_user = User::where('email', $request->email)->first();
            $validator  = Validator::make($request->all(), [
                'user_type' => 'required',
                'phone'     => 'required',
                'email'     => 'required',
            ]);
            if ($validator->fails()) {
                return $this->error('Validation Error', 200, [], $validator->errors());
            }
            if($check_user == null) {

                DB::beginTransaction();
                $digits   = 4;
                $otpToken = rand(pow(10, $digits-1), pow(10, $digits)-1);
                // $otpToken =  1234;

                $user = User::create([
                    "role_id"   => $request->user_type,
                    "name"      => $request->name,
                    "email"     => $request->email,
                    "phone"     => $request->phone,
                    "password"  => Hash::make($request->password),
                    "otp"       => $otpToken,
                    "status"    => 0,
                ]);
                $token           = $user->createToken('API Token')->plainTextToken;
                $user->api_token = $token;
                $user->save();

                if($request->user_type == 3) {
                    $member = Member::create([
                        "user_id"    => $user->id,
                        "name"       => $request->name,
                        "email"      => $request->email,
                        "phone"      => $request->phone,
                    ]);
                }
                            
                try {
                    $response = Http::withBasicAuth('mcgrew.zach@gmail.com', '89ACA402-5D94-6FDB-2069-6D284D6EF6EE')
                                ->post('https://rest.clicksend.com/v3/sms/send', [
                                    'messages' => [
                                        [
                                            'body' => 'Firestock - Your OTP code is '.$otpToken,
                                            'to'   => $request->phone,
                                            'from' => $request->phone,
                                        ],
                                    ],
                                ]);

                } catch (\Exception $ex) {
                    DB::rollback();

                    return $this->error($ex->getMessage());
                }
                DB::commit();
                return $this->success($user,"Registered successfully");
            } else {
                return $this->error('Email is already exist', 200);
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    // public function UpdateLocation(Request $request) {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'location'=> 'required',
    //             'lat'     => 'required',
    //             'lng'     => 'required',
    //         ]);
    //         if ($validator->fails()){
    //             return $this->error('Validation Error', 200, [], $validator->errors());
    //         }

    //         $user = User::find(auth()->user()->id);
    //         $user->location = $request->location;
    //         $user->lat      = $request->lat;
    //         $user->lng      = $request->lng;
    //         $user->save();

    //         return $this->success([], "Location updated");
    //     } catch (\Exception $e) {
    //         return $this->error($e->getMessage());
    //     }
    // }

    public function resendOtpToken(Request $request) {
        $validator = Validator::make($request->all(), [
            'api_token' => 'required'
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        $user = User::where("api_token", $request->api_token)->first();
        if ($user != null) {
            $digits = 4;
            $otpToken = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            // $otpToken =  1234;
            $user->otp = $otpToken;
            $user->save();

            try {
                 $response = Http::withBasicAuth('mcgrew.zach@gmail.com', '89ACA402-5D94-6FDB-2069-6D284D6EF6EE')
                            ->post('https://rest.clicksend.com/v3/sms/send', [
                                'messages' => [
                                    [
                                        'body' => 'Firestock - Your OTP code is '.$otpToken,
                                        'to'   => $user->phone,
                                        'from' => $user->phone,
                                    ],
                                ],
                            ]);
            } catch (\Exception $ex) {
                return $this->error($ex->getMessage());
            }
            return $this->success(array("otp" => $otpToken));
        } else { 
            return $this->error('Invalid User');
        }
    }

    public function verifyToken(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp_token' => 'required',
            'api_token' => 'required'
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }
        if ($request->has('otp_token')) {
            $user = User::where("api_token", $request->api_token)->first();
            
            if (isset($user->otp) && $user->otp == $request->otp_token) {
                $user->api_token = $user->createToken('API Token')->plainTextToken;
                $user->status    = 1;
                $user->save();
                Auth::login($user);
                // $arr = [
                //     "id" => $user->id,
                //     "name" => $user->name,
                //     "email" => $user->email,
                //     "phone" => $user->phone,
                //     "otp" => (int)$user->otp,
                //     "image" => $user->image,
                //     "api_token" => $user->api_token,
                //     "fcm_token" => $user->fcm_token,
                //     "is_push_notification" => (int)$user->is_push_notification,
                //     "status" => (int)$user->status,
                //     "is_verified" => (int)$user->is_verified
                // ];
                return $this->success($user, 'Token Verified Successfully.');
            } else {
                return $this->error('Invalid OTP Token',422);
            }
        } else {
            return $this->error('OTP Token Required', 422);
        }
    }

    public function setPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'password'         => 'required',
            'confirm_password' => 'required',
            "user_id"          => 'required'
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        $user            = User::find($request->user_id);
        $user->api_token = $user->createToken('API Token')->plainTextToken;
        $user->password  = Hash::make($request->password);
        $user->save();
        Auth::login($user);
        
          $arr = [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "phone" => $user->phone,
                    "otp" => (int)$user->otp,
                    "image" => $user->image,
                    "api_token" => $user->api_token,
                    "fcm_token" => $user->fcm_token,
                    "is_push_notification" => (int)$user->is_push_notification,
                    "status" => (int)$user->status,
                    "is_verified" => (int)$user->is_verified
                ];
        
        return $this->success($arr, 'Password Set Successfully.');
    }

    // public function updateFcmToken(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'fcm_token' => 'required'
    //     ]);
    //     if ($validator->fails()){
    //         return $this->error('Validation Error', 429, [], $validator->errors());
    //     }
    //     $user = Auth::user();
    //     $user->fcm_token = $request->fcm_token;
    //     $user->save();
    //     return $this->success($user, 'FCM Token Updated Successfully.');
    // }

    public function logout(Request $request) {
        $user_id                  = Auth::user()->id;
        $update_status            = User::find($user_id);
        $update_status->is_active = 0;
        $update_status->save();

        Auth::user()->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function forgotPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if ($user != null){

            $digits          = 4;
            $otpToken        = rand(pow(10, $digits-1), pow(10, $digits)-1);
            // $otpToken        = 1234;
            $user->api_token = $user->createToken('API Token')->plainTextToken;
            $user->otp       = $otpToken;
            //$user->api_token = $user->createToken('API Token')->plainTextToken;
            $user->save();
            // try {
            //     // $messageBody = env('APP_NAME')."\nOTP token is:$otpToken";
            //     // $this->sendMessageToClient($user->phone, $messageBody);

            //     $mailData = array(
            //         'otpCode'  => $otpToken,
            //         'to'       => $request->email,
            //     );
        
            //     // Mail::send('emails.otp', $mailData, function($message) use($mailData){
            //     //     $message->to($mailData['to'])->subject('MobileApp - OTP Verification');
            //     // });

            // } catch (\Exception $ex){
            //     return $this->error($ex->getMessage());
            // }

            try {
                    $response = Http::withBasicAuth('mcgrew.zach@gmail.com', '89ACA402-5D94-6FDB-2069-6D284D6EF6EE')
                                ->post('https://rest.clicksend.com/v3/sms/send', [
                                    'messages' => [
                                        [
                                            'body' => 'Firestock - Your OTP code is '.$otpToken,
                                            'to'   => $user->phone,
                                            'from' => $user->phone,
                                        ],
                                    ],
                                ]);

                } catch (\Exception $ex) {

                    return $this->error($ex->getMessage());
                }
            
            return $this->success($user, 'OTP has been sent on your phone.');
        } else {
            return $this->error('Your Phone is not registered. Please Signup', 429);
        }
    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(),[
            "old_password" => "required",
            "password"     => "required|min:6|confirmed",
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        $user = Auth::user();

        if(Hash::check($request->old_password, $user->password)) {

            $user->password = Hash::make($request->password);
            $user->save();

            return $this->success($user, 'Password Updated Successfully');

        } else {
            return $this->error("Please enter old password correctly..!!");
        }
    }

    public function unauthenticatedUser() {
        return $this->error('Unauthorized', 401);
    }
}
