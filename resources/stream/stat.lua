local redis = require "resty.redis"
local red = redis:new()

-- Connect to the Redis server
local ok, err = red:connect("127.0.0.1", 6379)
if not ok then
    ngx.log(ngx.ERR, "Failed to connect to Redis: ", err)
    ngx.exit(ngx.HTTP_INTERNAL_SERVER_ERROR)
end

-- red:flushall()

-- Retrieve session tokens
-- local session_tokens = red:keys("*")  -- Assuming session tokens are stored as keys in Redis
local session_tokens = red:keys("!!STREAM_NAME!!_session_tokens:*")
local total_session_tokens = #session_tokens

-- Retrieve total bytes in and total bytes out individually
local bytes_in = red:get("!!STREAM_NAME!!_total_bytes_in")
local bytes_out = red:get("!!STREAM_NAME!!_total_bytes_out")

-- Check if values are not nil before performing arithmetic
local mb_in = tonumber(bytes_in) or 0
local mb_out = tonumber(bytes_out) or 0

mb_in = mb_in / (1024 * 1024)  -- Convert bytes to megabytes
mb_out = mb_out / (1024 * 1024)  -- Convert bytes to megabytes

ngx.say("<html><head><title>Active Users and Bandwidth</title></head><body>")
ngx.say("<table border='1'>")
ngx.say("<tr><th>Active Users</th><th>Bandwidth In (MB)</th><th>Bandwidth Out (MB)</th></tr>")
ngx.say("<tr><td>", total_session_tokens, "</td><td>", string.format("%.2f", mb_in), "</td><td>", string.format("%.2f", mb_out), "</td></tr>")
ngx.say("</table></body></html>")

-- Close the connection to Redis
local ok, err = red:set_keepalive(10000, 100)
if not ok then
    ngx.log(ngx.ERR, "Failed to set Redis keepalive: ", err)
end
