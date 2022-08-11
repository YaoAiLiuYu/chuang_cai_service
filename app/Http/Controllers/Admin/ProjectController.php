<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\ProjectSaveService;
use App\Repositories\Service\Admin\StaffSaveService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    /**
     * Brief:项目创建和修改
     * User: XinLau
     * Date: 2022/7/14
     * Time: 15:49
     * docs:
     */
    public function projectInfoSave(Request $request): array
    {
        $this->validateData([
            'id' => 'numeric',
            'project_name' => 'required',
            'project_type' => 'required',
            'contract_begin_time' => 'required',
            'contract_end_time' => 'required',
            'predict_hour' => 'required',
            'project_state' => 'required',
            'staff_project_ids_str' => 'required',
        ], $request->all(), [
            'id' => '项目序号',
            'project_name' => '项目名称',
            'project_type' => '项目类型',
            'contract_begin_time' => '合同开始时间',
            'contract_end_time' => '合同结束时间',
            'predict_hour' => '预计工时',
            'project_state' => '项目状态',
            'staff_project_ids_str' => '参与人员',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        if (!array_key_exists('id', $all)) {
            $res = (new ProjectSaveService())->projectInfoCreate($all, $token);
        } else {
            $res = (new ProjectSaveService())->projectInfoUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:获取项目列表展示
     * User: XinLau
     * Date: 2022/7/15
     * Time: 8:57
     * docs:
     */
    public function getProjectList(Request $request): array
    {
        $this->validateData([
            'page' => 'numeric',
            'size' => 'numeric',
        ], $request->all(), [
            'page' => '页数',
            'size' => 'required',
        ]);
        $all = $request->all();
        $page = $all['page'] ?? CommonConst::DEFAULT_SHOW_PAGE;
        $size = $all['size'] ?? CommonConst::DEFAULT_SHOW_SIZE;
        $project_name = $all['project_name'] ?? '';
        $project_type = $all['project_type'] ?? '';
        $project_state = $all['project_state'] ?? '';
        $token = $request->header('token');
        $info = (new ProjectSaveService())->getProjectList($page, $size, $project_name, $project_type, $project_state, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:根据一个项目ID，查询详情
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:27
     * docs:
     */
    public function getProjectDetails(Request $request): array
    {
        $this->validateData([
            'id' => 'numeric|required',
        ], $request->all(), [
            'id' => '项目ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $project_id = $all['id'];
        $info = (new ProjectSaveService())->getProjectDetails($project_id, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:根据项目ID进行删除，支持批量删除
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:51
     * docs:
     */
    public function projectInfoDelete(Request $request): array
    {
        $this->validateData([
            'id_str' => 'required',
        ], $request->all(), [
            'id_str' => '项目ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $projectIdsStr = $all['id_str'];
        $info = (new ProjectSaveService())->projectInfoDelete($projectIdsStr, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:根据项目ID获取到项目人员
     * User: XinLau
     * Date: 2022/7/15
     * Time: 11:29
     * docs:
     */
    public function getProjectStaff(Request $request): array
    {
        $this->validateData([
            'id' => 'required',
            'page' => 'numeric',
            'size' => 'numeric',
        ], $request->all(), [
            'id' => '项目ID',
            'page' => '页数',
            'size' => '每页显示条数',
        ]);
        $all = $request->all();
        $page = $all['page'] ?? CommonConst::DEFAULT_SHOW_PAGE;
        $size = $all['size'] ?? CommonConst::DEFAULT_SHOW_SIZE;
        $token = $request->header('token');
        $project_id = $all['id'];
        $info = (new ProjectSaveService())->getProjectStaff($project_id, $token, $page, $size);
        return $this->responseData($info);
    }


    /**
     * Brief:工时添加修改
     * User: XinLau
     * Date: 2022/7/18
     * Time: 13:33
     * docs:
     */
    public function staffManHourSave(Request $request): array
    {
        $this->validateData([
            'staff_user_id' => 'numeric|required',
            'create_date' => 'string|required',
            'man_hour' => 'required',
            'project_id' => 'required|numeric'
        ], $request->all(), [
            'staff_user_id' => '员工ID',
            'create_date' => '填写日期',
            'man_hour' => '工作时长',
            'project_id' => '项目ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new ProjectSaveService())->staffManHourSave($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:展示员工工时
     * User: XinLau
     * Date: 2022/7/18
     * Time: 14:32
     * docs:
     */
    public function getStaffManHour(Request $request)
    {
        $this->validateData([
            'staff_user_id' => 'numeric|required',
            'filtrate_date' => 'string|required',
        ], $request->all(), [
            'staff_user_id' => '员工序号',
            'filtrate_date' => '筛选日期',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new ProjectSaveService())->getStaffManHour($all, $token);
        return $this->responseData($res);
    }


    /**
     * Brief:给项目分配员工
     * User: XinLau
     * Date: 2022/7/20
     * Time: 11:19
     * docs:
     */
    public function projectStaffCreate(Request $request)
    {
        $this->validateData([
            'staff_user_id' => 'numeric|required',
            'project_id' => 'numeric|required',
        ], $request->all(), [
            'staff_user_id' => '员工序号',
            'project_id' => '项目ID',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new ProjectSaveService())->projectStaffCreate($all, $token);
        return $this->responseData($res);
    }
}
