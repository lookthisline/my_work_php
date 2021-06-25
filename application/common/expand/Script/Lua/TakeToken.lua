-- 令牌桶取令牌，不允许预消费
local length = redis.call('llen', KEYS[1])
if (length <= 0) then
    return false
end

if (not tonumber(KEYS[2]) ~= false) or (not tonumber(KEYS[3]) ~= false) then
    -- 参数过滤
    return false
end

local number      = tonumber(KEYS[2])
local max         = tonumber(KEYS[3])
local consumption = number >= max and max or number

while (consumption > 0) do
    -- 根据参数改变消耗令牌数
    redis.call('rpop', KEYS[1])
    consumption = consumption - 1
end
return true