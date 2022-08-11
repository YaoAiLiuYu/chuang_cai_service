<?php
namespace App\Exceptions;

use App\Supports\Util\Logger;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommonException extends HttpException
{
    protected $code = 400;  //常规
    protected $message = 'Error';  //常规
    protected $content = [];  //常规

    /**
     * QddException constructor.
     * @param $code     错误编码
     * @param array $args   错误信息
     * @param array $msgArr 错误信息，用于记录到日志
     */
    public function __construct($code = null, $args = [], $msgArr = [])
    {
        if(is_numeric($code)){
            $errMsg = trans('error_code');
            $args = is_array($args) ? $args : [$args];
            $msg = isset($errMsg[$code]) ? vsprintf($errMsg[$code], $args) : '异常错误';
        }else if(is_string($code)){
            $msg = $code;
            $code = 400;
        }else if(is_array($code)){
            $msg = 'Success';
            $this->content = $code;
            $code = 200;
        }else{
            $msg = '异常错误';
            $code = 400;
        }
        if($msgArr){
            Logger::debug('--QddException--', [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'code' => $code,
                'msg' => $msgArr,
            ]);
        }
        $this->code = $code;
        $this->message = $msg;
        parent::__construct(200, $msg);
    }

    public function report()
    {
    }

    public function render()
    {
        return response([
            'code' => $this->code,
//            'status' => $this->code,
//            'msg' => $this->message,
            'message'    => $this->message,
            'content'    => $this->content
        ]);
    }
}
