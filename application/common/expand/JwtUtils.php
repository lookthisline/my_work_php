<?php

namespace app\common\expand;

final class JwtUtils
{
    protected static array $payload;

    private static array $header = [
        'algo' => 'sha256',
        'typ' => 'JWT'
    ];

    private static string $key;

    public function __construct()
    {
        self::$key = config('app.jwt_key', '');
    }

    /**
     * @access private
     * @return Void
     */
    private static function initData(): Void
    {
        // 初始化数据
        self::$payload = [
            // aud (audience)：受众，接收 jwt 的一方
            'aud'           => 'client',
            // iat (Issued At)：签发时间
            'iat'           => time(),
            // iss (issuer)：签发人
            'iss'           => 'system',
            // // exp (expiration time)：过期时间，必须要大于签发时间
            // 'exp'           => time() + 604800,
            // // nbf (Not Before)：生效时间，定义在这个时间之前，该jwt都是不可用的
            // 'nbf'           => time() + 3,
            // 在 redis 中保存的 hash key 名
            'jwt_hash_key'  => date('Ymd', time()),
            // sub (subject)：主题
            'sub'           => 'request_token',
            'auth_hash_key' => StringUtils::getUniqueCode(),
            // jti (JWT ID)：编号，jwt的唯一身份标识，主要用来作为一次性token,从而回避重放攻击。
            'jti'           => md5(uniqid('JWT', true) . time()),
        ];
    }

    /**
     * 生成 Json Web Token
     * @access public
     * @param Array $payload jwt 载荷
     * @return String header + payload + signature
     */
    public static function buildToken(array $payload = []): String
    {
        self::initData();
        if (!empty($payload) && is_array($payload)) {
            self::$payload = array_merge(self::$payload, $payload);
        }
        $base64_header  = base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
        $base64_payload = base64UrlEncode(json_encode(self::$payload, JSON_UNESCAPED_UNICODE));
        return $base64_header . '.' . $base64_payload . '.' . self::signature($base64_header . '.' . $base64_payload, self::$key, self::$header['algo']);
    }

    /**
     * 校验 Json Web Token 默认验证exp,nbf,iat时间
     * @access public
     * @param String $token
     * @return Mixed
     */
    public static function verifyToken(string $token)
    {
        self::initData();
        $tokens = explode('.', $token);
        if (count($tokens) != 3) {
            return false;
        }

        list($base64_header, $base64_payload, $sign) = $tokens;

        // 获取jwt算法
        $base64_decode_header = json_decode(base64UrlDecode($base64_header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64_decode_header['algo'])) {
            return false;
        }

        // 签名验证
        if (self::signature($base64_header . '.' . $base64_payload, self::$key, $base64_decode_header['algo']) !== $sign) {
            return false;
        }

        $payload = json_decode(base64UrlDecode($base64_payload), JSON_OBJECT_AS_ARRAY);

        // 签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            return false;
        }

        // 过期时间小于当前服务器时间验证失败
        // if (isset($payload['exp']) && $payload['exp'] < time()) {
        //     // 已过期
        //     return false;
        // }

        // 该nbf时间之前不接收处理该token
        // if (isset($payload['nbf']) && $payload['nbf'] > time()) {
        //     return false;
        // }

        return $payload;
    }

    /**
     * 签名 Json Web Token
     * @access private
     * @param String $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param String $key
     * @param String $alg 算法方式
     * @return String
     */
    private static function signature(string $input, string $key, string $algo = 'sha256'): String
    {
        return base64UrlEncode(hash_hmac($algo, $input, $key, true));
    }

    /**
     * @access public
     * @param String $name
     * @return Mixed
     */
    public function __get($name)
    {
        return self::$payload[$name];
    }
}
