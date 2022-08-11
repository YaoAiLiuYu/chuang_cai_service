<?php

namespace App\Tools;

use App\Exceptions\ApiException;
use Exception;
use Illuminate\Support\Facades\Redis;

class Jwt
{
    private static $key = "!@#$%^%&^*I&KYJHGFBVCXZSWQ#@$%$^%&UIJKYHMNBVCXDSAWQ@#@$#%$^YUTYJHNGBVCX";

    private static $header = [
        'typ' => 'JWT',
        "alg" => 'HS256'
    ];

    public static function OutTime()
    {
        return 7200;
    }

    public static function getToken(array $payload)
    {
        if (is_array($payload)) {
            $base64header = self::base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
            $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, self::$key, self::$header['alg']);
            return $token;
        } else {
            return false;
        }
    }

    /**
     * 验证token是否有效,默认验证exp,nbf,iat时间
     * @param string $Token 需要验证的token
     * @return bool|string
     */
    public static function verifyToken(string $Token)
    {
        $tokens = explode('.', $Token);
        if (count($tokens) != 3)
            return false;
        list($base64header, $base64payload, $sign) = $tokens;
        //获取jwt算法
        $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeheader['alg']))
            return false;

        //签名验证
        if (self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign)
            return false;

        $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        //签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time())
            return false;

        //过期时间小宇当前服务器时间验证失败
        if (isset($payload['exp']) && $payload['exp'] < time())
            return false;

        //该nbf时间之前不接收处理该Token
        if (isset($payload['nbf']) && $payload['nbf'] > time())
            return false;

        return $payload;
    }

    /**
     * base64UrlEncode  https://jwt.io/ 中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    private static function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode https://jwt.io/ 中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return bool|string
     */
    public static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256签名  https://jwt.io/ 中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg 算法方式
     * @return mixed
     */
    public static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config = array(
            'HS256' => 'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }


    //验证Token是否生效
    public static function verifyTokenType($type = 'worker')
    {
        $token = Request()->header('token');
        if (is_null($token) || !$token) {
            throw new ApiException(500);
        }
        if ($type == 'worker') {
            $token_worker = Jwt::GetTokenData($token);
            $worker_token_key = 'AUTH_TOKEN_' . $token_worker['worker_id'];
            $token_data = Redis::get($worker_token_key);
            if ($token == $token_data) {
                Redis::setex($worker_token_key, self::OutTime(), $token);
            } else {
                throw new ApiException(501);
            }
        } elseif ($type == 'user') {
            $token_user = Jwt::GetTokenData($token);
            $user_token_key = 'USER_TOKEN_' . $token_user['user_id'];
            $token_data = Redis::get($user_token_key);
            if ($token == $token_data) {
                Redis::setex($user_token_key, self::OutTime(), $token);
            } else {
                throw new ApiException(502);
            }
        }
        return [];
    }


    /**
     * @Notes:获取Token数据
     * @Interface GetTokenData
     * @param $data
     * @return mixed
     * @author: Xin.Lau
     * @Time: 2022-3-12   14:55
     */
    public static function GetTokenData($data)
    {
        $token = explode(".", $data);
        $token = $token[1];
        $token = base64_decode($token);
        return json_decode($token, true);
    }
}
