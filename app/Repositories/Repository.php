<?php
/**
 * @author  kai.chen
 * Date: 2019/12/24
 * Time: 10:45
 * Source: BaseRepository.php
 * Project: 7ddv2
 */

namespace App\Repositories;

use App\Supports\Util\Logger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Repository
{
    public function __construct()
    {
        //TODO:
    }

    /**
     * @param $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    protected function make($abstract, $parameters = [])
    {
        return app()->make($abstract, $parameters);
    }

    /**
     * @param $instance
     * @param string $name
     * @param bool $useTrans
     * @param bool $useTransNoThr
     * @param array $arguments
     *
     * @return null
     * @throws \Throwable
     */
    public function invokeThis($instance, string $name, bool $useTrans, bool $useTransNoThr, array $arguments)
    {
        try {
            $result = null;
            if ($useTrans || $useTransNoThr) {
                DB::beginTransaction();
            }

            $result = call_user_func_array([$instance, $name], $arguments);

            if ($useTrans || $useTransNoThr) {
                DB::commit();
            }
            return $result;
        } catch (\Throwable $e) {
            Logger::error("--执行失败:{$name}--", [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
                'arg' => $arguments,
            ]);
            if ($useTrans) {
                DB::rollBack();
                throw $e;
            }
            if ($useTransNoThr) {
                DB::rollBack();
                return $result ?? false;
            }
        } catch (QueryException $q) {
            Logger::debug('--Sql Error--', [
                'msg' => $q->getFile() . '|' . $q->getLine() . '|' . $q->getCode() . '|' . $q->getMessage(),
            ]);
        }
    }

    /**
     * @param $name
     * @param array $arguments
     *
     * @return null
     * @throws \Throwable
     */
    public function __call($name, $arguments = [])
    {
        $useTrans = Str::startsWith($name, 'trans_');
        $useTransNoThr = Str::startsWith($name, 'transNoThr_');

        $methodName = $useTrans ? substr($name, 6) : substr($name, 11);
        return $this->invokeThis($this, $methodName, $useTrans, $useTransNoThr, $arguments);
    }

    /**
     * string $TableId ID  抛弃后所剩的值
     * array $Filtration 值
     */
    protected function discard($TableId = [], $Filtration = [])
    {
        if (empty($Filtration)) {
            if ($TableId) {
                $Guarded = [env('APP_GUARDED_VERSION'), env('APP_GUARDED_URL')];
                $Guarded = array_merge($Guarded, $TableId);
            } else {
                $Guarded = [env('APP_GUARDED_VERSION'), env('APP_GUARDED_URL')];
            }
        } else {
            if ($TableId) {
                $Guarded = array_merge($Filtration, $TableId);
            } else {
                $Guarded = $Filtration;
            }
        }
        return $Guarded;
    }

    /**
     * array  $Filtration 数组
     * string   $value 数组键名
     */
    protected function acquire($Filtration = [], $value = '')
    {
        return $Filtration[$value];
    }
}
