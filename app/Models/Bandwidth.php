<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bandwidth extends Model
{
    protected $fillable = [
        'stream_name',
        'ip_address',
        'incoming_bandwidth',
        'outgoing_bandwidth',
        'created_at',
        'updated_at',
    ];
}
