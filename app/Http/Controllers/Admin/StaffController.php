<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\StaffSaveService;
use Illuminate\Http\Request;

class StaffController extends Controller
{

    /**
     * Brief:员工添加修改
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:30
     * docs:
     */
    public function staffInfoSave(Request $request): array
    {
        $this->validateData([
            'staff_user_id' => 'numeric',
            'staff_user_name' => 'required',
            'staff_user_position' => 'required',
            'staff_monthly_salary' => 'required',
            'staff_time_limit' => 'required',
        ], $request->all(), [
            'staff_user_id' => '员工序号',
            'staff_user_name' => '员工名称',
            'staff_user_position' => '员工职位',
            'staff_monthly_salary' => '月薪',
            'staff_time_limit' => '时效',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $all['staff_project_ids_str'] = $all['staff_project_ids_str'] ?? '';
        if ($all['staff_project_ids_str'] == '') {
            unset($all['staff_project_ids_str']);
        }
        if (!array_key_exists('staff_user_id', $all)) {
            $res = (new StaffSaveService())->staffInfoCreate($all, $token);
        } else {
            $res = (new StaffSaveService())->staffInfoUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:员工列表  包含分页和搜索
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:31
     * docs:
     */
    public function getStaffInfo(Request $request): array
    {
        $this->validateData([
            'page' => 'numeric',
            'size' => 'numeric',
        ], $request->all(), [
            'page' => '页数',
            'size' => '每页显示条数',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $staffUserName = $all['staff_user_name'] ?? '';
        $page = $all['page'] ?? CommonConst::DEFAULT_SHOW_PAGE;
        $size = $all['size'] ?? CommonConst::DEFAULT_SHOW_SIZE;
        $info = (new StaffSaveService())->getStaffInfo($staffUserName, $token, $page, $size);
        return $this->responseData($info);
    }


    /**
     * Brief:员工普通删除
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:52
     * docs:
     */
    public function staffInfoDelete(Request $request): array
    {
        $this->validateData([
            'staff_user_id' => 'numeric|required',
        ], $request->all(), [
            'staff_user_name' => '员工序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $staff_user_id = $all['staff_user_id'];
        $res = (new StaffSaveService())->staffInfoDelete($staff_user_id, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:员工批量删除
     * User: XinLau
     * Date: 2022/7/18
     * Time: 11:05
     * docs:
     */
    public function staffInfoBatchDelete(Request $request): array
    {
        $this->validateData([
            'staff_user_ids_str' => 'string|required',
        ], $request->all(), [
            'staff_user_ids_str' => '员工序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $staff_user_ids_str = $all['staff_user_ids_str'];
        $staff_user_ids_str = trim($staff_user_ids_str);
        $res = (new StaffSaveService())->staffInfoBatchDelete($staff_user_ids_str, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:获取员工月时薪列表
     * User: XinLau
     * Date: 2022/7/21
     * Time: 17:34
     * docs:
     */
    public function getStaffHoursLog(Request $request)
    {
        $this->validateData([
            'staff_user_id' => 'numeric|required',
        ], $request->all(), [
            'staff_user_ids_str' => '员工序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new StaffSaveService())->getStaffHoursLog($all, $token);
        return $this->responseData($res);
    }
}
