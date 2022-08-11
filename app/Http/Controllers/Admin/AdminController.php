<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\AdminSaveService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Brief:后端用户添加修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 10:21
     * docs:
     */
    public function adminUserSave(Request $request): array
    {
        $this->validateData([
            'id' => 'integer',
            'user_name' => 'required',
            'account' => 'required',
            'password' => 'string',
            'user_email' => 'email',
            'user_stationed' => 'string',
        ], $request->all(), [
            'id' => '用户序号',
            'user_name' => '用户名称',
            'account' => '账号',
            'password' => '密码',
            'user_email' => '用户邮箱',
            'user_stationed' => '岗位',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        if (!array_key_exists('id', $all)) {
            $res = (new AdminSaveService())->adminUserCreate($all, $token);
        } else {
            $res = (new AdminSaveService())->adminUserUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:后端用户列表（带分页）
     * User: XinLau
     * Date: 2022/7/12
     * Time: 10:57
     * docs:
     */
    public function adminUserList(Request $request): array
    {
        $this->validateData([
            'page' => 'integer',
            'size' => 'integer',
        ], $request->all(), [
            'page' => '当前页数',
            'size' => '每页显示条数',
        ]);
        $all = $request->all();
        $page = $all['page'] ?? CommonConst::DEFAULT_SHOW_PAGE;
        $size = $all['size'] ?? CommonConst::DEFAULT_SHOW_SIZE;
        $user_name = $all['user_name'] ?? '';
        $info = (new AdminSaveService())->adminUserList($user_name, $page, $size);
        return $this->responseData($info);
    }


    /**
     * Brief:后端用户删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 13:34
     * docs:
     */
    public function adminUserDelete(Request $request): array
    {
        $this->validateData([
            'id' => 'integer|required',
        ], $request->all(), [
            'id' => '数据ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $info = (new AdminSaveService())->adminUserDelete($all, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:给用户分配角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:40
     * docs:
     */
    public function userRoleCreate(Request $request): array
    {
        $this->validateData([
            'user_id' => 'required|numeric',
        ], $request->all(), [
            'user_id' => '用户序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->userRoleCreate($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:修改用户的角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:59
     * docs:
     */
    public function userRoleUpdate(Request $request): array
    {
        $this->validateData([
            'user_id' => 'required|numeric',
            'role_ids_str' => 'required',
        ], $request->all(), [
            'user_id' => '用户序号',
            'role_ids_str' => '角色ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->userRoleUpdate($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:获取某个用户的角色
     * User: XinLau
     * Date: 2022/7/12
     * Time: 16:06
     * docs:
     */
    public function getUserRoleInfo(Request $request): array
    {
        $this->validateData([
            'user_id' => 'required|numeric',
        ], $request->all(), [
            'user_id' => '用户序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->getUserRoleInfo($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:给某个角色赋权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:34
     * docs:
     */
    public function rolePowerCreate(Request $request): array
    {
        $this->validateData([
            'role_id' => 'required|numeric',
            'power_ids_str' => 'required',
        ], $request->all(), [
            'role_id' => '角色序号',
            'power_ids_str' => '权限序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->rolePowerCreate($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:修改某一个角色的权限
     * User: XinLau
     * Date: 2022/7/13
     * Time: 11:41
     * docs:
     */
    public function rolePowerUpdate(Request $request): array
    {
        $this->validateData([
            'role_id' => 'required|numeric',
            'power_ids_str' => 'required',
        ], $request->all(), [
            'role_id' => '角色序号',
            'power_ids_str' => '权限序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->rolePowerUpdate($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:获取某个角色的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 10:23
     * docs:
     */
    public function getRolePowerInfo(Request $request): array
    {
        $this->validateData([
            'role_id' => 'required|numeric',
        ], $request->all(), [
            'role_id' => '角色ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->getRolePowerInfo($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:根据用户登录的Token获取到用户的权限
     * User: XinLau
     * Date: 2022/7/14
     * Time: 11:05
     * docs:
     */
    public function userPowerInfo(Request $request)
    {
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->userPowerInfo($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:账户设置（修改个人信息）
     * User: XinLau
     * Date: 2022/7/19
     * Time: 10:26
     * docs:
     */
    public function userAdminMySave(Request $request)
    {
        $this->validateData([
            'user_name' => 'required',
            'user_portrait' => 'string',
            'old_password' => 'required',
            'user_email' => 'email',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ], $request->all(), [
            'user_name' => '用户名称',
            'user_portrait' => '用户头像',
            'password' => '密码',
            'user_email' => '用户邮箱',
            'new_password' => '新密码',
            'confirm_password' => '确认密码',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->userAdminMySave($all, $token);
        return $this->responseData($res);
    }
}
