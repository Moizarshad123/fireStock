<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = ["user_id", "name", "count", "image"];

    public function manager() {
        return $this->hasOne(User::class, 'id','user_id');
    }
}
