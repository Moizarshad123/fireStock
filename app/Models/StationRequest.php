<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'member_id',
        'station_id',
        'status',
    ];


    public function member() {
        return $this->hasOne(User::class, 'id', 'member_id');
    }
}
