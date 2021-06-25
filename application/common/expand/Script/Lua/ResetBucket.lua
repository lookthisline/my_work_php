-- 重置令牌桶
if (not tonumber(KEYS[2]) ~= false) or (not tonumber(KEYS[3]) ~= false) then
    -- 参数过滤
    return false
end

local bucket  = tostring(KEYS[1]) -- 桶名称
local num     = tonumber(KEYS[2]) -- 此次添加量
local max_num = tonumber(KEYS[3]) -- 令牌桶最大容量

local function toboolean(e)
    if ((not e) or (not tonumber(e)) or (tonumber(e) <= 0)) then
        return false
    end
    return true
end

local function add(bucket, num, max_num)
    if (num == 0 or max_num == 0) then
        return 0
    end
    local now_num = redis.call('llen', bucket)
    local num     = max_num > = (now_num + num) and num or (max_num - now_num)
    if (num > 0) then
        for i = 1, num do
            redis.call('lpush', bucket, 1)
        end
        return num
    end
    return 0
end

local function reset(bucket)
    local is_exists = redis.call('exists', bucket)
    if toboolean(is_exists) then
        redis.call('del', bucket)
    end
end

reset(bucket)
return add(bucket, num, max_num)
