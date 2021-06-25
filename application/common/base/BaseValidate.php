<?php

namespace app\common\base;

use think\Validate;

class BaseValidate extends Validate
{
    // 基类不验证场景
    // private $baseExcludeActionScene = [];

    // 获取基类不验证场景
    // public function getBaseExcludeActionScene()
    // {
    //     return $this->baseExcludeActionScene;
    // }

    // 获取子类不验证场景
    // public function getExcludeActionScene()
    // {
    //     return isset($this->excludeActionScene) ? $this->excludeActionScene : [];
    // }

    // public function __get(string $variable_name)
    // {
    //     return $this->$variable_name;
    // }

    // public function __call($method, $args)
    // {
    //     return $this->$method(...$args);
    // }

    /**
     * 获取场景内验证字段
     * @param string $scene
     * @return array
     */
    public function getSceneRule(string $scene): array
    {
        $this->getScene($scene);
        return $this->only;
    }
}
