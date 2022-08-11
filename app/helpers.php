<?php

use App\Consts\CommonConst;
use App\Supports\Util\Logger;
use Godruoyi\Snowflake\Snowflake;

/*
|--------------------------------------------------------------------------
| 自定义方法
|--------------------------------------------------------------------------
|
| 1. 所有自定义方法加一个自定义前缀
| 2. 方法命名方式为下划线分割方式
|
*/


if (!function_exists('qdd_make_sign')) {
    /**
     * 生成签名
     * @param array $arr
     * @param string $secret
     * @param string $time
     * @return string
     */
    function qdd_make_sign($arr = [], $secret = '', $time = '')
    {
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        if ($time) {
            $buff .= "&time={$time}";
        }
        //签名步骤二：在string后加入KEY
        if ($secret) {
            $buff .= "&key={$secret}";
        }
        //签名步骤三：MD5加密
        $string = md5($buff);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
}

if (!function_exists('strToHex')) {
    /**
     * 字符串转十六进制函数
     * @param string $str
     * @return string
     */
    function _strToHex($str = '')
    {
        $hex = "";
        for ($i = 0; $i < strlen($str); $i++)
            $hex .= dechex(ord($str[$i]));
        $hex = strtoupper($hex);
        return $hex;
    }
}
if (!function_exists('hexToStr')) {
    /**
     * 十六进制转字符串函数
     * @param string $hex
     * @return string
     */
    function _hexToStr($hex = '')
    {
        $str = "";
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $str;
    }
}
if (!function_exists('time_format')) {
    /**
     * 时间格式转化
     * @param $time
     * @param $type
     * @return string
     */
    function time_format($time = 0, $type = 0)
    {
        if ($type == 1) {
            $result = date('Y-m-d', $time);
        } else {
            $result = date('Y-m-d H:i:s', $time);
        }
        return $result;
    }
}

if (!function_exists('new_md5')) {
    /**
     * 时间格式转化
     * @param $time
     * @param $type
     * @return string
     */
    function new_md5($string)
    {
        $key = config("app.new_md5_key.key");
        return md5($string . $key);
    }
}

if (!function_exists('rmb_format')) {
    /**
     * 格式转化
     * @param $amount
     * @param $par
     * @param $type
     * @param $digit
     * @return string
     */
    function rmb_format($amount = 0, $par = 100, $type = 0, $digit = 2)
    {
        if ($type == 1) {
            $result = bcmod($amount, $par, $digit);
        } else {
            $result = bcdiv($amount, $par, $digit);
        }
        return $result;
    }
}

if (!function_exists('gen_order_sn')) {
    /**
     * @Notes: 生成订单号
     * @Interface gen_order_sn
     * @param int $num
     * @param array $sn
     * @return array|mixed
     * @author: kai.chen
     * @Time: 2020/12/5   5:21 下午
     */
    function gen_order_sn($num = null, $sn = [])
    {
        $timestamp = time();
        $y = date('y', $timestamp); //年 后两位
        $z = date('z', $timestamp); //当年已过去天数
        $s = $timestamp - strtotime(date("Y-m-d")); //当天过过去秒数
        $s = str_pad($s, 5, 0, STR_PAD_LEFT); //一天秒数长度为5 不足补0
        $z = str_pad($z, 3, 0, STR_PAD_LEFT); //一年已过去天数长度为3 不足补0
        $order_sn = $y . $z . $s; //以每秒生成一个key，用以计数
        $key = "QDD_API_COUNTER:{$order_sn}";
        if (\Illuminate\Support\Facades\Redis::TTL($key)) {
            \Illuminate\Support\Facades\Redis::EXPIRE($key, 60);
        }
        $n = $num ?? 1;
        for ($i = 0; $i < $n; $i++) {
            $sort = \Illuminate\Support\Facades\Redis::INCR($key);
            if ($sort > 9999) {
                sleep(1);
                return gen_order_sn($n - $i, $sn);
            }
            $sort = str_pad($sort, 4, 0, STR_PAD_LEFT); //每秒最大生成长度4， 不足补0
            $sort1 = substr($sort, 0, 2);
            $sort2 = substr($sort, 2);
            $sn[] = $y . $sort1 . $z . $sort2 . $s; // 年 + 自增前两位 + 一过去天数 + 自增后两位 + 过去秒数
        }
        return $num == null ? $sn[0] : $sn;
    }
}

if (!function_exists('qdd_random_str')) {
    /**
     * 生成随机字符串
     *
     * @param int $len
     * @return string
     */
    function qdd_random_str($len = 6)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = "";
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

if (!function_exists('_return_square_point')) {
    /**
     * 获取周围坐标
     * Enter description here ...
     * @param string $lng 固定坐标经度
     * @param string $lat 固定坐标纬度
     * @param int $distance 搜索周边距离，单位KM
     * @return array
     */
    function _return_square_point($lng, $lat, $distance = 1)
    {
        $earthRadius = 6378.138; //地球半径，单位KM
        $dlng = 2 * asin(sin($distance / (2 * $earthRadius)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance / $earthRadius;
        $dlat = rad2deg($dlat);
        return [
            'left-top' => array('lat' => $lat + $dlat, 'lng' => $lng - $dlng),
            'right-top' => array('lat' => $lat + $dlat, 'lng' => $lng + $dlng),
            'left-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng - $dlng),
            'right-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng + $dlng)
        ];

        //使用此函数计算得到结果后，带入sql查询。
//    $squares = returnSquarePoint($lng, $lat);
//    $info_sql = "select id,locateinfo,lat,lng from `lbs_info` where lat<>0 and lat>{$squares['right-bottom']['lat']} and lat<{$squares['left-top']['lat']} and lng>{$squares['left-top']['lng']} and lng<{$squares['right-bottom']['lng']} ";
    }
}
if (!function_exists('_get_distance')) {
    /**
     * 计算两个坐标的直线距离
     * Enter description here ...
     * @param string $lat1
     * @param string $lng1
     * @param string $lat2
     * @param string $lng2
     * @return float
     */
    function _get_distance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6378.138; //近似地球半径千米
        // 转换为弧度
        $lat1 = ($lat1 * pi()) / 180;
        $lng1 = ($lng1 * pi()) / 180;
        $lat2 = ($lat2 * pi()) / 180;
        $lng2 = ($lng2 * pi()) / 180;
        // 使用半正矢公式  用尺规来计算
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance, 3);
    }
}

if (!function_exists('qdd_order_sign')) {
    /**
     * 生成订单方法
     *
     * @param int $bit
     * @return string
     */
    function qdd_order_sign($bit = CommonConst::QDD_COMMON_ORDER_DEFAULT_BIT)
    {
        $order_id_main = date('ymdHis') . rand(pow(10, $bit - 1), pow(10, $bit) - 1);
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        $sign = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $sign;
    }
}

if (!function_exists('qdd_is_cli')) {
    /**
     * 判断当前脚本运行模式是否是cli
     *
     * @return string
     */
    function qdd_is_cli()
    {
        return preg_match('/cli/i', PHP_SAPI);
    }
}

if (!function_exists('qdd_trans_string')) {
    /**
     * 返回数据转为字符串格式
     *
     * @param array $data
     * @return mixed
     */
    function qdd_trans_string($data)
    {
        array_walk($data, function (&$val) {
            if (is_array($val)) {
                $val = $val ? qdd_trans_string($val) : [];
            } else if (is_object($val) && !(array)$val) {
                //空对象不作处理
            } else {
                $val = "{$val}";
            }
        });
        return $data;
    }
}
if (!function_exists('errorHandler')) {
    /**
     * 自定义错误处理函数
     *
     * @param number $type 错误类型
     * @param string $message 错误信息
     * @param string $file 发生错误的文件
     * @param number $line 发生错误的所在行号
     * @return void
     * @author shijunjun
     * @email jun_5197@163.com
     * @date 2019年12月21日 下午12:09:24
     */
    function errorHandler($type = 0, $message = '', $file = '', $line = 0)
    {
        $level = [
            E_ERROR => 'ERROR', // 1
            E_WARNING => 'WARNING', // 2
            E_PARSE => 'PARSE', // 4
            E_NOTICE => 'NOTICE', // 8
            E_CORE_ERROR => 'CORE_ERROR', // 16
            E_CORE_WARNING => 'CORE_WARNING', // 32
            E_COMPILE_ERROR => 'COMPILE_ERROR', // 64
            E_COMPILE_WARNING => 'COMPILE_WARNING', // 128
            E_USER_ERROR => 'USER_ERROR', // 256
            E_USER_WARNING => 'USER_WARNING', // 512
            E_USER_NOTICE => 'USER_NOTICE', // 1024
            E_STRICT => 'STRICT', // 2048
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR', // 4096
            E_DEPRECATED => 'DEPRECATED', // 8192
            E_USER_DEPRECATED => 'USER_DEPRECATED', // 16384
        ];
        $type_name = $level[$type] ?? 'unknown';
        $msg = "{$type_name} - type:{$type} - file:{$file} - line:{$line} - msg:{$message}";
        SeasLog::alert($msg);
    }
}

if (!function_exists('qdd_random_secret')) {
    /**
     * 生成随机秘钥
     *
     * @param int $len
     * @return string
     */
    function qdd_random_secret($len = 6)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
        $str = "";
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

if (!function_exists('qdd_get_today_second_left')) {
    /**
     * 获取当天剩下的秒数
     *
     * @return string
     */
    function qdd_get_today_second_left()
    {
        return strtotime(date('Y-m-d 23:59:59')) - time();
    }
}

if (!function_exists('qdd_array_column')) {
    /**
     * 优化后的array_column
     *
     * PS: 以某个键作key，其他键值对作为value
     * @param array $data
     * @param string $key
     * @return array
     */
    function qdd_array_column($data, $key)
    {
        $ret = [];
        foreach ($data as $k => $v) {
            if (isset($v[$key]) && $v[$key]) {
                $ret[$v[$key]] = $v;
                unset($ret[$v[$key]][$key]);
            }
        }
        return $ret;
    }
}

if (!function_exists('qdd_trans')) {
    /**
     * 优化后的trans
     *
     * @param string $name
     * @return array
     */
    function qdd_trans($name)
    {
        return (array)trans($name) + (array)trans('error_code');
    }
}

if (!function_exists('qdd_is_json')) {
    /**
     * 判断是否是json格式
     *
     * @param string $name
     * @return boolean
     */
    function qdd_is_json($name)
    {
        if (!is_string($name)) {
            return false;
        }
        json_decode($name);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

if (!function_exists('qdd_camel_case')) {
    /**
     * 参数下划线转换成驼峰形式
     *
     * @param array $data
     * @return mixed
     */
    function qdd_camel_case($data)
    {
        array_walk($data, function ($v, $k) use (&$tmp) {
            $k = camel_case($k);
            if (is_array($v)) {
                $tmp[$k] = $v ? qdd_camel_case($v) : [];
            } else {
                $tmp[$k] = $v;
            }
        });
        return $tmp;
    }
}

if (!function_exists('qdd_underline_case')) {
    /**
     * 驼峰变量转换成下划线形式
     *
     * @param array $data
     * @return mixed
     */
    function qdd_underline_case($data)
    {
        array_walk($data, function ($v, $k) use (&$tmp) {
            $k = qdd_trans_underline($k);
            if (is_array($v)) {
                $tmp[$k] = $v ? qdd_underline_case($v) : [];
            } else {
                $tmp[$k] = $v;
            }
        });
        return $tmp;
    }
}

if (!function_exists('qdd_trans_underline')) {
    /**
     * 驼峰变量转换成下划线
     *
     * @param array $data
     * @param string $separator
     * @return mixed
     */
    function qdd_trans_underline($data, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $data));
    }
}

if (!function_exists('qdd_mb_str_split')) {
    /**
     * 将中文字符串分割成数组
     *
     * @param string $string
     * @return mixed
     */
    function qdd_mb_str_split($string)
    {
        return preg_split('/(?<!^)(?!$)/u', $string);
    }
}
if (!function_exists('starts_with')) {
    function starts_with($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}

if (!function_exists('qdd_load_balance_one')) {
    /**
     * 负载均衡计算方法一
     *
     * PS: 数据格式 ['节点名称' => '概率']
     * @param array $data
     * @return mixed
     */
    function qdd_load_balance_one($data)
    {
        if (!$data || !is_array($data)) {
            return false;
        }
        $sum = [];
        //========== 计算总概率 ==========
        $rateSum = array_sum($data);
        $random = mt_rand(0, $rateSum - 1);
        //========== 初始化概率数组 ==========
        foreach ($data as $k => $v) {
            $rate = intval($v);
            for ($i = 0; $i < $rate; $i++) {
                $sum[] = $k;
            }
        }
        return $sum[$random] ?? '';
    }
}

if (!function_exists('qdd_load_balance_two')) {
    /**
     * 负载均衡算法二
     *
     * PS: 数据格式 ['节点名称' => '概率']
     * @param array $data
     * @return mixed
     */
    function qdd_load_balance_two($data)
    {
        if (!$data || !is_array($data)) {
            return false;
        }
        $result = '';
        //========== 计算总概率 ==========
        $rateSum = array_sum($data);
        //========== 概率数组循环 ==========
        foreach ($data as $k => $v) {
            $randNum = mt_rand(1, $rateSum);
            if ($randNum <= $v) {
                $result = $k;
                break;
            } else {
                $rateSum -= $v;
            }
        }
        return $result;
    }
}
//if(!function_exists('qdd_make_sign')){
//    /**
//     * 负载均衡算法二
//     *
//     * PS: 数据格式 ['节点名称' => '概率']
//     * @param array $data
//     * @return mixed
//     */
//    function qdd_make_sign($data,$key,$str){
//        ksort($data);
//        $query = http_build_query($data);
//        $query .= '&key='.$key;
//        $sign = strtoupper(md5($query));
//        return $sign;
//    }
//}

if (!function_exists('rsa_str')) {
    /***
     * rsa加密解密字符串(默认公钥加密)
     * @param string $str
     * @param string $type [encrypt|decrypt]
     * @param string $method [private|public]
     * @param $keyPath
     * @return string
     */
    function rsa_str($str = '', $type = 'encrypt', $method = 'public', $keyPath = '')
    {

        // 加载key
        $key_method = $method . ucfirst('key');
        App\Supports\Crypt\RSA::$key_method($keyPath);

        // 加密 or 解密
        $ed_type = $method . ucfirst($type);
        $str = App\Supports\Crypt\RSA::$ed_type($str);

        return $str;
    }
}

if (!function_exists('aes_str')) {

    /***
     * Aes加解密
     * @param array $data
     * @param string $type
     * @param string $lockKey
     * @return array|int|string
     */
    function aes_str($data = [], $type = 'encrypt', $lockKey = '')
    {

        if ($type == 'encrypt') {
            $str = App\Supports\Crypt\AES::encrypt($data);
        } else {
            $str = App\Supports\Crypt\AES::decrypt($data, $lockKey);
        }

        return $str;
    }
}

if (!function_exists('api_request')) {
    /**
     * @param string $url
     * @param array $param
     * @param string $type
     * @return array|bool|string
     */
    function api_request($url, $param = [], $type = '')
    {
        try {

            $data_string = json_encode($param);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8'
            ]);

            $response = curl_exec($curl);

            Logger::debug('HTTP请求Url:' . $url, ['req' => $param, 'res' => json_decode($response, true)]);

            if ($err = curl_error($curl)) {
                throw new \Exception("请求错误", 404);
            }
            curl_close($curl);
            return $response;
        } catch (Exception $e) {
            Logger::debug('HTTP请求Url:' . $url, ['status' => $e->getCode(), 'message' => $e->getMessage()]);
            // 返回异常信息如请求超时,客户端连接超时等等
            return json_encode([
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }

    if (!function_exists('string_format')) {
        /**
         * @note 字符串处理
         * @param string $str
         * @param int $start
         * @param int $end
         * @param string $star
         * @return string
         */
        function string_format($str = '', $start = 6, $end = 4, $star = '****')
        {
            return substr($str, 0, $start) . $star . substr($str, '-' . $end);
        }
    }

    if (!function_exists('qdd_hide_mobile')) {
        /**
         * 隐藏手机号中间4位
         * @param $mobile
         * @return string|string[]
         */
        function qdd_hide_mobile($mobile = '')
        {
            return preg_match("/^1[3456789]\d{9}$/", $mobile) ? substr_replace($mobile, '****', 3, 4) : $mobile;
        }
    }


    /**
     * @Title 记录日志
     * @Author lfq
     * @Time 2020/11/26 4:23 下午
     * @param $message
     * @param int $level 0
     * @param string $path ''
     * @param string $dingTitle
     *            0=>SEASLOG_ALL, ALL - 记录所有日志
     *            1=>SEASLOG_DEBUG, DEBUG - debug信息、细粒度信息事件
     *            2=>SEASLOG_INFO,  INFO - 重要事件、强调应用程序的运行过程
     *            3=>SEASLOG_NOTICE, NOTICE - 一般重要性事件、执行过程中较INFO级别更为重要的信息
     *            4=>SEASLOG_WARNING, WARNING - 出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复
     *            5=>SEASLOG_ERROR, ERROR - 运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测
     *            6=>SEASLOG_CRITICAL, CRITICAL - 紧急情况、需要立刻进行修复、程序组件不可用
     *            7=>SEASLOG_ALERT, ALERT - 必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复
     *            8=>SEASLOG_EMERGENCY, EMERGENCY - 系统不可用
     */
    if (!function_exists('writeLog')) {
        function writeLog($message, $level = 0, $path = "")
        {
            $message = preg_replace("/[|]{1,}/i", " ", $message);
            // 初始化Seaslog日志目录
            $config = [
                0 => SEASLOG_ALL,
                1 => SEASLOG_DEBUG,
                2 => SEASLOG_INFO,
                3 => SEASLOG_NOTICE,
                4 => SEASLOG_WARNING,
                5 => SEASLOG_ERROR,
                6 => SEASLOG_CRITICAL,
                7 => SEASLOG_ALERT,
                8 => SEASLOG_EMERGENCY,
            ];
            $levels = isset($config[$level]) ? $config[$level] : $config[5];
            $path = strtolower($path);
            $path === "" && defined('CONTROLLER') && $path = strtolower(CONTROLLER);
            $filePath = __DIR__ . "/../storage/logs/{$path}";
            try {
                \SeasLog::setBasePath($filePath);
                \SeasLog::log($levels, $message);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }
    /***
     * 字段过滤
     * @param array $TableFiltration 数组
     * @param array $Guarded 需过滤数组
     */
    if (!function_exists('TableFiltration')) {
        function TableFiltration($TableFiltration = [], $Guarded = [])
        {
            return array_diff_key($TableFiltration, array_flip($Guarded));
        }
    }
    /****
     * 时间转换时间戳
     */
    if (!function_exists('time_timestamp')) {
        /**
         * 时间格式转化
         * @param $time
         * @param $type
         * @return string
         */
        function time_timestamp($time_timestamp = 0)
        {
            if ($time_timestamp) {
                $timestamp = strtotime($time_timestamp);
            } else {
                $timestamp = time();
            }
            return $timestamp;
        }
    }
    /***
     * 数字转换大写
     */
    if (!function_exists('numToWord')) {
        function numToWord($num)
        {
            $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
            $chiUni = array('', '十', '百', '千', '万', '十', '百', '千', '亿', '十', '百', '千', '万', '十', '百', '千');
            $uniPro = array(4, 8);
            $chiStr = '';


            $num_str = (string)$num;

            $count = strlen($num_str);
            $last_flag = true; //上一个 是否为0
            $zero_flag = true; //是否第一个
            $temp_num = null; //临时数字
            $uni_index = 0;

            $chiStr = '';//拼接结果
            if ($count == 2) {//两位数
                $temp_num = $num_str[0];
                $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num] . $chiUni[1];
                $temp_num = $num_str[1];
                $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
            } else if ($count > 2) {
                $index = 0;
                for ($i = $count - 1; $i >= 0; $i--) {
                    $temp_num = $num_str[$i];
                    if ($temp_num == 0) {
                        $uni_index = $index % 15;
                        if (in_array($uni_index, $uniPro)) {
                            $chiStr = $chiUni[$uni_index] . $chiStr;
                            $last_flag = true;
                        } else if (!$zero_flag && !$last_flag) {
                            $chiStr = $chiNum[$temp_num] . $chiStr;
                            $last_flag = true;
                        }
                    } else {
                        $chiStr = $chiNum[$temp_num] . $chiUni[$index % 16] . $chiStr;

                        $zero_flag = false;
                        $last_flag = false;
                    }
                    $index++;
                }
            } else {
                $chiStr = $chiNum[$num_str[0]];
            }
            return $chiStr;
        }
    }
    /**
     *项目来源
     */
    if (!function_exists('dataSource')) {
        function dataSource($project = '')
        {
            if ($project) {
                switch ($project) {
                    case 1:
                        return '企叮咚';
                    case 2:
                        return '道珉供应链';
                    default:
                        return "暂时无解";
                }
            }
            return "数据不能为空";
        }
    }
    /**
     *运输方式
     */
    if (!function_exists('tariffTransport')) {
        function tariffTransport($transport = '')
        {
            if ($transport) {
                switch ($transport) {
                    case 1:
                        return '物流运输';
                    case 2:
                        return '整车运输';
                    default:
                        return "暂时无解";
                }
            }
            return "数据不能为空";
        }
    }
    /**
     *是否启用
     */
    if (!function_exists('tariffEnabled')) {
        function tariffEnabled($enabled = '')
        {
            if ($enabled) {
                switch ($enabled) {
                    case 1:
                        return '启用';
                    case 2:
                        return '关闭';
                    default:
                        return "暂时无解";
                }
            }
            return "数据不能为空";
        }
    }
    /**
     *类型
     */
    if (!function_exists('tariffType')) {
        function tariffType($enabled = '')
        {
            if ($enabled) {
                switch ($enabled) {
                    case 1:
                        return '件数';
                    case 2:
                        return '重量';
                    case 3:
                        return '体积';
                    default:
                        return "暂时无解";
                }
            }
            return "数据不能为空";
        }
    }
}
