<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentFriendRequests extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'receiver_id',
        'status'
    ];
}
