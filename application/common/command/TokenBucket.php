<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\common\enum\Redis as RedisEnum;
use app\common\expand\NetworkTrafficUtils;

/**
 * 令牌桶限流，令牌生成器
 */
class TokenBucket extends Command
{
    private $_max            = 0;  // 最大令牌数
    private $_num            = 0;  // 加入的令牌数
    private $_redis_list_key = ''; // 令牌桶

    public function __construct()
    {
        parent::__construct();
        $this->_max            = config('bucket.max');
        $this->_redis_list_key = RedisEnum::BUCKET_FOLDER . config('bucket.key');
    }

    protected function configure()
    {
        $this->setName(class_basename($this))
            ->addArgument('num', Argument::OPTIONAL, "此次添加数")
            ->addArgument('max', Argument::OPTIONAL, "最大令牌数")
            ->addOption('reset', null, Option::VALUE_NONE, '重置令牌桶')
            ->setDescription('令牌桶');
    }

    protected function execute(Input $input, Output $output)
    {
        $num = intval(trim($input->getArgument('num')));
        $max = intval(trim($input->getArgument('max')));

        !$max ?: $this->_max = $max;
        !$num ?: $this->_num = $num;

        if ($input->hasOption('reset')) {
            $this->reset($output);
            return;
        }

        $output->writeln('添加了' . $this->add() . '个令牌；' . date('Y-m-d H:i:s'));
    }

    /**
     * 加入令牌
     * @return int
     */
    private function add(): int
    {
        return NetworkTrafficUtils::executeLuaScript('AddToken', [
            $this->_redis_list_key,
            $this->_num,
            $this->_max
        ], false);
    }

    /**
     * 重置令牌桶，填满令牌
     * @param Output $output
     * @return void
     */
    private function reset(Output $output): void
    {
        $result = NetworkTrafficUtils::executeLuaScript('ResetBucket', [
            $this->_redis_list_key,
            $this->_num,
            $this->_max
        ], false);
        $output->writeln('重置成功，添加了' . $result . '个令牌；' . date('Y-m-d H:i:s'));
    }
}
