<?php

namespace App\Http\Controllers;

use App\Exceptions\CommonException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;
    protected $input;
    protected $errCode = 400;
    protected $errMsg = '操作异常';

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->input = $request->input();
    }

    /**
     * Brief:返回数据
     * User: XinLau
     * Date: 2022/7/11
     * Time: 13:55
     * docs:
     */
    protected function responseData($data = [])
    {
        $ret = ['code' => 200, 'message' => 'OK'];
        if (is_array($data)) {
            $ret['code'] = $data['code'] ?? 200;
            $ret['message'] = $data['message'] ?? 'OK';
            $ret['content'] = $data['content'] ?? $data;
        } else {
            if ($this->errCode || $this->errMsg) {
                $ret['code'] = $this->errCode ? $this->errCode : '';
                $ret['message'] = $this->errMsg ? $this->errMsg : '';
            }
        }
        return $ret;
    }

    /**
     * Brief:
     * User: XinLau
     * Date: 2022/6/27
     * Time: 17:27
     * docs:
     */
    protected function validateData($rules = [], $data = [], $attr = [], $ruleMsg = [])
    {
        if ($rules || ($data)) {
            $validator = app('validator')->make($data, $rules, $ruleMsg, $attr);
            if ($validator->fails()) {
                throw new CommonException($validator->getMessageBag()->first());
            }
        }
        return true;
    }
}
