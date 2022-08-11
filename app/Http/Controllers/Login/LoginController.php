<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Repositories\Service\Login\LoginSaveService;
use Illuminate\Http\Request;

class LoginController extends Controller
{

    /**
     * Brief:后端登录
     * User: XinLau
     * Date: 2022/7/11
     * Time: 14:34
     * docs:
     */
    public function login(Request $request)
    {
        $this->validateData([
            'account' => 'required',
            'password' => 'required',
        ], $request->all(), [
            'account' => '账号',
            'password' => '密码',
        ]);
        $account = $request->input('account');
        $password = $request->input('password');
        $token = (new LoginSaveService())->login($account, $password);
        return $this->responseData($token);
    }
}
