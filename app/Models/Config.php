<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'given_name',
        'stream_name',

        'rtmp_app_name',
        'rtmp_url',
        'rtmp_server_directory',
        'rtmp_server_file_directory',

        'hls_server_name',
        'hls_url',
        'hls_server_directory',
        'hls_server_file_directory',

        'lua_directory',
        'lua_hls_file_directory',
        'lua_stat_file_directory',

        'source_url',

        'm3u8_directory',
        'm3u8_file_directory',
        'm3u8_log_directory',

        'access_log_directory',
        'error_log_directory',
        'bandwidth_log_directory',

        'ffmpeg_cmd',
        'ffmpeg_pid',

        'status',
    ];
}
