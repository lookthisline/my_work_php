<?php

namespace app\common\expand;

/**
 * cUrl 工具
 * @final
 * @internal
 */
final class cUrlUtils
{
    private static $instance;
    private $cUrl;
    private $request_url;
    private $request_method;
    private $request_params;
    private function __construct()
    {
    }
    public static function instance()
    {
        if (!(self::$instance instanceof self)) {
            // self::$instance
        }
        return new self();
    }
    public function setMethod()
    {
        return $this;
    }
    public function query()
    {
    }
}
