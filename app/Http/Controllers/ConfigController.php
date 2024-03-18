<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ConfigController extends Controller
{
    public function list()
    {
        return view('config.list', ['configs' => Config::all()]);
    }

    public function create()
    {
        return view('config.create');
    }

    public function store(Request $request)
    {
        // Validate the request...
        $request->validate([
            'given_name' => 'required',
            'source_url' => 'required',
        ]);

        // get the server ip address
        $serverIp = gethostbyname(gethostname());

        $givenName  = preg_replace('/[^a-zA-Z0-9\/]/', '', trim($request->given_name));
        $streamName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', trim($request->given_name)));

        $rtmpAppName             = $streamName;
        $rtmpUrl                 = "rtmp://$serverIp/$givenName";
        $rtmpServerDirectory     = "/usr/local/nginx/rtmp.d";
        $rtmpServerFileDirectory = "/usr/local/nginx/rtmp.d/$streamName.conf";

        $hlsServerName          = $streamName;
        $hlsUrl                 = "http://$serverIp/$givenName/stream.m3u8";
        $hlsServerDirectory     = "/usr/local/nginx/html.d";
        $hlsServerFileDirectory = "/usr/local/nginx/html.d/$streamName.conf";

        $luaDirectory         = "/usr/local/nginx/lua.d";
        $luaHlsFileDirectory  = "/usr/local/nginx/lua.d/$streamName" . "_hls.lua";
        $luaStatFileDirectory = "/usr/local/nginx/lua.d/$streamName" . "_stat.lua";

        $sourceUrl = $request->source_url;

        $m3u8Directory     = "/tmp/$streamName";
        $m3u8FileDirectory = "/tmp/$streamName/stream.m3u8";
        $m3u8LogDirectory  = "/tmp/$streamName/ffmpeg.log";

        $accessLogDirectory    = "/usr/local/nginx/logs/$streamName" . "_access.log";
        $errorLogDirectory     = "/usr/local/nginx/logs/$streamName" . "_error.log";
        $bandwidthLogDirectory = "/usr/local/nginx/logs/$streamName" . "_bw.log";

        $ffmpegCmd = "nohup ffmpeg -i '$sourceUrl' -c:v copy -c:a copy -hls_time 10 -hls_list_size 6 -hls_wrap 10 -f hls /tmp/$streamName/stream.m3u8 > /tmp/$streamName/ffmpeg.log 2>&1 &";

        // Create a new config...
        $config = Config::create([
            'given_name'  => $givenName,
            'stream_name' => $streamName,

            'rtmp_app_name'              => $rtmpAppName,
            'rtmp_url'                   => $rtmpUrl,
            'rtmp_server_file_directory' => $rtmpServerFileDirectory,

            'hls_server_name'           => $hlsServerName,
            'hls_url'                   => $hlsUrl,
            'hls_server_file_directory' => $hlsServerFileDirectory,

            'lua_hls_file_directory'  => $luaHlsFileDirectory,
            'lua_stat_file_directory' => $luaStatFileDirectory,

            'source_url' => $sourceUrl,

            'm3u8_directory'      => $m3u8Directory,
            'm3u8_file_directory' => $m3u8FileDirectory,
            'm3u8_log_directory'  => $m3u8LogDirectory,

            'access_log_directory'    => $accessLogDirectory,
            'error_log_directory'     => $errorLogDirectory,
            'bandwidth_log_directory' => $bandwidthLogDirectory,

            'ffmpeg_cmd' => $ffmpegCmd,
        ]);

        if ($config) {
            // Run config:stream command in the background
            Artisan::call('config:stream', ['configData' => $config->toArray()]);

            return redirect()
                ->route('config.list')
                ->with('success', 'Config created successfully!');
        }

        return redirect()
            ->route('config.create')
            ->with('error', 'Config creation failed!');

    }

    public function show($id)
    {
        return view('config.show', ['config' => Config::findOrFail($id)]);
    }

    public function edit($id)
    {
        return view('config.edit', ['config' => Config::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request...
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
        ]);

        // Update the config...
    }

    public function destroy($id)
    {
        // Delete the config...
    }
}
