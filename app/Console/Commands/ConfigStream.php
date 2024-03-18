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
        Log::channel('stream')->info('Configuring the stream...');

        // Access the passed data
        $configData = $this->argument('configData');

        // check m3u8 directory
        if (!file_exists($configData['m3u8_directory'])) {
            mkdir($configData['m3u8_directory']);
            chmod($configData['m3u8_directory'], 0755);
        }

        Log::channel('stream')->info('M3U8 directory created successfully.');

        // check rtmp directory
        if (!file_exists($configData['rtmp_server_directory'])) {
            mkdir($configData['rtmp_server_directory']);
            chmod($configData['rtmp_server_directory'], 0755);
        }

        Log::channel('stream')->info('RTMP directory created successfully.');

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

        Log::channel('stream')->info('RTMP file created successfully.');

        // check lua directory
        if (!file_exists($configData['lua_directory'])) {
            mkdir($configData['lua_directory']);
            chmod($configData['lua_directory'], 0755);
        }

        Log::channel('stream')->info('Lua directory created successfully.');

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

        Log::channel('stream')->info('Lua HLS file created successfully.');

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

        Log::channel('stream')->info('Lua stat file created successfully.');

        // check hls directory
        if (!file_exists($configData['hls_server_directory'])) {
            mkdir($configData['hls_server_directory']);
            chmod($configData['hls_server_directory'], 0755);
        }

        Log::channel('stream')->info('HLS directory created successfully.');

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

        Log::channel('stream')->info('HLS file created successfully.');

        // Check Nginx configuration syntax
        $configCheckResult = shell_exec('/usr/local/nginx/sbin/nginx -t');

        // If configuration syntax is correct, reload Nginx
        if (str_contains($configCheckResult, 'successful')) {

            // check source url exists
            if (!empty($configData['source_url'])) {
                // Run ffmpeg command and get the PID
                $ffmpegPid = shell_exec($configData['ffmpeg_cmd']);

                if (!empty($ffmpegPid)) {
                    // Update the config
                    $config = Config::find($configData['id'])
                        ->update([
                            'ffmpeg_pid' => $ffmpegPid,
                            'status'     => '1',
                        ]);
                }
            }

            // Reload Nginx
            exec('/usr/local/nginx/sbin/nginx -s reload');
            Log::channel('stream')->info('Nginx configuration syntax check passed and Nginx reloaded successfully.');
        }
        else {
            Log::channel('stream')->error('Nginx configuration syntax check failed!');
        }
    }
}
