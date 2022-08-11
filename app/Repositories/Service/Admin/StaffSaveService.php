<?php

namespace App\Repositories\Service\Admin;

use App\Consts\CommonConst;
use App\Repositories\Service\BaseService;
use App\Repositories\Db\Admin\UserAdminDb;
use App\Repositories\Db\Admin\RoleAdminDb;
use App\Repositories\Db\Admin\PowerAdminDb;
use App\Repositories\Db\Admin\ProjectAdminDb;
use App\Repositories\Db\Admin\StaffAdminDb;

class StaffSaveService extends BaseService
{
    protected $UserAdminDb;
    protected $RoleAdminDb;
    protected $PowerAdminDb;
    protected $ProjectAdminDb;
    protected $StaffAdminDb;

    public function __construct()
    {
        parent::__construct();
        $this->UserAdminDb = new UserAdminDb();
        $this->RoleAdminDb = new RoleAdminDb();
        $this->PowerAdminDb = new PowerAdminDb();
        $this->ProjectAdminDb = new ProjectAdminDb();
        $this->StaffAdminDb = new StaffAdminDb();
    }


    /**
     * Brief:员工添加
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:06
     * docs:
     */
    public function staffInfoCreate($all, $token)
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        return $this->StaffAdminDb->staffInfoCreate($all, $token);
    }


    /**
     * Brief:员工修改
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:18
     * docs:
     */
    public function staffInfoUpdate($all, $token)
    {
        $all['updated_at'] = time_format(time());
        $all['staff_project_ids_str'] = $all['staff_project_ids_str'] ?? '';
        if (!$all['staff_project_ids_str']) {
            unset($all['staff_project_ids_str']);
        }
        return $this->StaffAdminDb->staffInfoUpdate($all, $token);
    }


    /**
     * Brief:获取到员工列表 包含分页和搜索
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:39
     * docs:
     */
    public function getStaffInfo($staffUserName, $token, $page, $size)
    {
        return $this->StaffAdminDb->getStaffInfo($staffUserName, $token, $page, $size);
    }


    /**
     * Brief:员工删除（单删）
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:53
     * docs:
     */
    public function staffInfoDelete($staff_user_id, $token)
    {
        return $this->StaffAdminDb->staffInfoDelete($staff_user_id, $token);
    }


    /**
     * Brief:员工删除（批量删除）
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:53
     * docs:
     */
    public function staffInfoBatchDelete($staff_user_ids_str, $token)
    {
        return $this->StaffAdminDb->staffInfoBatchDelete($staff_user_ids_str, $token);
    }



    /**
     * Brief:获取员工月时薪列表
     * User: XinLau
     * Date: 2022/7/21
     * Time: 17:34
     * docs:
     */
    public function getStaffHoursLog($all, $token)
    {
        return $this->StaffAdminDb->getStaffHoursLog($all, $token);
    }
}

