<?php

namespace app\common\expand;

use \Exception;

/**
 * cUrl 工具
 * @final
 * @internal
 */
final class cUrlUtils
{
    private static $instance;
    private $cUrl;
    private $_url;
    private $_port = 80;
    private $_method;
    private $_params;
    private $_timeout;
    private $_http_version = CURL_HTTP_VERSION_NONE;
    private $_user_agent;

    /**
     * @param string $request_url
     */
    private function __construct(string $request_url)
    {
        $this->_url = $request_url;
        $this->cUrl = curl_init($this->_url);
        curl_setopt($this->cUrl, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($this->cUrl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($this->cUrl, CURLOPT_HTTPHEADER, []);
    }

    private function __destruct()
    {
        $this->close();
    }


    private function __clone()
    {
    }

    /**
     * @param string $request_url
     * @param mixed $other
     * @return self
     */
    public static function instance(string $request_url, $other = null): self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($request_url);
        } else {
            self::$instance->_url = $request_url;
            curl_setopt_array(self::$instance->cUrl, [
                CURLOPT_URL => $request_url
            ]);
        }
        return self::$instance;
    }

    public function setNoBody(): self
    {
        curl_setopt($this->cUrl, CURLOPT_NOBODY, true);
        return $this;
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort(int $port): self
    {
        $this->_port = $port;
        curl_setopt($this->cUrl, CURLOPT_PORT, $port);
        return $this;
    }

    /**
     * @param int $version CURL_HTTP_VERSION_NONE CURL_HTTP_VERSION_1_0 CURL_HTTP_VERSION_1_1
     * @return self
     */
    public function setHttpVersion(int $version): self
    {
        $this->_http_version = $version;
        curl_setopt($this->cUrl, CURLOPT_HTTP_VERSION, $version);
        return $this;
    }

    /**
     * @param string $method
     * @return self
     */
    public function setMethod(string $method = 'GET', array $param = []): self
    {
        $this->_method = $method;
        $param = (is_array($param)) ? http_build_query($param) : $param;
        switch (strtoupper($this->_method)) {
            case 'GET':
                curl_setopt($this->cUrl, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($this->cUrl, CURLOPT_POST, true);
                curl_setopt($this->cUrl, CURLOPT_POSTFIELDS, $param);
                break;
            default:
                throw new Exception('不支持的方法：' . $this->_method);
        }
        return $this;
    }

    public function close()
    {
        curl_close($this->cUrl);
    }

    /**
     * @param int $timeout
     */
    public function query(int $timeout = 0)
    {
        // $time   = microtime(true);
        // $expire = $time + ($timeout ?: $this->_timeout);
        //
        // $pid = pcntl_fork();
        //
        // if ($pid == -1) {
        //     die('could not fork');
        // } elseif ($pid) {
        $result        = curl_exec($this->cUrl);
        $response_code = curl_getinfo($this->cUrl, CURLINFO_HTTP_CODE);
        if (curl_error($this->cUrl)) {
            throw new Exception(curl_error($this->cUrl));
        }
        // pcntl_wait($status);
        return $result;
        // } else {
        //     while (microtime(true) < $expire) {
        //         sleep(0.5);
        //     }
        //     return false;
        // }
    }

    public function __get(string $name)
    {
        return isset($this->$name) ? $this->name : null;
    }
}
