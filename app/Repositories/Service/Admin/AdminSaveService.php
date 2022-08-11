<?php

namespace App\Repositories\Service\Admin;

use App\Consts\CommonConst;
use App\Models\User\CcAdminPowerModel;
use App\Repositories\Service\BaseService;
use App\Repositories\Db\Admin\UserAdminDb;
use App\Repositories\Db\Admin\RoleAdminDb;
use App\Repositories\Db\Admin\PowerAdminDb;
use App\Tools\Tools;

class AdminSaveService extends BaseService
{
    protected $UserAdminDb;
    protected $RoleAdminDb;
    protected $PowerAdminDb;

    public function __construct()
    {
        parent::__construct();
        $this->UserAdminDb = new UserAdminDb();
        $this->RoleAdminDb = new RoleAdminDb();
        $this->PowerAdminDb = new PowerAdminDb();
    }


    /**
     * Brief:后端管理员信息添加修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 10:28
     * docs:
     */
    public function adminUserCreate($all, $token): array
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        return $this->UserAdminDb->adminUserCreate($all, $token);
    }


    /**
     * Brief:后端用户修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 11:00
     * docs:
     */
    public function adminUserUpdate($all, $token): array
    {
        $all['updated_at'] = time_format(time());
        return $this->UserAdminDb->adminUserUpdate($all, $token);
    }


    /**
     * Brief:后端用户列表
     * User: XinLau
     * Date: 2022/7/12
     * Time: 11:02
     * docs:
     */
    public function adminUserList($user_name, $page, $size): array
    {
        return $this->UserAdminDb->adminUserList($user_name, $page, $size);
    }


    /**
     * Brief:后端用户删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:37
     * docs:
     */
    public function adminUserDelete($all, $token): array
    {
        return $this->UserAdminDb->adminUserDelete($all, $token);
    }


    /**
     * Brief:角色添加
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:45
     * docs:
     */
    public function roleInfoCreate($all, $token): array
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        return $this->RoleAdminDb->roleInfoCreate($all, $token);
    }


    /**
     * Brief:角色修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:49
     * docs:
     */
    public function roleInfoUpdate($all, $token): array
    {
        $all['updated_at'] = time_format(time());
        return $this->RoleAdminDb->roleInfoUpdate($all, $token);
    }


    /**
     * Brief:角色列表
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:07
     * docs:
     */
    public function getRoleList($role_name, $page, $size): array
    {
        return $this->RoleAdminDb->getRoleList($role_name, $page, $size);
    }


    /**
     * Brief:角色删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:18
     * docs:
     */
    public function roleInfoDelete($all, $token): array
    {
        return $this->RoleAdminDb->roleInfoDelete($all, $token);
    }


    /**
     * Brief:权限添加
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:40
     * docs:
     */
    public function powerInfoCreate($all, $token): array
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        return $this->PowerAdminDb->powerInfoCreate($all, $token);
    }


    /**
     * Brief:权限修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:59
     * docs:
     */
    public function powerInfoUpdate($all, $token): array
    {
        $all['updated_at'] = time_format(time());
        return $this->PowerAdminDb->powerInfoUpdate($all, $token);
    }


    /**
     * Brief:权限展示列表
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:27
     * docs:
     */
    public function getPowerList($power_name, $page, $size): array
    {
        return $this->PowerAdminDb->getPowerList($power_name, $page, $size);
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
        return $this->PowerAdminDb->powerInfoDelete($all, $token);
    }


    /**
     * Brief:给用户添加角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:58
     * docs:
     */
    public function userRoleCreate($all, $token)
    {
        if ($all['role_ids_str'] == '') {
            //修改的时候 取消了所有
            unset($all['role_ids_str']);
        }
        return $this->UserAdminDb->userRoleCreate($all, $token);
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
        return $this->UserAdminDb->userRoleUpdate($all, $token);
    }


    /**
     * Brief:查看某个用户下的角色
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:13
     * docs:
     */
    public function getUserRoleInfo($all, $token)
    {
        return $this->UserAdminDb->getUserRoleInfo($all, $token);
    }


    /**
     * Brief:给某个角色分配权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:36
     * docs:
     */
    public function rolePowerCreate($all, $token)
    {
        return $this->UserAdminDb->rolePowerCreate($all, $token);
    }


    /**
     * Brief:修改某个角色的权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:38
     * docs:
     */
    public function rolePowerUpdate($all, $token)
    {
        return $this->UserAdminDb->rolePowerUpdate($all, $token);
    }


    /**
     * Brief:获取某个角色的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 10:23
     * docs:
     */
    public function getRolePowerInfo($all, $token)
    {
        return $this->UserAdminDb->getRolePowerInfo($all, $token);
    }


    /**
     * Brief:根据用户登录的Token获取到用户的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 11:05
     * docs:
     */
    public function userPowerInfo($all, $token)
    {
        return $this->UserAdminDb->userPowerInfo($all, $token);
    }


    /**
     * Brief:账户信息修改
     * User: XinLau
     * Date: 2022/7/19
     * Time: 10:39
     * docs:
     */
    public function userAdminMySave($all, $token)
    {
        $all['updated_at'] = time_format(time());
        return $this->UserAdminDb->userAdminMySave($all, $token);
    }


    /**
     * Brief:返回父类权限
     * User: XinLau
     * Date: 2022/7/21
     * Time: 15:49
     * docs:
     */
    public function getParentPowerInfo($all, $token)
    {
        return CcAdminPowerModel::where('parent_id', CommonConst::PARENT_ID)->get()->toArray();
    }


    /**
     * Brief:以树结构的形式返回权限列表
     * User: XinLau
     * Date: 2022/7/22
     * Time: 8:33
     * docs:
     */
    public function getPowerTreeList()
    {
        $list = CcAdminPowerModel::select('power_id', 'power_name', 'parent_id', 'url', 'icon', 'created_at', 'updated_at')->orderBy('updated_at', 'desc')
            ->get()->toArray();
        $newList = Tools::ArrToTreeList($list, 'power_id', 'parent_id');
        $count = count($list);
        return ['count' => $count, 'data' => $newList];
    }
}
