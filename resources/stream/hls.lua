local redis = require "resty.redis"
local red = redis:new()

-- Connect to the Redis server
local ok, err = red:connect("127.0.0.1", 6379)
if not ok then
    ngx.log(ngx.ERR, "Failed to connect to Redis: ", err)
    ngx.exit(ngx.HTTP_INTERNAL_SERVER_ERROR)
end

local remote_addr = ngx.var.remote_addr

-- Check if remote_addr is available
if not remote_addr then
    ngx.status = 400
    ngx.say("Bad Request: remote_addr not available")
    ngx.exit(ngx.HTTP_BAD_REQUEST)
end

local session_token = ngx.var.cookie_session_token

-- If the token is not provided or is invalid, generate a new one
if not session_token or red:exists("!!STREAM_NAME!!_session_tokens:" .. session_token) == 0 then
    session_token = ngx.md5(ngx.var.remote_addr .. ngx.now())
    ngx.header['Set-Cookie'] = 'session_token=' .. session_token .. '; Path=/; HttpOnly'

    -- Check if the total_users key exists, if not, set it to 0
    if red:get("total_users") == ngx.null then
        red:set("total_users", 0)
    end

    -- Increment the "total_users" key
    red:incr("total_users")

    -- Extract country from IP address
    local geoip_country = ngx.req.get_headers()["X-GeoIP-Country"]

    if geoip_country then
        local country_key = "country_users:" .. geoip_country
        red:hincrby(country_key, geoip_country, 1) -- Track total users per country
    end
end

red:setex("!!STREAM_NAME!!_session_tokens:" .. session_token, 12, ngx.var.remote_addr)

-- Store user-specific in/out bandwidth
local total_bytes_in_key = "!!STREAM_NAME!!_total_bytes_in"
local total_bytes_out_key = "!!STREAM_NAME!!_total_bytes_out"

-- Check if keys exist, if not, set them to 0
if red:get(total_bytes_in_key) == ngx.null then
    red:set(total_bytes_in_key, 0)
end

if red:get(total_bytes_out_key) == ngx.null then
    red:set(total_bytes_out_key, 0)
end

local bytes_in = ngx.var.request_length
local bytes_out = ngx.var.bytes_sent

red:incrby(total_bytes_in_key, bytes_in)
red:incrby(total_bytes_out_key, bytes_out)

-- Close the connection to Redis
local ok, err = red:set_keepalive(10000, 100)
if not ok then
    ngx.log(ngx.ERR, "Failed to set Redis keepalive: ", err)
end
