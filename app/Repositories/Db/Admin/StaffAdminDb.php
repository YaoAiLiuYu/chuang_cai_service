<?php

namespace App\Repositories\Db\Admin;

use App\Consts\CommonConst;
use App\Consts\RedisConst;
use App\Exceptions\ApiException;
use App\Models\User\CcAdminProjectModel;
use App\Models\User\CcMonthWorkModel;
use App\Models\User\CcStaffHoursLogModel;
use App\Models\User\CcStaffProjectModel;
use App\Models\User\CcStaffUserModel;
use App\Repositories\Db\BaseRepository;
use App\Tools\Jwt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User\CcStaffManHourModel;

class StaffAdminDb extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Brief:添加员工
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:10
     * docs:
     */
    public function staffInfoCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                //处理数据
                //月薪
                $all['staff_monthly_salary'] = bcmul($all['staff_monthly_salary'], 100);
                if (array_key_exists('staff_project_ids_str', $all)) {
                    //处理传递过来的字符串
                    $staffProjectIdsArr = explode(',', trim($all['staff_project_ids_str']));
                    unset($all['staff_project_ids_str']);
                    $id = CcStaffUserModel::insertGetId($all);
                    if ($staffProjectIdsArr) {
                        //循环
                        foreach ($staffProjectIdsArr as $value) {
                            CcStaffProjectModel::insert([
                                'staff_user_id' => $id,
                                'project_id' => $value,
                            ]);
                        }
                    }
                } else {
                    $id = CcStaffUserModel::insertGetId($all);
                }
                DB::commit();
                return ['id' => $id];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11033);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief: 员工修改
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:19
     * docs:
     */
    public function staffInfoUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                //处理数据
                //月薪
                //查询一下，要修改的数据存不存在
                $info = CcStaffUserModel::where('staff_user_id', $all['staff_user_id'])->first();
                if (!$info) {
                    throw new ApiException(11034);
                }
                $all['staff_monthly_salary'] = bcmul($all['staff_monthly_salary'], 100);
                if (array_key_exists('staff_project_ids_str', $all)) {
                    //处理传递过来的字符串
                    $staffProjectIdsArr = explode(',', trim($all['staff_project_ids_str']));
                    unset($all['staff_project_ids_str']);
                    $res = CcStaffUserModel::where('staff_user_id', $all['staff_user_id'])->update($all);
                    if (!$res) {
                        throw new ApiException(11035);
                    }
                    $staffProjectInfo = CcStaffProjectModel::where('staff_user_id', $all['staff_user_id'])->get()->toArray();
                    if ($staffProjectInfo) {
                        $res = CcStaffProjectModel::where('staff_user_id', $all['staff_user_id'])->delete();
                        if (!$res) {
                            throw new ApiException(11036);
                        }
                    }
                    foreach ($staffProjectIdsArr as $value) {
                        CcStaffProjectModel::insert([
                            'staff_user_id' => $all['staff_user_id'],
                            'project_id' => $value,
                        ]);
                    }
                } else {
                    $res = CcStaffUserModel::where('staff_user_id', $all['staff_user_id'])->update($all);
                    if (!$res) {
                        throw new ApiException(11045);
                    }
                    $staffProjectInfo = CcStaffProjectModel::where('staff_user_id', $all['staff_user_id'])->get()->toArray();
                    if ($staffProjectInfo) {
                        $res = CcStaffProjectModel::where('staff_user_id', $all['staff_user_id'])->delete();
                        if (!$res) {
                            throw new ApiException(11046);
                        }
                    }
                }
                DB::commit();
                return ['id' => $all['staff_user_id']];
            } catch (ApiException $e) {
                dd(2);
                DB::rollBack();
                throw new ApiException(11037);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:获取用户列表 包含用户和搜索
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:39
     * docs:
     */
    public function getStaffInfo($staffUserName, $token, $page, $size)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $query = CcStaffUserModel::select('staff_user_id', 'staff_user_name', 'staff_user_position', 'staff_monthly_salary', 'staff_time_limit', 'created_at', 'updated_at')->when($staffUserName != '', function ($info) use ($staffUserName) {
                $info->where('staff_user_name', 'like', "%{$staffUserName}%");
            });
            $count = $query->count();
            $list = $query->orderBy('updated_at', 'desc')
                ->take($size)
                ->offset(($page - 1) * $size)
                ->get()
                ->toArray();
            foreach ($list as $key => &$value) {
                $value['man_hour'] = 0;
                $value['staff_monthly_salary'] = bcdiv($value['staff_monthly_salary'], 100);
                //查询一下他的这个项目的工时
                $manHourInfo = CcStaffManHourModel::where('staff_user_id', $value['staff_user_id'])->get();
                if (!$manHourInfo) {
                    $value['man_hour'] = 0;
                } else {
                    $manHourInfo = $manHourInfo->toArray();
                    foreach ($manHourInfo as $k => $v) {
                        $value['man_hour'] += bcdiv($v['man_hour'], 10);
                    }
                }
                $staffProjectIdsArr = CcStaffProjectModel::where('staff_user_id', $value['staff_user_id'])->get()->toArray();
                if (!$staffProjectIdsArr) {
                    $value['project_info'] = [];
                } else {
                    $projectIdsArr = array_column($staffProjectIdsArr, 'project_id');
                    $value['project_info'] = CcAdminProjectModel::select('id as project_id', 'project_name')->whereIn('id', $projectIdsArr)->get()->toArray();
                }

                $year = date("Y", time());
                $month = date("n", time());
                $info = CcMonthWorkModel::where("year", $year)->first();
                if (!$info) {
                    $day = date('t', time());
                } else {
                    $info = $info->toArray();
                    $date_data = unserialize($info['date_data']);
                    $day = $date_data[$month - 1];
                }
                $staff_day_salary = bcdiv($value['staff_monthly_salary'], $day, 2);
                $value['staff_hours_salary'] = bcdiv($staff_day_salary, CommonConst::DEFAULT_HOURS, 2);
                //添加到记录表里面
                $logInfo = CcStaffHoursLogModel::where('date', date("Y-m", time()))->where('staff_user_id', $value['staff_user_id'])->first();
                if (!$logInfo) {
                    CcStaffHoursLogModel::insert([
                        'staff_user_id' => $value['staff_user_id'],
                        'staff_hours_salary' => bcmul($value['staff_hours_salary'], 100),
                        'date' => date("Y-m", time())
                    ]);
                }
            }
            return ['count' => $count, 'data' => $list];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:员工删除 单删
     * User: XinLau
     * Date: 2022/7/18
     * Time: 10:55
     * docs:
     */
    public function staffInfoDelete($staff_user_id, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $staffUserInfo = CcStaffUserModel::where('staff_user_id', $staff_user_id)->first();
            if (!$staffUserInfo) {
                throw new ApiException(11038);
            }

            $staffProjectInfo = CcStaffProjectModel::where('staff_user_id', $staff_user_id)->get()->toArray();
            if ($staffProjectInfo) {
                throw new ApiException(11039);
            }

            $res = CcStaffUserModel::where('staff_user_id', $staff_user_id)->delete();
            if (!$res) {
                throw new ApiException(11040);
            }
            return ['id' => $staff_user_id];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:员工删除  批量删除
     * User: XinLau
     * Date: 2022/7/18
     * Time: 11:09
     * docs:
     */
    public function staffInfoBatchDelete($staff_user_ids_str, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $staffUserIdsArr = explode(',', trim($staff_user_ids_str, ','));
            $staffUserInfo = CcStaffUserModel::whereIn('staff_user_id', $staffUserIdsArr)->get()->toArray();
            if (!$staffUserInfo) {
                throw new ApiException(11041);
            }
            $staffProjectInfo = CcStaffProjectModel::whereIn('staff_user_id', $staffUserIdsArr)->get()->toArray();
            if ($staffProjectInfo) {
                throw new ApiException(11042);
            }
            $res = CcStaffUserModel::whereIn('staff_user_id', $staffUserIdsArr)->delete();
            if (!$res) {
                throw new ApiException(11043);
            }
            return ['id' => $staff_user_ids_str];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:获取员工月时薪列表
     * User: XinLau
     * Date: 2022/7/21
     * Time: 17:37
     * docs:
     */
    public function getStaffHoursLog($all, $token)
    {
        $info = CcStaffHoursLogModel::where('staff_user_id', $all['staff_user_id'])->get()->toArray();
        foreach ($info as $key => &$value) {
            $value['staff_hours_salary'] = bcdiv($value['staff_hours_salary'], 100, 2);
        }
        return ['data' => $info];
    }

}
