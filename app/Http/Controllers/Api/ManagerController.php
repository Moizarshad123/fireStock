<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Notifications;

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
            $member = Member::create([
                "added_by"=>auth()->user()->id,
                "name"=>$request->name,
                "email"=>$request->email,
                "phone"=>$request->phone
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
                                        ->where('is_read', 0)
                                        ->orderByDESC('id')->get();
        return $this->success($notifications);
    }
}
