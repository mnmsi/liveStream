<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = [
        'stream_name',
        'ip_address',
        'user_agent',
        'token',
        'created_at',
        'updated_at',
    ];
}
