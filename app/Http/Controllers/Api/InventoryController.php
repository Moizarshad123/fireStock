<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Notifications;
use App\Models\User;


class InventoryController extends Controller
{
    public function add_inventory(Request $request) {

        $image = "";
        if ($request->has('image')) {

            $dir      = "uploads/blogs/";
            $file     = $request->file('image');
            $fileName = time().'-service.'.$file->getClientOriginalExtension();
            $file->move($dir, $fileName);
            $fileName = $dir.$fileName;
            $image = asset($fileName);
        }

        if($request->id != null) {

            $inventory        = Inventory::find($request->id); 
            $inventory->name  = $request->name;
            $inventory->count = $request->count;
            $inventory->image = $image;
            $inventory->save();

        } else {
            Inventory::create([
                "user_id"=>auth()->user()->id,
                "name"=>$request->name,
                "count"=>$request->count,
                "image"=>$image
            ]);
        }
        
        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }

    public function update_inventory(Request $request) {

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required',
        ]);
        if ($validator->fails()){
            return $this->error('Validation Error', 429, [], $validator->errors());
        }

        if(auth()->user()->role_id == 2) {
            $image = "";
            $inventory = Inventory::find($request->inventory_id);
            if ($request->has('image') && $request->image != null) {
    
                $dir      = "uploads/inventory/";
                $file     = $request->file('image');
                $fileName = time().'-service.'.$file->getClientOriginalExtension();
                $file->move($dir, $fileName);
                $fileName = $dir.$fileName;
                $image = asset($fileName);
                $inventory->image=$image;
            }
            
            $inventory->name=$request->name;
            $inventory->count=$request->count;
            $inventory->save();
        } else {

            $inventory = Inventory::find($request->inventory_id);      
            $inventory->count=$request->count;
            $inventory->save();
            Notifications::create([
                'sender_id'=>auth()->user()->id,
                'receiver_id'=>$inventory->user_id,
                'title'=>"Update Inventory",
                'notification'=> $request->name.' inventory is been update by '.auth()->user()->name,
            ]);
        }

        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }

    public function inventories() {

        if(auth()->user()->role_id == 3) {
            $stationId   = Member::where('user_id', auth()->user()->id)->pluck('station_id')->first();
            if(!$stationId) {
                return $his->error("No Station ID found");
            }
            $inventories = Inventory::where("user_id", $stationId)->orderByDESC('id')->get();
        } else {
            $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        }
        return $this->success($inventories);
    }

    public function deleteInventory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'inventory_id' => 'required',
            ]);
            if ($validator->fails()){
                return $this->error('Validation Error', 200, [], $validator->errors());
            }

            $inventory = Inventory::find($request->inventory_id);
            if(!$inventory) {
                return $this->error("No Inventory Found");
            }
            $inventory->delete();

            $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
            return $this->success($inventories, "Inventory Deleted");

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
