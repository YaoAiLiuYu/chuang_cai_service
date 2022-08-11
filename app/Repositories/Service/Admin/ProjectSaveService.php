<?php

namespace App\Repositories\Service\Admin;

use App\Consts\CommonConst;
use App\Models\User\CcStaffHoursLogModel;
use App\Models\User\CcStaffManHourModel;
use App\Models\User\CcStaffProjectModel;
use App\Repositories\Service\BaseService;
use App\Repositories\Db\Admin\UserAdminDb;
use App\Repositories\Db\Admin\RoleAdminDb;
use App\Repositories\Db\Admin\PowerAdminDb;
use App\Repositories\Db\Admin\ProjectAdminDb;

class ProjectSaveService extends BaseService
{
    protected $UserAdminDb;
    protected $RoleAdminDb;
    protected $PowerAdminDb;
    protected $ProjectAdminDb;

    public function __construct()
    {
        parent::__construct();
        $this->UserAdminDb = new UserAdminDb();
        $this->RoleAdminDb = new RoleAdminDb();
        $this->PowerAdminDb = new PowerAdminDb();
        $this->ProjectAdminDb = new ProjectAdminDb();
    }


    /**
     * Brief:项目创建
     * User: XinLau
     * Date: 2022/7/14
     * Time: 15:50
     * docs:
     */
    public function projectInfoCreate($all, $token)
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        $all['clinch_price'] = $all['clinch_price'] ?? 0;
        $all['balance_payment'] = $all['balance_payment'] ?? 0;
        $all['already_account_price'] = $all['already_account_price'] ?? 0;
        $all['cost_price'] = $all['cost_price'] ?? 0;
        $all['staff_project_ids_str'] = $all['staff_project_ids_str'] ?? '';
        if (!$all['staff_project_ids_str']) {
            unset($all['staff_project_ids_str']);
        }
        return $this->ProjectAdminDb->projectInfoCreate($all, $token);
    }


    /**
     * Brief:项目修改
     * User: XinLau
     * Date: 2022/7/14
     * Time: 15:51
     * docs:
     */
    public function projectInfoUpdate($all, $token)
    {
        $all['updated_at'] = time_format(time());
        $all['clinch_price'] = $all['clinch_price'] ?? 0;
        $all['balance_payment'] = $all['balance_payment'] ?? 0;
        $all['already_account_price'] = $all['already_account_price'] ?? 0;
        $all['cost_price'] = $all['cost_price'] ?? 0;
        $all['staff_project_ids_str'] = $all['staff_project_ids_str'] ?? '';
        if (!$all['staff_project_ids_str']) {
            unset($all['staff_project_ids_str']);
        }
        return $this->ProjectAdminDb->projectInfoUpdate($all, $token);
    }


    /**
     * Brief:返回项目列表
     * User: XinLau
     * Date: 2022/7/15
     * Time: 9:04
     * docs:
     */
    public function getProjectList($page, $size, $project_name, $project_type, $project_state, $token)
    {
        $projectInfo = $this->ProjectAdminDb->getProjectList($page, $size, $project_name, $project_type, $project_state, $token);
        $data = $projectInfo['data'];
        foreach ($data as $key => &$value) {
            $res = $this->getProjectExpenditure($value);
            if ($res['total'] > $value['clinch_price']) {
                $value['if_loss'] = CommonConst::PROJECT_LOSS;
            } else {
                $value['if_loss'] = CommonConst::PROJECT_PROFIT;
            }
        }
        $projectInfo['data'] = $data;
        return $projectInfo;
    }


    /**
     * Brief:根据传递过来的ID，获取到这个项目的详情
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:34
     * docs:
     */
    public function getProjectDetails($project_id, $token)
    {
        return $this->ProjectAdminDb->getProjectDetails($project_id, $token);
    }


    /**
     * Brief:根据传递过来的项目ID进行项目删除（支持批删）
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:52
     * docs:
     */
    public function projectInfoDelete($projectIdsStr, $token)
    {
        return $this->ProjectAdminDb->projectInfoDelete($projectIdsStr, $token);
    }


    /**
     * Brief:根据某和项目ID查询到做这个项目的人员
     * User: XinLau
     * Date: 2022/7/15
     * Time: 11:21
     * docs:
     */
    public function getProjectStaff($project_id, $token, $page, $size)
    {
        return $this->ProjectAdminDb->getProjectStaff($project_id, $token, $page, $size);
    }


    /**
     * Brief:员工工时管理
     * User: XinLau
     * Date: 2022/7/18
     * Time: 13:57
     * docs:
     */
    public function staffManHourSave($all, $token)
    {
        $all['man_hour'] = bcmul($all['man_hour'], 10);
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        return $this->ProjectAdminDb->staffManHourSave($all, $token);
    }


    /**
     * Brief:展示员工工时
     * User: XinLau
     * Date: 2022/7/18
     * Time: 14:32
     * docs:
     */
    public function getStaffManHour($all, $token)
    {
        //获取到这个月一共多少天
        $day = date('t', strtotime($all['filtrate_date']));
        //月份开始时间
        $start = $all['filtrate_date'] . '-01';
        //月份最后一天
        $end = $all['filtrate_date'] . '-' . $day;
        return $this->ProjectAdminDb->getStaffManHour($start, $end, $all, $token);
    }


    /**
     * Brief:给项目分配员工
     * User: XinLau
     * Date: 2022/7/20
     * Time: 11:20
     * docs:
     */
    public function projectStaffCreate($all, $token)
    {
        return $this->ProjectAdminDb->projectStaffCreate($all, $token);
    }


    /**
     * Brief:获取某个用户在某个项目上支出
     * User: XinLau
     * Date: 2022/7/22
     * Time: 14:16
     * docs:
     */
    public function getProjectExpenditure($project_info)
    {
        $info = CcStaffManHourModel::where('project_id', $project_info['id'])->get()->toArray();
        $staff_hours_money = 0;
        if ($info) {
            //某个用户的某个项目的列表
            foreach ($info as $key => $value) {
                $create_date = date("Y-m", strtotime($value['create_date']));
                //员工的工时记录表
                $staff_hours = CcStaffHoursLogModel::where('date', $create_date)->where('staff_user_id', $value['staff_user_id'])->get()->toArray();
                if (!$staff_hours) {
                    //如果没有 钱数为0;
                    $staff_hours_money += 0;
                } else {
                    //如果有 那么就进行循环计算，将钱数给回来
                    foreach ($staff_hours as $k => $v) {
                        $staff_hours_money += bcmul(bcdiv($value['man_hour'], 10), bcdiv($v['staff_hours_salary'], 100, 2), 2);
                    }
                }
            }
        }
        return ['total' => $staff_hours_money];
    }
}
