local sqlite3 = require("lsqlite3")
local db = sqlite3.open("/usr/local/nginx/html/admin/database/database.sqlite")

-- Extract variables
local stream_name = "!!STREAM_NAME!!"
local remote_addr = ngx.var.remote_addr
local session_token = ngx.var.cookie_session_token
local country_iso_code = ngx.var.geoip_country_iso_code
local country_name = ngx.var.geoip_country_name
local user_agent = ngx.var.http_user_agent
local incoming_bandwidth = ngx.var.incoming_bandwidth
local outgoing_bandwidth = ngx.var.outgoing_bandwidth

-- Function to increment user count for a country
local function increment_country_user_count(country_code, country)
    -- Check if the country exists in the database
    local stmt_check = db:prepare("SELECT COUNT(*) FROM country_stats WHERE stream_name = ? AND country_code = ?")
    stmt_check:bind_values(stream_name, country_code)

    -- Execute the prepared statement
    local result_check = stmt_check:step()

    -- Extract the count value from the result set
    local count = 0
    if result_check == sqlite3.ROW then
        count = stmt_check:get_value(0)
    end

    -- Finalize the prepared statement to release resources
    stmt_check:finalize()

    if count > 0 then
        -- If country exists, increment total visits
        local stmt_update = db:prepare("UPDATE country_stats SET total_visits = total_visits + 1 WHERE stream_name = ? AND country_code = ?")
        stmt_update:bind_values(stream_name, country_code)
        stmt_update:step()
        stmt_update:finalize()
    else
        -- If country doesn't exist, insert a new record
        local stmt_insert = db:prepare("INSERT INTO country_stats (stream_name, country_code, country_name, total_visits) VALUES (?, ?, ?, 1);")
        stmt_insert:bind_values(stream_name, country_code, country)
        stmt_insert:step()
        stmt_insert:finalize()
    end
end

local stmt_check = db:prepare("SELECT COUNT(*) FROM sessions WHERE stream_name = ? AND token = ?")
stmt_check:bind_values(stream_name, session_token)
local result = stmt_check:step()
local sessionCount = 0
if result == sqlite3.ROW then
    sessionCount = stmt_check:get_value(0)
end
stmt_check:finalize()


-- If the token is not provided or is invalid, generate a new one
if not session_token or sessionCount == 0 then
    session_token = ngx.md5(remote_addr .. ngx.now())

    -- Insert new session token into database
    local stmt = db:prepare("INSERT INTO sessions (stream_name, ip_address, user_agent, token) VALUES (?, ?, ?, ?);")
    stmt:bind_values(stream_name, remote_addr, user_agent, session_token)  -- Removed the extra comma here
    stmt:step()
    stmt:finalize()

	-- Increment user count for country
	if country_iso_code  then
		increment_country_user_count(country_iso_code, country_name)
	end

    -- Set session token cookie
    ngx.header['Set-Cookie'] = 'session_token=' .. session_token .. '; Path=/; HttpOnly'
end

-- Insert bandwidth data into the database
local stmt_bandwidth = db:prepare("INSERT INTO bandwidths (stream_name, ip_address, incoming_bandwidth, outgoing_bandwidth) VALUES (?, ?, ?, ?);")
stmt_bandwidth:bind_values(stream_name, remote_addr, incoming_bandwidth, outgoing_bandwidth)
stmt_bandwidth:step()
stmt_bandwidth:finalize()

-- Close the SQLite connection
db:close()


