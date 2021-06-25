-- 计数器
local toboolean = function (e)
    if ((not e) or (not tonumber(e)) or (tonumber(e) <= 0)) then
        return false
    end
    return true
end
if (not tonumber(KEYS[2]) ~= false) or (not tonumber(KEYS[3]) ~= false) then
    -- 参数过滤
    return false
end
local is_exists = redis.call('exists', KEYS[1])
if (not toboolean(is_exists)) then
    redis.call('set', KEYS[1], 1)
    redis.call('expire', KEYS[1], KEYS[2])
else
    local now_num = redis.call('get', KEYS[1])
    if (now_num >= KEYS[3]) then
        return false
    end
    redis.call('incr', KEYS[1])
end
return true