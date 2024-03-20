<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class ConfigController extends Controller
{
    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = Config::latest();

            // Filtering
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $data->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('url', 'like', "%{$searchValue}%")
                        ->orWhere('status', 'like', "%{$searchValue}%");
                    // Add other fields as needed
                });
            }

            // Get total records count
            $totalRecords = $data->count();

            // Paging
            $start  = $request->start;
            $length = $request->length;
            $data->skip($start)->take($length);

            // Get filtered records count
            $filteredRecords = $data->count();

            // Ordering
            if ($request->order && count($request->order)) {
                $orderBy  = $request->columns[$request->order[0]['column']]['data'];
                $orderDir = $request->order[0]['dir'];
                $data->orderBy($orderBy, $orderDir);
            }

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
                if (file_exists($config->m3u8_file_directory)) {
                    // font awesome icon for play with link and red icon
                    $status = "<a href='" . $config->hls_url . "' target='_blank'><i class='fa fa-play' style='color: red;'></i></a>";
                    $stop   = "<a href='" . route('config.destroy', $config->id) . "'><i class='fa fa-stop' style='color: red;'></i></a>";
                }

                $action = "<div>
                            <a href='" . route('config.show', $config->id) . "' style='margin-right: 5px;'><i class='fa fa-eye' style='color: red;'></i></a>
                            $stop
                        </div>";

                // get active users count from redis
                $activeUsers = Redis::connection('default')->keys("{$config->stream_name}_session_tokens:*");

                //check app is in local
                if (!file_exists($config->bandwidth_log_directory)) {
                    $bandwidth = [
                        'incoming_bandwidth' => 0,
                        'outgoing_bandwidth' => 0
                    ];
                } else {
                    // get bandwidth from log files
                    $bandwidth = $this->getBandwidth($config->bandwidth_log_directory);
                }

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
            $response = [
                "draw"            => intval($request->draw),
                "recordsTotal"    => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data"            => $configs,
            ];

            return response()->json($response);
        }

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
        $serverIp = file_get_contents('https://api.ipify.org');

        $givenName  = preg_replace('/[^a-zA-Z0-9\/]/', '', trim($request->given_name));
        $streamName = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', trim($request->given_name)));

        $rtmpAppName             = $givenName;
        $rtmpUrl                 = "rtmp://$serverIp/$givenName";
        $rtmpServerDirectory     = "/usr/local/nginx/conf/rtmp.d";
        $rtmpServerFileDirectory = "/usr/local/nginx/conf/rtmp.d/$streamName.conf";

        $hlsServerName          = $givenName;
        $hlsUrl                 = "http://$serverIp/$givenName/stream.m3u8";
        $hlsServerDirectory     = "/usr/local/nginx/conf/http.d";
        $hlsServerFileDirectory = "/usr/local/nginx/conf/http.d/$streamName.conf";

        $luaDirectory         = "/usr/local/nginx/conf/lua.d";
        $luaHlsFileDirectory  = "/usr/local/nginx/conf/lua.d/$streamName" . "_hls.lua";
        $luaStatFileDirectory = "/usr/local/nginx/conf/lua.d/$streamName" . "_stat.lua";

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
        $config = Config::findOrFail($id);

        // run the ffmpeg_kill_command
        exec($config->ffmpeg_kill_command);

        $config->delete();
    }

    private function getBandwidth($logDir)
    {
        // Open the log file for reading
        $logFile = fopen($logDir, 'r');

        // Initialize variables to store incoming and outgoing bandwidth
        $incomingBandwidth = 0;
        $outgoingBandwidth = 0;

        // Read the log file line by line
        while ($line = fgets($logFile)) {
            // Parse the line based on the log format
            $logParts = explode(' ', $line);

            $incomingBandwidth     += intval($logParts[24]);
            $outgoingBandwidth     += intval($logParts[25]);
        }

        // Close the log file
        fclose($logFile);

        return [
            'incoming_bandwidth' => round($incomingBandwidth / (1024 * 1024), 2),
            'outgoing_bandwidth' => round($outgoingBandwidth / (1024 * 1024), 2)
        ];
    }
}
