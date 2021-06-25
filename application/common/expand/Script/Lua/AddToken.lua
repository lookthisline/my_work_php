-- 往令牌桶添加令牌
if (not tonumber(KEYS[2]) ~= false) or (not tonumber(KEYS[3]) ~= false) then
    -- 参数过滤
    return false
end

local bucket  = tostring(KEYS[1]) -- 桶名称
local num     = tonumber(KEYS[2]) -- 此次添加量
local max_num = tonumber(KEYS[3]) -- 令牌桶最大容量

local function add(bucket, num, max_num)
    if (num == 0 or max_num == 0) then
        return 0
    end
    local now_num = redis.call('llen', bucket)
    local num     = max_num >= (now_num + num) and num or (max_num - now_num)
    if (num > 0) then
        for i = 1, num do
            redis.call('lpush', bucket, 1)
        end
        return num
    end
    return 0
end

return add(bucket, num, max_num)