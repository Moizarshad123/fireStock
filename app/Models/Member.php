<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "station_id",
        "name",
        "email",
        "phone",
        "image",
        "status"
    ]; 
}
