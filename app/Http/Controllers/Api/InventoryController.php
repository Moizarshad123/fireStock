<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Inventory;

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

        $image = "";
        $inventory = Inventory::find($request->inventory_id);
        if ($request->has('image')) {

            $dir      = "uploads/blogs/";
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

        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }

    public function inventories() {

        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }


    
}
