<?php

namespace App\Repositories\Db\Admin;

use App\Consts\RedisConst;
use App\Exceptions\ApiException;
use App\Models\User\CcAdminPowerModel;
use App\Models\User\CcAdminUserModel;
use App\Models\User\CcRolePowerModel;
use App\Repositories\Db\BaseRepository;
use App\Tools\Jwt;
use App\Tools\Tools;
use Illuminate\Support\Facades\Cache;

class PowerAdminDb extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Brief:权限添加
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:42
     * docs:
     */
    public function powerInfoCreate($all, $token): array
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $powerInfo = CcAdminPowerModel::where('power_name', $all['power_name'])->first();
            if ($powerInfo) {
                throw new ApiException(11003);
            }
            $id = CcAdminPowerModel::insertGetId($all);
            if ($id <= 0) {
                throw new ApiException(11004);
            }
            return ['id' => $id];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:权限修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:01
     * docs:
     */
    public function powerInfoUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $powerInfo = CcAdminPowerModel::where('power_name', $all['power_name'])->where('power_id', '!=', $all['power_id'])->first();
            if ($powerInfo) {
                throw new ApiException(11005);
            }
            $res = CcAdminPowerModel::where('power_id', $all['power_id'])->update($all);
            if (!$res) {
                throw new ApiException(11006);
            }
            return ['id' => $all['power_id']];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:权限展示列表
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:19
     * docs:
     */
    public function getPowerList($power_name, $page, $size)
    {
        $list = CcAdminPowerModel::select('power_id', 'power_name', 'parent_id', 'url', 'icon', 'created_at', 'updated_at')->orderBy('updated_at', 'desc')
            ->when($power_name != '', function ($info) use ($power_name) {
                $info->where('power_name', 'like', "%{$power_name}%");
            })->get()->toArray();
//        $newList = Tools::ArrToTreeList($list, 'power_id', 'parent_id');
        $count = count($list);
        return ['count' => $count, 'data' => $list];
    }


    /**
     * Brief:权限删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:30
     * docs:
     */
    public function powerInfoDelete($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $powerInfo = CcAdminPowerModel::where('power_id', '=', $all['power_id'])->first();
            if (!$powerInfo) {
                throw new ApiException(11007);
            }
            $rolePowerInfo = CcRolePowerModel::where('power_id', $all['power_id'])->get()->toArray();
            if ($rolePowerInfo) {
                throw new ApiException(11008);
            }
            $res = CcAdminPowerModel::where('power_id', $all['power_id'])->delete();
            if (!$res) {
                throw new ApiException(11009);
            }
            return ['id' => $all['power_id']];
        }
        throw new ApiException(666);
    }
}

