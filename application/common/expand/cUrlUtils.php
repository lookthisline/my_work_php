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
    private $_cUrl;
    private $_url;
    private $_port;
    private $_method = 'GET';
    private $_parameter = [];
    private $_header = [];
    private $_timeout = 0;
    private $_http_version = CURL_HTTP_VERSION_NONE;
    private $_user_agent;

    /**
     * @param string $request_url
     */
    private function __construct(string $request_url)
    {
        $this->_url  = $request_url;
        $this->_cUrl = curl_init();
        curl_setopt_array($this->_cUrl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        ]);
    }

    public function __destruct()
    {
        curl_close($this->_cUrl);
    }

    private function __clone()
    {
    }

    public function __get(string $name)
    {
        return isset($this->$name) ? $this->name : null;
    }

    /**
     * @param string $request_url
     * @param mixed $other
     * @return self
     */
    public static function instance(string $request_url): self
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($request_url);
        } else {
            self::$instance->_url = $request_url;
        }
        return self::$instance;
    }

    public function setNoBody(): self
    {
        return $this->setOther(CURLOPT_NOBODY, true);
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header = []): self
    {
        array_merge($this->_header, $header);
        return $this;
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort(int $port): self
    {
        $this->_port = $port ?: 80;
        return $this->setOther(CURLOPT_PORT, $this->_port);
    }

    /**
     * @param int $version CURL_HTTP_VERSION_NONE CURL_HTTP_VERSION_1_0 CURL_HTTP_VERSION_1_1
     * @return self
     */
    public function setHttpVersion(int $version): self
    {
        $this->_http_version = $version ?: CURL_HTTP_VERSION_1_1;
        return $this->setOther(CURLOPT_HTTP_VERSION, $this->_http_version);
    }

    /**
     * @param bool $is_use
     * @param string $certificate_path
     */
    public function setSslVerify(bool $is_use = true, string $certificate_path = ''): self
    {
        $this->setOther(CURLOPT_SSL_VERIFYPEER, $is_use);
        $this->setOther(CURLOPT_SSL_VERIFYHOST, $is_use);
        !$certificate_path ?: $this->setOther(CURLOPT_CAINFO, $certificate_path);
        return $this;
    }

    /**
     * @param string $method
     * @param mixed $param
     * @param string $content_type
     * @return self
     */
    public function setMethod(string $method = 'get', $param = []): self
    {
        $this->_method = strtolower($method);
        switch ($this->_method) {
            case 'head':
                break;
            case 'options':
                break;
            case 'put':
                $this->_header['Content-Type'] = 'application/json;';
                $this->_parameter              = $param = $param && is_array($param) ? json_encode($param, JSON_UNESCAPED_UNICODE) : '{}';
                break;
            case 'get':
                $this->_parameter = $param = $param && is_array($param) ? http_build_query($param) : [];
                $this->_url .= !$this->_parameter ? '' : '?' . $this->_parameter;
                $this->setOther(CURLOPT_HTTPGET, true);
                break;
            case 'post':
                $this->_header['Content-Type'] = 'multipart/form-data;';
                $this->_parameter              = $param = $param && is_array($param) ? $param : [];
                break;
            case 'delete':
                $this->_header['Content-Type'] = 'application/json;';
                $this->_parameter              = $param = $param && is_array($param) ? json_encode($param, JSON_UNESCAPED_UNICODE) : '{}';
                break;
            case 'patch':
                $this->_header['Content-Type'] = 'application/json;';
                $this->_parameter              = $param = $param && is_array($param) ? json_encode($param, JSON_UNESCAPED_UNICODE) : '{}';
                break;
            default:
                throw new Exception('Not supported Request Method: ' . $this->_method);
                break;
        }
        return $this;
    }

    /**
     * @param int $second
     * @return self
     */
    public function setTimeOut(int $second): self
    {
        $this->_timeout = $second ?: 6;
        $this->setOther(CURLOPT_TIMEOUT, $this->_timeout);
        return $this->setOther(CURLOPT_CONNECTTIMEOUT, $this->_timeout);
    }

    /**
     * @param mixed $parameter
     * @param mixed $val
     */
    public function setOther($parameter, $val):self
    {
        curl_setopt($this->_cUrl, $parameter, $val);
        return $this;
    }

    /**
     * @param bool $require_header
     * @return array|bool
     */
    public function query(bool $require_header = false)
    {
        $result = [];
        $header = [];
        $this->setOther(CURLOPT_URL, $this->_url);
        $this->setOther(CURLOPT_CUSTOMREQUEST, $this->_method);
        !$this->_parameter ?: $this->setOther(CURLOPT_POSTFIELDS, $this->_parameter);
        !$require_header ?: $this->setOther(CURLOPT_HEADER, true);
        foreach ($this->_header as $k=>$v) {
            array_push($header, $k . ':' . (string)$v);
        }
        !$header ?: $this->setOther(CURLOPT_HTTPHEADER, $header);
        $execute_result = curl_exec($this->_cUrl);
        if (curl_error($this->_cUrl)) {
            throw new Exception(curl_error($this->_cUrl), curl_errno($this->_cUrl));
        }
        if ($require_header) {
            if (curl_getinfo($this->_cUrl, CURLINFO_HTTP_CODE) == 200) {
                list($result['response_header'], $result['response_body']) = explode(PHP_EOL . PHP_EOL, $execute_result, 2);
            // $header_size = curl_getinfo($this->_cUrl, CURLINFO_HEADER_SIZE);  // 获取header长度
                // $result      = substr($result, $header_size);                     // 截取掉header
            } else {
                $result = false;
            }
        } else {
            if (curl_getinfo($this->_cUrl, CURLINFO_HTTP_CODE) == 200) {
                $result = $execute_result;
            } else {
                $result = false;
            }
        }
        return $result;
    }
}
