<?php

namespace App\Repositories\Service\Login;

use App\Consts\RedisConst;
use App\Repositories\Service\BaseService;
use App\Repositories\Db\Admin\UserAdminDb;
use App\Tools\Jwt;
use Illuminate\Support\Facades\Redis;


class LoginSaveService extends BaseService
{
    protected $UserAdminDb;   //public   protected    parent

    public function __construct()
    {
        parent::__construct();
        $this->UserAdminDb = new UserAdminDb(); //对象资源     $this:本类调用
    }


    /**
     * Brief:后端登录
     * User: XinLau
     * Date: 2022/7/11
     * Time: 14:34
     * docs:
     */
    public function login($account, $password)
    {
        $password = new_md5($password);
        $userInfo = $this->UserAdminDb->getUserInfo($account, $password);
        $payload = [
            "iat" => time(),//时间
            "worker_id" => $userInfo['id']  //  用户ID
        ];
        $token = Jwt::getToken($payload);
        Redis::setex('AUTH_TOKEN_' . $userInfo['id'], RedisConst::TOKEN_OUT_TIME, $token);//缓存   他是存在内存里面的   速度要比在硬盘里面快
        return ['token' => $token];
    }
}
