<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        
        Inventory::create([
            "user_id"=>auth()->user()->id,
            "name"=>$request->name,
            "count"=>$request->count,
            "image"=>$image
        ]);
        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }

    public function inventories() {

        $inventories = Inventory::where("user_id", auth()->user()->id)->orderByDESC('id')->get();
        return $this->success($inventories);
    }


    
}
