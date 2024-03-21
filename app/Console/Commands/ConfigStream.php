<?php

namespace App\Console\Commands;

use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConfigStream extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:stream {configData}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the command to configure the stream';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Access the passed data
        $configData = $this->argument('configData');
        Log::channel('stream')->info($configData['stream_name'] . ': Configuring the stream...');

        // check m3u8 directory
        if (!file_exists($configData['m3u8_directory'])) {
            mkdir($configData['m3u8_directory']);
            chmod($configData['m3u8_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': M3U8 directory created successfully.');

        // check rtmp directory
        if (!file_exists($configData['rtmp_server_directory'])) {
            mkdir($configData['rtmp_server_directory']);
            chmod($configData['rtmp_server_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': RTMP directory created successfully.');

        // check rtmp file
        if (!file_exists($configData['rtmp_server_file_directory'])) {
            // Read the content of the file
            $rtmpServerFileContent = file_get_contents(resource_path('stream/rtmp.conf'));

            // Define the replacements
            $replacements = [
                '!!RTMP_APP_NAME!!'  => $configData['rtmp_app_name'],
                '!!M3U8_DIRECTORY!!' => $configData['m3u8_directory'],
            ];

            // Replace placeholders
            $rtmpServerFileContent = str_replace(array_keys($replacements), array_values($replacements), $rtmpServerFileContent);

            // Write the modified content back to the file
            file_put_contents($configData['rtmp_server_file_directory'], $rtmpServerFileContent);
            chmod($configData['rtmp_server_file_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': RTMP file created successfully.');

        // check lua directory
        if (!file_exists($configData['lua_directory'])) {
            mkdir($configData['lua_directory']);
            chmod($configData['lua_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': Lua directory created successfully.');

        // check lua hls file
        if (!file_exists($configData['lua_hls_file_directory'])) {
            // Read the content of the file
            $luaFileContent = file_get_contents(resource_path('stream/hls.lua'));

            // Define the replacements
            $replacements = [
                '!!STREAM_NAME!!' => $configData['stream_name'],
            ];

            // Replace placeholders
            $luaFileContent = str_replace(array_keys($replacements), array_values($replacements), $luaFileContent);

            // Write the modified content back to the file
            file_put_contents($configData['lua_hls_file_directory'], $luaFileContent);
            chmod($configData['lua_hls_file_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': Lua HLS file created successfully.');

        // check lua stat file
        if (!file_exists($configData['lua_stat_file_directory'])) {
            // Read the content of the file
            $luaFileContent = file_get_contents(resource_path('stream/stat.lua'));

            // Define the replacements
            $replacements = [
                '!!STREAM_NAME!!' => $configData['stream_name'],
            ];

            // Replace placeholders
            $luaFileContent = str_replace(array_keys($replacements), array_values($replacements), $luaFileContent);

            // Write the modified content back to the file
            file_put_contents($configData['lua_stat_file_directory'], $luaFileContent);
            chmod($configData['lua_stat_file_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': Lua stat file created successfully.');

        // check hls directory
        if (!file_exists($configData['hls_server_directory'])) {
            mkdir($configData['hls_server_directory']);
            chmod($configData['hls_server_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': HLS directory created successfully.');

        // check hls file
        if (!file_exists($configData['hls_server_file_directory'])) {
            // Read the content of the file
            $hlsServerFileContent = file_get_contents(resource_path('stream/hls.conf'));

            // Define the replacements
            $replacements = [
                '!!HLS_SERVER_NAME!!'         => $configData['hls_server_name'],
                '!!M3U8_DIRECTORY!!'          => $configData['m3u8_directory'],
                '!!LUA_HLS_FILE_DIRECTORY!!'  => $configData['lua_hls_file_directory'],
                '!!LUA_STAT_FILE_DIRECTORY!!' => $configData['lua_stat_file_directory'],
                '!!ERROR_LOG_DIRECTORY!!'     => $configData['error_log_directory'],
                '!!ACCESS_LOG_DIRECTORY!!'    => $configData['access_log_directory'],
                '!!BANDWIDTH_LOG_DIRECTORY!!' => $configData['bandwidth_log_directory'],
            ];

            // Replace placeholders
            $hlsServerFileContent = str_replace(array_keys($replacements), array_values($replacements), $hlsServerFileContent);

            // Write the modified content back to the file
            file_put_contents($configData['hls_server_file_directory'], $hlsServerFileContent);
            chmod($configData['hls_server_file_directory'], 0755);
        }

        Log::channel('stream')->info($configData['stream_name'] . ': HLS file created successfully.');

        // Check Nginx configuration syntax
        //exec('/usr/local/nginx/sbin/nginx -t');

        // check source url exists
        if (!empty($configData['source_url'])) {
            // Run ffmpeg command and get the PID
            exec($configData['ffmpeg_cmd']);

            Log::channel('stream')->info($configData['stream_name'] . ': FFMPEG command executed successfully.');

            $sourceUrl   = $configData['source_url'];
            $killCommand = "kill $(pgrep -f 'ffmpeg.*-i $sourceUrl')";

            if (!empty($ffmpegPid)) {
                // Update the config
                $config = Config::find($configData['id'])
                    ->update([
                        'ffmpeg_kill_command' => $killCommand,
                        'status'              => 1,
                    ]);

                Log::channel('stream')->info($configData['stream_name'] . ': FFMPEG kill command updated successfully.');
            }
        } else {
            // Update the config
            $config = Config::find($configData['id'])
                ->update([
                    'status' => 1,
                ]);

            Log::channel('stream')->info($configData['stream_name'] . ': Config updated successfully.');
        }

        // Reload Nginx
        exec('sudo /usr/local/nginx/sbin/nginx -s reload');
        Log::channel('stream')->info($configData['stream_name'] . ': Nginx configuration syntax check passed and Nginx reloaded successfully.');

        // Create config
        $config = Config::create($configData);
        if ($config) {
            Log::channel('stream')->info($configData['stream_name'] . ': Config created successfully.');
        } else {
            Log::channel('stream')->error($configData['stream_name'] . ': Config creation failed.');
        }
    }
}
