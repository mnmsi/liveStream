<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'given_name',
        'stream_name',
        'rtmp_url',
        'hls_url',
        'source_url',
        'm3u8_directory',
        'm3u8_file_directory',
        'm3u8_log_directory',
        'hls_log_directory',
        'bandwidth_log_directory',
        'ffmpeg_cmd',
        'ffmpeg_pid',
        'status',
    ];
}
