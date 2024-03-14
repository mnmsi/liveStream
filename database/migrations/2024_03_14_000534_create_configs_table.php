<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('given_name');
            $table->string('stream_name');

            $table->string('rtmp_app_name');
            $table->string('rtmp_url');
            $table->string('rtmp_server_directory')->default('/usr/local/nginx/rtmp.d');
            $table->string('rtmp_server_file_directory');

            $table->string('hls_server_name');
            $table->string('hls_url');
            $table->string('hls_server_directory')->default('/usr/local/nginx/html.d');
            $table->string('hls_server_file_directory');

            $table->string('lua_directory')->default('/usr/local/nginx/lua.d');
            $table->string('lua_hls_file_directory');
            $table->string('lua_stat_file_directory');

            $table->string('source_url')->nullable();

            $table->string('m3u8_directory')->nullable();
            $table->string('m3u8_file_directory')->nullable();
            $table->string('m3u8_log_directory')->nullable();

            $table->string('access_log_directory')->nullable();
            $table->string('error_log_directory')->nullable();
            $table->string('bandwidth_log_directory')->nullable();

            $table->string('ffmpeg_cmd')->nullable();
            $table->string('ffmpeg_pid')->nullable();

            $table->string('status')->default(0);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
