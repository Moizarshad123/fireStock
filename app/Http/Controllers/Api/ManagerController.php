<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Member;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Notifications;
use App\Models\Payment;



class ManagerController extends Controller
{
    public function managerDashboard(Request $request) {
        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->skip(0)->take(4)->get();
        $payments    = Payment::where("user_id", auth()->user()->id)->orderByDESC('id')->skip(0)->take(2)->get();

        $arr = ["inventories"=>$inventories, "payments"=>$payments];
        return $this->success($arr);
    }

    public function addMember(Request $request) {
        try {

            $image = "";
            if ($request->has('image')) {
    
                $dir      = "uploads/blogs/";
                $file     = $request->file('image');
                $fileName = time().'-service.'.$file->getClientOriginalExtension();
                $file->move($dir, $fileName);
                $fileName = $dir.$fileName;
                $image = asset($fileName);
            }

            $user = User::create([
                "role_id"   => 3,
                "name"      => $request->name,
                "email"     => $request->email,
                "phone"     => $request->phone,
                "password"  => Hash::make($request->password),
                "image"     => $image,
                "has_station" => 1
            ]);

            $member = Member::create([
                "station_id" => auth()->user()->id,
                "user_id"    => $user->id,
                "name"       => $request->name,
                "email"      => $request->email,
                "phone"      => $request->phone,
                "image"      => $image
            ]);

            return $this->success([], "Member added successfully");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function editStation(Request $request) {
        try {

            $image = "";
            if ($request->has('logo')) {
    
                $dir      = "uploads/blogs/";
                $file     = $request->file('logo');
                $fileName = time().'-service.'.$file->getClientOriginalExtension();
                $file->move($dir, $fileName);
                $fileName = $dir.$fileName;
                $image = asset($fileName);
            }

           $station                = User::find(auth()->user()->id);
           $station->station_name  = $request->station_name;
           $station->station_image = $image;
           $station->save();

           return $this->success($station, "Station details updated");

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    public function notifications(Request $request) {
        $notifications = Notifications::with("sender")
                                        ->where('receiver_id', auth()->user()->id)
                                        // ->where('is_read', 0)
                                        ->orderByDESC('id')->get();
        return $this->success($notifications);
    }

    public function markAsRead(Request $request) {

        $validator = Validator::make($request->all(), [
            'notification_id' => 'required',
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        $notification = Notifications::find($request->notification_id);
        $notification->is_read = 1;
        $notification->save();

        $notifications = Notifications::with("sender")
                                        ->where('receiver_id', auth()->user()->id)
                                        // ->where('is_read', 0)
                                        ->orderByDESC('id')->get();

        return $this->success($notifications, "Notification marked as read");
    }

    public function members() {
        $members = Member::where('station_id', auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($members);
    }

    public function removeMember(Request $request) {

        $validator = Validator::make($request->all(), [
            'member_id' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }
        if($request->type == "single") {
            $member = Member::find($request->member_id);
            if(!$member) {
                return $this->error("Member not found with this ID");
            }
            User::where('id', $member->user_id)->delete();
            $member->delete();
        } else {
            
            $members = Member::where('station_id', auth()->user()->id)->get();
            if(count($members) > 0) {
                foreach ($members as $member) {

                    User::where('id', $member->user_id)->delete();
                    $member->delete();
                }  
            }
        }
        $members = Member::where('station_id', auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($members, "Deleted");
    }
}
