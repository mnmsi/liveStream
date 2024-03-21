<?php

namespace App\Http\Traits;

use App\Models\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

trait ConfigTrait
{
    private function getConfigs(array $params): array
    {
        // Extract params
        extract($params);

        $data = Config::latest();

        if ($searchValue) {
            $data->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('url', 'like', "%{$searchValue}%")
                    ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        // Get total records count
        $totalRecords = $data->count();
        $data->skip($start)->take($length);

        // Get filtered records count
        $filteredRecords = $data->count();

        // Get the data
        $configs = $data->get();

        // take only needed fields, sl, info, active users, incoming bandwidth, outgoing bandwidth, status, action
        $configs = $configs->map(function ($config) {

            $info = "<div>
                            <p style='font-weight: 1200;margin: 0;'>$config->given_name</p>
                            <p style='margin: 0;font-size: 14px;'>$config->hls_url</p>
                        </div>";

            // check hls directory for m3u8 files at m3u8_file_directory
            $stop   = '';
            $status = "<img src='" . asset('assets/img/Double Ring-1s-200px.gif') . "' alt='loading' height=30px width=30px />";
            if (count(File::glob("{$config->m3u8_directory}/*.m3u8")) > 0) {
                $status = "<a href='" . $config->hls_url . "' target='_blank'><i class='fa fa-play' style='color: red;'></i></a>";
                $stop   = "<a href='" . route('config.destroy', $config->id) . "'><i class='fa fa-stop' style='color: red;'></i></a>";
            }

            $action = "<div>
                            <a href='" . route('config.details', $config->id) . "' style='margin-right: 5px;'><i class='fa fa-eye' style='color: red;'></i></a>
                            $stop
                        </div>";

            // get active users count from redis
            $activeUsers = Redis::connection('default')->keys("{$config->stream_name}_session_tokens:*");

            // get bandwidth from log files
            $bandwidth = $this->getBandwidth($config->bandwidth_log_directory);

            return [
                'id'                 => $config->id,
                'info'               => $info,
                'active_users'       => count($activeUsers),
                'incoming_bandwidth' => $bandwidth['incoming_bandwidth'] . ' MB',
                'outgoing_bandwidth' => $bandwidth['outgoing_bandwidth'] . ' MB',
                'status'             => $status,
                'action'             => $action,
            ];
        });

        // Prepare response
        return [
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data"            => $configs,
        ];
    }

    private function storeConfig(array $params): bool
    {
        // Extract params
        extract($params);

        // get the server ip address
        $serverIp = file_get_contents('https://api.ipify.org');

        $givenName  = preg_replace('/[^a-zA-Z0-9\/]/', '', trim($given_name));
        $streamName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', trim($given_name)));
        $sourceUrl  = $source_url;

        $rtmpAppName             = $givenName;
        $rtmpUrl                 = "rtmp://$serverIp/$givenName";
        $rtmpServerDirectory     = "/usr/local/nginx/conf/rtmp.d";
        $rtmpServerFileDirectory = "/usr/local/nginx/conf/rtmp.d/$streamName.conf";

        $hlsServerName = $givenName;

        if ($sourceUrl) {
            $hlsUrl = "http://$serverIp/$givenName/stream.m3u8";
        }
        else {
            $hlsUrl = "http://$serverIp/$givenName/index.m3u8";
        }

        $hlsServerDirectory     = "/usr/local/nginx/conf/http.d";
        $hlsServerFileDirectory = "/usr/local/nginx/conf/http.d/$streamName.conf";

        $luaDirectory         = "/usr/local/nginx/conf/lua.d";
        $luaHlsFileDirectory  = "/usr/local/nginx/conf/lua.d/$streamName" . "_hls.lua";
        $luaStatFileDirectory = "/usr/local/nginx/conf/lua.d/$streamName" . "_stat.lua";

        $m3u8Directory = "/tmp/$streamName";

        if ($sourceUrl) {
            $m3u8FileDirectory = "/tmp/$streamName/stream.m3u8";
        }
        else {
            $m3u8FileDirectory = "/tmp/$streamName/index.m3u8";
        }

        $m3u8LogDirectory = "/tmp/$streamName/ffmpeg.log";

        $accessLogDirectory    = "/usr/local/nginx/logs/$streamName" . "_access.log";
        $errorLogDirectory     = "/usr/local/nginx/logs/$streamName" . "_error.log";
        $bandwidthLogDirectory = "/usr/local/nginx/logs/$streamName" . "_bw.log";

        $ffmpegCmd = null;
        if ($sourceUrl) {
            $ffmpegCmd = "nohup ffmpeg -i '$sourceUrl' -c:v copy -c:a copy -hls_time 10 -hls_list_size 6 -hls_wrap 10 -f hls /tmp/$streamName/stream.m3u8 > /tmp/$streamName/ffmpeg.log 2>&1 &";
        }

        // Create a new config...
        $config = Config::create([
            'given_name'  => $givenName,
            'stream_name' => $streamName,

            'rtmp_app_name'              => $rtmpAppName,
            'rtmp_url'                   => $rtmpUrl,
            'rtmp_server_directory'      => $rtmpServerDirectory,
            'rtmp_server_file_directory' => $rtmpServerFileDirectory,

            'hls_server_name'           => $hlsServerName,
            'hls_url'                   => $hlsUrl,
            'hls_server_directory'      => $hlsServerDirectory,
            'hls_server_file_directory' => $hlsServerFileDirectory,

            'lua_directory'           => $luaDirectory,
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
            if (App::environment('production')) {
                Artisan::call('config:stream', ['configData' => $config->toArray()]);
            }

            return true;
        }

        return false;
    }

    private function destroyConfig($id)
    {
        // Find the configuration
        $config = Config::findOrFail($id);

        // check run the ffmpeg_kill_command
        if ($config->ffmpeg_kill_command){
            exec($config->ffmpeg_kill_command);
        }

        // Delete associated files (excluding directories) for all except m3u8
        $filesToDelete = [
            $config->access_log_directory,
            $config->error_log_directory,
            $config->bandwidth_log_directory,
            $config->lua_hls_file_directory,
            $config->lua_stat_file_directory,
            $config->rtmp_server_file_directory,
            $config->hls_server_file_directory,
        ];

        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Delete m3u8_directory
        if (is_dir($config->m3u8_directory)) {
            rmdir($config->m3u8_directory);
        }

        // Delete the configuration and return the result
        return $config->delete();
    }
}
