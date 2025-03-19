<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\User;
use App\Models\Inventory;

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
}
