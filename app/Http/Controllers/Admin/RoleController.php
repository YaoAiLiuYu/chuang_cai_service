<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\AdminSaveService;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    /**
     * Brief:角色添加修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:04
     * docs:
     */
    public function roleInfoSave(Request $request): array
    {
        $this->validateData([
            'role_id' => 'integer',
            'role_name' => 'required',
        ], $request->all(), [
            'role_id' => '角色序号',
            'user_name' => '角色名称',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        if (!array_key_exists('role_id', $all)) {
            $res = (new AdminSaveService())->roleInfoCreate($all, $token);
        } else {
            $res = (new AdminSaveService())->roleInfoUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:角色返回列表（分页）
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:05
     * docs:
     */
    public function getRoleList(Request $request): array
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
        $role_name = $all['role_name'] ?? '';
        $info = (new AdminSaveService())->getRoleList($role_name, $page, $size);
        return $this->responseData($info);
    }


    /**
     * Brief:角色删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:16
     * docs:
     */
    public function roleInfoDelete(Request $request): array
    {
        $this->validateData([
            'role_id' => 'integer|required',
        ], $request->all(), [
            'role_id' => '数据ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $info = (new AdminSaveService())->roleInfoDelete($all, $token);
        return $this->responseData($info);
    }
}
