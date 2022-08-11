<?php

namespace App\Repositories\Db\Admin;

use App\Exceptions\ApiException;
use App\Models\User\CcAdminRoleModel;
use App\Models\User\CcAdminUserModel;
use App\Models\User\CcUserRoleModel;
use App\Repositories\Db\BaseRepository;
use App\Tools\Jwt;
use Illuminate\Support\Facades\Cache;
use App\Consts\RedisConst;

class RoleAdminDb extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Brief:角色添加
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:46
     * docs:
     */
    public function roleInfoCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $userInfo = CcAdminRoleModel::where('role_name', $all['role_name'])->first();
            if ($userInfo) {
                throw new ApiException(907);
            }
            $id = CcAdminRoleModel::insertGetId($all);
            if ($id <= 0) {
                throw new ApiException(908);
            }
            return ['id' => $id];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:角色修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:49
     * docs:
     */
    public function roleInfoUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $roleInfo = CcAdminRoleModel::where('role_name', $all['role_name'])->where('role_id', '!=', $all['role_id'])->first();
            if ($roleInfo) {
                throw new ApiException(909);
            }
            $res = CcAdminRoleModel::where('role_id', $all['role_id'])->update($all);
            if (!$res) {
                throw new ApiException(910);
            }
            return ['id' => $all['role_id']];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:角色列表
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:09
     * docs:
     */
    public function getRoleList($role_name, $page, $size)
    {
        $query = CcAdminRoleModel::select('role_id', 'role_name', 'created_at', 'updated_at')->orderBy('updated_at', 'desc')
            ->when($role_name != '', function ($info) use ($role_name) {
                $info->where('role_name', "{$role_name}");
            })
            ->take($size)
            ->offset(($page - 1) * $size);
        $count = $query->count();
        $list = $query->get()->toArray();
        return ['count' => $count, 'data' => $list];
    }


    /**
     * Brief:角色删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:19
     * docs:
     */
    public function roleInfoDelete($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $roleInfo = CcAdminRoleModel::where('role_id', '=', $all['role_id'])->first();
            if (!$roleInfo) {
                throw new ApiException(11000);
            }
            $userRoleInfo = CcUserRoleModel::where('role_id', $all['role_id'])->get()->toArray();
            if ($userRoleInfo) {
                throw new ApiException(11001);
            }
            $res = CcAdminRoleModel::where('role_id', $all['role_id'])->delete();
            if (!$res) {
                throw new ApiException(11002);
            }
            return ['id' => $all['role_id']];
        }
        throw new ApiException(666);
    }
}

