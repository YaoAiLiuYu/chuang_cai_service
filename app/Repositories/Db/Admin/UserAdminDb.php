<?php

namespace App\Repositories\Db\Admin;

use App\Consts\CommonConst;
use App\Exceptions\ApiException;
use App\Models\User\CcAdminPowerModel;
use App\Models\User\CcAdminRoleModel;
use App\Models\User\CcAdminUserModel;
use App\Models\User\CcRolePowerModel;
use App\Models\User\CcUserRoleModel;
use App\Repositories\Db\BaseRepository;
use App\Tools\Jwt;
use App\Tools\Tools;
use Illuminate\Support\Facades\Cache;
use App\Consts\RedisConst;
use Illuminate\Support\Facades\DB;

class UserAdminDb extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Brief:判断账号密码
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:06
     * docs:
     */
    public function getUserInfo($account, $password)
    {
        $userInfo = CcAdminUserModel::where('account', $account)->first();   //first  一条     //查出来的话  是一条  但 不是数组
        if (!$userInfo) {
            throw new ApiException(600);
        }
        $userInfo = $userInfo->toArray();
        if ($password != $userInfo['password']) {
            throw new ApiException(601);
        }
        if ($userInfo['is_start'] == CommonConst::NOT_START) {
            throw new ApiException(602);
        }
        return $userInfo;
    }


    /**
     * Brief:后端用户添加
     * User: XinLau
     * Date: 2022/7/12
     * Time: 10:37
     * docs:
     */
    public function adminUserCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $userInfo = CcAdminUserModel::where('account', $all['account'])->first();
            if ($userInfo) {
                throw new ApiException(900);
            }
            $all['password'] = new_md5($all['password']);
            $id = CcAdminUserModel::insertGetId($all);
            if ($id <= 0) {
                throw new ApiException(901);
            }
            return ['id' => $id];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:管理员信息修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 10:52
     * docs:
     */
    public function adminUserUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $userInfo = CcAdminUserModel::where('account', $all['account'])->where('id', '!=', $all['id'])->first();
            if ($userInfo) {
                throw new ApiException(902);
            }
            $res = CcAdminUserModel::where('id', $all['id'])->update($all);
            if (!$res) {
                throw new ApiException(903);
            }
            return ['id' => $all['id']];
        }
        throw new ApiException(666);
    }

    /**
     * Brief:管理员信息展示
     * User: XinLau
     * Date: 2022/7/12
     * Time: 11:07
     * docs:
     */
    public function adminUserList($user_name, $page, $size)
    {
        $query = CcAdminUserModel::select('id', 'user_name', 'account', 'user_email', 'user_stationed', 'remarks', 'created_at', 'updated_at')->orderBy('updated_at', 'desc')
            ->when($user_name != '', function ($info) use ($user_name) {
                $info->where('user_name', "{$user_name}");
            })
            ->take($size)
            ->offset(($page - 1) * $size);
        $count = $query->count();
        $list = $query->get()->toArray();
        return ['count' => $count, 'data' => $list];
    }


    /**
     * Brief:后端管理员删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:40
     * docs:
     */
    public function adminUserDelete($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $userInfo = CcAdminUserModel::where('id', '=', $all['id'])->first();
            if (!$userInfo) {
                throw new ApiException(904);
            }
            $userRoleInfo = CcUserRoleModel::where('user_id', $all['id'])->get()->toArray();
            if ($userRoleInfo) {
                throw new ApiException(906);
            }
            $res = CcAdminUserModel::where('id', $all['id'])->delete();
            if (!$res) {
                throw new ApiException(905);
            }
            return ['id' => $all['id']];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:给用户分配角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:44
     * docs:
     */
    public function userRoleCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                //处理传递过来的字符串
                if (!array_key_exists('role_ids_str', $all)) {
                    //修改，取消所有
                    $res = CcUserRoleModel::where('user_id', $all['user_id'])->delete();
                    if (!$res) {
                        throw new ApiException(11011);
                    }
                } else {
                    //添加或者是修改
                    $roleIdsArr = explode(',', trim($all['role_ids_str'], ','));
                    $info = CcUserRoleModel::whereIn('role_id', $roleIdsArr)->where('user_id', $all['user_id'])->get()->toArray();
                    if ($info) {
                        $res = CcUserRoleModel::where('user_id', $all['user_id'])->delete();
                        if (!$res) {
                            throw new ApiException(11011);
                        }
                    }
                    foreach ($roleIdsArr as $value) {
                        CcUserRoleModel::insert([
                            'user_id' => $all['user_id'],
                            'role_id' => $value
                        ]);
                    }
                }
                DB::commit();
                return ['id' => $all['user_id']];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11010);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:修改用户的角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 16:00
     * docs:
     */
    public function userRoleUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                $res = CcUserRoleModel::where('user_id', $all['user_id'])->delete();
                if (!$res) {
                    throw new ApiException(11011);
                }
                //处理传递过来的字符串
                $roleIdsArr = explode(',', trim($all['role_ids_str']));
                foreach ($roleIdsArr as $value) {
                    CcUserRoleModel::insert([
                        'user_id' => $all['user_id'],
                        'role_id' => $value
                    ]);
                }
                DB::commit();
                return ['id' => $all['user_id']];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11012);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:根据用户ID获取到他的角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 16:07
     * docs:
     */
    public function getUserRoleInfo($all, $token): array
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $userRoleIdsArray = CcUserRoleModel::select('user_id', 'cc_admin_role.role_id', 'role_name')
                ->join('cc_admin_role', 'cc_user_role.role_id', '=', 'cc_admin_role.role_id')
                ->where('user_id', $all['user_id'])->get()->toArray();
            $userInfo = CcAdminUserModel::where('id', $all['user_id'])->first();
            if (!$userInfo) {
                throw new ApiException(11013);
            }
            $userInfo = $userInfo->toArray();
            $data['user_id'] = $all['user_id'];
            $data['user_name'] = $userInfo['user_name'];
            $data['role_info'] = $userRoleIdsArray;

            return ['data' => $data];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:给某个角色分配权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:36
     * docs:
     */
    public function rolePowerCreate($all, $token): array
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                //处理传递过来的字符串
                $powerIdsArr = explode(',', trim($all['power_ids_str']));
                foreach ($powerIdsArr as $value) {
                    CcRolePowerModel::insert([
                        'power_id' => $value,
                        'role_id' => $all['role_id'],
                    ]);
                }
                DB::commit();
                return ['id' => $all['role_id']];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11014);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:修改某个用户的权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:38
     * docs:
     */
    public function rolePowerUpdate($all, $token): array
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                $res = CcRolePowerModel::where('role_id', $all['role_id'])->delete();
                if (!$res) {
                    throw new ApiException(11015);
                }
                //处理传递过来的字符串
                $powerIdsArr = explode(',', trim($all['power_ids_str']));
                foreach ($powerIdsArr as $value) {
                    CcRolePowerModel::insert([
                        'power_id' => $value,
                        'role_id' => $all['role_id'],
                    ]);
                }
                DB::commit();
                return ['id' => $all['role_id']];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11016);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:根据某个角色ID获取到这个角色的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 10:25
     * docs:
     */
    public function getRolePowerInfo($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $rolePowerArray = CcRolePowerModel::select('role_id', 'cc_admin_power.power_id', 'power_name', 'parent_id', 'url', 'icon')
                ->join('cc_admin_power', 'cc_role_power.power_id', '=', 'cc_admin_power.power_id')
                ->where('role_id', $all['role_id'])->get()->toArray();
            $roleInfo = CcAdminRoleModel::where('role_id', $all['role_id'])->first();
            if (!$roleInfo) {
                throw new ApiException(11017);
            }
            $roleInfo = $roleInfo->toArray();
            $data['role_id'] = $all['role_id'];
            $data['role_name'] = $roleInfo['role_name'];
            $newRolePowerArr = Tools::ArrToTreeList($rolePowerArray, 'power_id', 'parent_id');
            $data['power_info'] = $newRolePowerArr;

            return ['data' => $data];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:根据用户登录的Token获取到用户的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 11:03
     * docs:
     */
    public function userPowerInfo($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $user_id = $tokenInfo['worker_id'];
        //获取到这个用户的角色
        $userRoleInfo = CcUserRoleModel::where('user_id', $user_id)->get()->toArray();
        if ($userRoleInfo) {
            $userRoleInfo = array_column($userRoleInfo, 'role_id');
            $rolePowerInfo = CcRolePowerModel::whereIn('role_id', $userRoleInfo)->get()->toArray();
            $powerIdsInfo = array_column($rolePowerInfo, 'power_id');
            $powerInfo = CcAdminPowerModel::whereIn('power_id', $powerIdsInfo)->get()->toArray();
            $powerInfo = Tools::ArrToTreeList($powerInfo, 'power_id', 'parent_id');
        } else {
            $powerInfo = [];
        }
        return ['data' => $powerInfo];
    }


    /**
     * Brief:账户设置（修改个人信息）
     * User: XinLau
     * Date: 2022/7/19
     * Time: 10:40
     * docs:
     */
    public function userAdminMySave($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            //判断新旧密码是否一致
            $userInfo = CcAdminUserModel::where('id', $tokenInfo['worker_id'])->first();
            if (!$userInfo) {
                throw new ApiException(11047);
            }
            if ($userInfo['password'] !== new_md5($all['old_password'])) {
                throw new ApiException(11048);
            }
            if ($all['new_password'] !== $all['confirm_password']) {
                throw new ApiException(11049);
            }
            $all['password'] = new_md5($all['new_password']);
            unset($all['confirm_password'], $all['new_password'], $all['old_password']);
            $res = CcAdminUserModel::where('id', $tokenInfo['worker_id'])->update($all);
            if (!$res) {
                throw new ApiException(11050);
            }
            return ['id' => $tokenInfo['worker_id']];
        }
        throw new ApiException(666);
    }
}
