<?php

namespace app\common\expand;

use \Exception;

/**
 * cUrl 工具类
 * @final
 * @internal
 */
final class CUrlUtils
{
    private static $instance;
    private $_cUrl;
    private $_url;
    private $_port;
    private $_parameter;
    private $_method                    = 'GET';
    private $_timeout                   = 0;
    private $_header                    = ['charset' => 'utf8'];
    private $_http_version              = CURL_HTTP_VERSION_NONE;
    private static $_allow_content_type = ['multipart/form-data', 'application/json', 'application/x-www-form-urlencoded'];
    private static $_allow_methods      = ['GET', 'POST', 'PATCH', 'PUT', 'HEAD', 'OPTIONS', 'DELETE'];

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
        // disable clone
    }

    /**
     * @param string $name
     * @return mixed
     */
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

    /**
     * @return self
     */
    public function setNoBody(): self
    {
        return $this->setOther(CURLOPT_NOBODY, true);
    }

    /**
     * @param array $header default []
     * @return self
     */
    public function setHeader(array $header = []): self
    {
        $this->_header = array_merge($this->_header, $header);
        return $this;
    }

    /**
     * @param integer $port
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
    public function setHttpVersion(int $version = CURL_HTTP_VERSION_1_1): self
    {
        $this->_http_version = $version ?: CURL_HTTP_VERSION_1_1;
        return $this->setOther(CURLOPT_HTTP_VERSION, $this->_http_version);
    }

    /**
     * @param boolean $is_use
     * @param string $certificate_path
     * @return self
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
     * @return self
     */
    public function setMethod(string $method = 'get', $param = []): self
    {
        $this->_method = strtoupper($method);
        if (!in_array($this->_method, self::$_allow_methods)) {
            throw new Exception('Not supported Request Method: ' . $this->_method);
        }
        if ($this->_method === 'GET') {
            $this->_parameter = $param && is_array($param) ? http_build_query($param) : '';
            $this->_url .= !$this->_parameter ? '' : '?' . $this->_parameter;
            $this->setOther(CURLOPT_HTTPGET, true);
        }
        return $this;
    }

    /**
     * @param mixed $parameter
     * @param string $content_type
     * @return self
     */
    public function setParameter($parameter, string $content_type = 'multipart/form-data'): self
    {
        if (!in_array($content_type, self::$_allow_content_type)) {
            throw new Exception('Not supported Content Type: ' . $this->_method);
        }
        $this->_header['Content-Type'] = $content_type;
        switch ($this->_header['Content-Type']) {
            case 'application/json':
                $this->_parameter = $parameter && is_array($parameter) ? json_encode($parameter, JSON_UNESCAPED_UNICODE) : '{}';
                break;
            case 'multipart/form-data':
                $this->_parameter = $parameter && is_array($parameter) ? $parameter : [];
                break;
            case 'application/x-www-form-urlencoded':
                $this->_parameter = $parameter && is_array($parameter) ? http_build_query($parameter) : '';
                break;
            default:
                break;
        }
        return $this;
    }

    /**
     * @param integer $second
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
     * @return self
     */
    public function setOther($parameter, $val): self
    {
        curl_setopt($this->_cUrl, $parameter, $val);
        return $this;
    }

    /**
     * @param boolean $require_header
     * @return string|boolean $result
     */
    public function query(bool $require_header = false)
    {
        $result = [];
        $header = [];
        $this->setOther(CURLOPT_URL, $this->_url);
        $this->setOther(CURLOPT_CUSTOMREQUEST, $this->_method);
        !$this->_parameter ?: $this->setOther(CURLOPT_POSTFIELDS, $this->_parameter);
        !$require_header ?: $this->setOther(CURLOPT_HEADER, true);
        foreach ($this->_header as $k => $v) {
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
