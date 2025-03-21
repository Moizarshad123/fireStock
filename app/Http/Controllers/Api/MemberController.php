<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Member;
use App\Models\User;
use App\Models\Inventory;
use App\Models\StationRequest;
use App\Models\Notifications;


class MemberController extends Controller
{
    public function memberDashboard() {

        $member = Member::where('user_id', auth()->user()->id)->first();
        if(!$member) {
            return $this->error("Member not found");
        }
        if(auth()->user()->role_id == 3 && $member->station_id == null) {
            $stations = User::Select('id', "station_name", "station_image")->where('role_id', 2)->get();
            return $this->success($stations);
        } else {
            $inventories = Inventory::with("manager")->where('user_id', $member->station_id)->orderByDESC('id')->get();
            return $this->success($inventories);
        }
    }

    public function joinRequest(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'station_id' => 'required',
            ]);
            if ($validator->fails()){
                return $this->error('Validation Error', 429, [], $validator->errors());
            }

            StationRequest::firstOrCreate(['member_id'=>auth()->user()->id,'station_id'=>$request->station_id],[
                'member_id'=>auth()->user()->id,
                'station_id'=>$request->station_id,
                'status'=>"Pending"
            ]);


            Notifications::create([
                'sender_id'=>auth()->user()->id,
                'receiver_id'=>$request->station_id,
                'title'=>"Station Join Request",
                'notification'=> auth()->user()->name.' creates a join station request',
            ]);

            return $this->success([], "Station join request created");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
