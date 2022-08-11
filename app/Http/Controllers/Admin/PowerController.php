<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\AdminSaveService;
use Illuminate\Http\Request;

class PowerController extends Controller
{
    /**
     * Brief:权限添加修改
     * User: XinLau
     * Date: 2022/7/12
     * Time: 14:59
     * docs:
     */
    public function powerInfoSave(Request $request): array
    {
        $this->validateData([
            'power_id' => 'integer',
            'power_name' => 'required',
            'parent_id' => 'integer',
            'icon' => 'required',
        ], $request->all(), [
            'power_id' => '权限序号',
            'power_name' => '权限名称',
            'parent_id' => '父类ID',
            'icon' => '小图标',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        if (!array_key_exists('power_id', $all)) {
            $res = (new AdminSaveService())->powerInfoCreate($all, $token);
        } else {
            $res = (new AdminSaveService())->powerInfoUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:后端权限列表展示
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:20
     * docs:
     */
    public function getPowerList(Request $request): array
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
        $power_name = $all['$power_name'] ?? '';
        $info = (new AdminSaveService())->getPowerList($power_name, $page, $size);
        return $this->responseData($info);
    }


    /**
     * Brief:权限删除
     * User: XinLau
     * Date: 2022/7/12
     * Time: 15:35
     * docs:
     */
    public function powerInfoDelete(Request $request): array
    {
        $this->validateData([
            'power_id' => 'required|integer',
        ], $request->all(), [
            'power_id' => '权限序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new AdminSaveService())->powerInfoDelete($all, $token);
        return $this->responseData($res);
    }

    /**
     * Brief:返回父类权限
     * User: XinLau
     * Date: 2022/7/21
     * Time: 15:49
     * docs:
     */
    public function getParentPowerInfo(Request $request)
    {
        $all = $request->all();
        $token = $request->header('token');
        $info = (new AdminSaveService())->getParentPowerInfo($all, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:以树结构的形式返回权限列表
     * User: XinLau
     * Date: 2022/7/22
     * Time: 8:31
     * docs:
     */
    public function getPowerTreeList(Request $request)
    {
        $info = (new AdminSaveService())->getPowerTreeList();
        return $this->responseData($info);
    }
}
