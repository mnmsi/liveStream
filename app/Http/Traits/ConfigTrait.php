<?php

namespace App\Http\Traits;

use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;

trait ConfigTrait
{
    private function getConfigs(array $params): array
    {
        // Calculate start time as current time
        $endTime = Carbon::now();

        // Calculate end time by subtracting 5 seconds from the start time
        $startTime = $endTime->copy()->subSeconds(5);

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
        $configs = $configs->map(function ($config) use ($startTime, $endTime){

            $rtmpShow = empty($config->source_url) ? "<li>$config->rtmp_url</li>" : '';
            $sourceUrlDt = !empty($config->source_url)
                            ?  "<dt>Source Url</dt>
                                <ol>
                                    <li>$config->source_url</li>
                                </ol>"
                            : '';

            $info = "<div>
                            <p style='font-weight: 800;margin: 0;font-size: 25px;'>$config->given_name</p>
                            <dl>
                                $sourceUrlDt
                                <dt>Output Urls</dt>
                                <ol>
                                    $rtmpShow
                                    <li>$config->hls_url</li>
                                </ol>
                            </dl>
                        </div>";

            // check hls directory for m3u8 files at m3u8_file_directory
            $status = "<img src='" . asset('assets/img/Double Ring-1s-200px.gif') . "' alt='loading' height=30px width=30px />";
            if (count(File::glob("{$config->m3u8_directory}/*.m3u8")) > 0) {
                $status = "<a href='" . $config->hls_url . "' target='_blank'><i class='fa fa-play' style='color: red;'></i></a>";
            }

            $action = "<div>
                            <a href='" . route('config.edit', $config->id) . "' style='margin-right: 5px;'><i class='fa fa-edit' style='color: red;'></i></a>
                            <a href='" . route('config.destroy', $config->id) . "'><i class='fa fa-stop' style='color: red;'></i></a>
                        </div>";

            // get active users count from redis
            $activeUsers = Redis::connection('default')->keys("{$config->stream_name}_session_tokens:*");

            // get bandwidth from log files
            $bandwidth = $this->getBandwidth($config->bandwidth_log_directory, $startTime, $endTime);

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
        $sourceUrl  = $this->removeTrailingSlash($source_url);

        $configData = [
            'given_name'  => $givenName,
            'stream_name' => $streamName,

            'rtmp_app_name'              => $givenName,
            'rtmp_url'                   => "rtmp://$serverIp:1936/$givenName",
            'rtmp_server_directory'      => "/usr/local/nginx/conf/rtmp.d",
            'rtmp_server_file_directory' => "/usr/local/nginx/conf/rtmp.d/$streamName.conf",

            'hls_server_name'           => $givenName,
            'hls_url'                   => $sourceUrl ? "http://$serverIp:8880/$givenName/stream.m3u8" : "http://$serverIp:8880/$givenName/index.m3u8",
            'hls_server_directory'      => "/usr/local/nginx/conf/http.d",
            'hls_server_file_directory' => "/usr/local/nginx/conf/http.d/$streamName.conf",

            'lua_directory'           => "/usr/local/nginx/conf/lua.d",
            'lua_hls_file_directory'  => "/usr/local/nginx/conf/lua.d/$streamName" . "_hls.lua",
            'lua_stat_file_directory' => "/usr/local/nginx/conf/lua.d/$streamName" . "_stat.lua",

            'source_url' => $source_url,

            'm3u8_directory'      => "/tmp/$streamName",
            'm3u8_file_directory' => $sourceUrl ? "/tmp/$streamName/stream.m3u8" : "/tmp/$streamName/index.m3u8",
            'm3u8_log_directory'  => "/tmp/$streamName/ffmpeg.log",

            'access_log_directory'    => "/usr/local/nginx/logs/$streamName" . "_access.log",
            'error_log_directory'     => "/usr/local/nginx/logs/$streamName" . "_error.log",
            'bandwidth_log_directory' => "/usr/local/nginx/logs/$streamName" . "_bw.log",

            'ffmpeg_cmd' => $sourceUrl ? "nohup ffmpeg -i '$sourceUrl' -c:v copy -c:a copy -hls_time 10 -hls_list_size 6 -hls_wrap 10 -f hls /tmp/$streamName/stream.m3u8 > /tmp/$streamName/ffmpeg.log 2>&1 &" : null,
        ];


        if (request()->ip() != '127.0.0.1') {
            // Run config:stream command in the background
            Artisan::call('config:stream', ['configData' => $configData]);
        }

        return true;
    }

    private function updateConfig($id, array $params): bool
    {
        //Destroy the config
        $this->destroyConfig($id);

        return $this->storeConfig($params);
    }

    private function destroyConfig($id)
    {
        // Find the configuration
        $config = Config::findOrFail($id);

        // check run the ffmpeg_kill_command
        if ($config->ffmpeg_kill_command) {
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
            File::deleteDirectory($config->m3u8_directory);
        }

        // Delete the configuration and return the result
        return $config->delete();
    }
}
