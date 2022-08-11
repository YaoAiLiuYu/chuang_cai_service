<?php

namespace App\Tools;

use App\Consts\CommonConst;
use Mrgoon\AliSms\AliSms;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\ApiException;

class Tools
{
    /**
     * @Notes:无限极分类
     * @Interface ArrToTreeList
     * @param $list
     * @param string $id
     * @param string $pid
     * @param string $son
     * @return array
     * @author: Xin.Lau
     * @Time: 2022-3-28   8:31
     */
    public static function ArrToTreeList($list, string $id = 'id', string $pid = 'pid', string $son = 'treeList'): array
    {
        list($tree, $map) = [[], []];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }


    /**
     * @Notes:生成验证码
     * @Interface getCode
     * @param int $len
     * @return int
     * @author: Xin.Lau
     * @Time: 2022-3-28   8:31
     */
    public static function getCode(int $len = 4): int
    {
        return rand(pow(10, ($len - 1)), pow(10, $len) - 1);
    }


    /**
     * Brief:阿里云短信发送
     * User: XinLau
     * Date: 2022/6/21
     * Time: 7:47
     * docs:
     */
    public static function SendSms($phone, $type = 0): array
    {
        $verificationCode = Tools::getCode(4);
        if (strlen($phone) != 11) {
            throw new ApiException(700);
        }
        if (!preg_match("/^1[3456789]\d{9}$/", $phone)) {
            throw new ApiException(701);
        }
        $key = "PhoneCodeInfo:" . $phone;
        $data = array(   //短信模板中字段的值
            "code" => $verificationCode,
            "product" => "dsd"
        );
        //短信模板Code
        $templateCode = config('app.template_code.MrChuStore');
        $aliSms = new AliSms();
        $phoneCodeInfo = Redis::get($key);
        //判断缓存里面存不存在短信
        if (!$phoneCodeInfo) {
            //如果没有，那么就进行短信发送
            $res = $aliSms->sendSms($phone, $templateCode, $data);
            if ($res->Message !== "OK") {
                throw new ApiException(702);
            }
            Redis::SETEX($key, CommonConst::SMS_OUT_TIME, $verificationCode);
        }
        return ['data' => []];
    }

    /**
     * Brief:生成唯一的订单编号
     * User: XinLau
     * Date: 2022/6/27
     * Time: 16:55
     * docs:
     */
    public static function build_order_no()
    {
        return date('ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

}
