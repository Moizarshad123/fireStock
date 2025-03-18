<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Inventory;

class MemberController extends Controller
{
    public function memberDashboard() {
        $inventories = Inventory::with("manager")->orderByDESC('id')->get();
        return $this->success($inventories);
    }
}
