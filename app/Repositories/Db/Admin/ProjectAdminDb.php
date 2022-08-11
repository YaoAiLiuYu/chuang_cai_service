<?php

namespace App\Repositories\Db\Admin;

use App\Consts\CommonConst;
use App\Exceptions\ApiException;
use App\Models\User\CcAdminPowerModel;
use App\Models\User\CcAdminProjectModel;
use App\Models\User\CcAdminRoleModel;
use App\Models\User\CcAdminUserModel;
use App\Models\User\CcRolePowerModel;
use App\Models\User\CcStaffManHourModel;
use App\Models\User\CcStaffProjectModel;
use App\Models\User\CcStaffUserModel;
use App\Models\User\CcUserRoleModel;
use App\Repositories\Db\BaseRepository;
use App\Tools\Jwt;
use App\Tools\Tools;
use Illuminate\Support\Facades\Cache;
use App\Consts\RedisConst;
use Illuminate\Support\Facades\DB;

class ProjectAdminDb extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Brief:创建项目
     * User: XinLau
     * Date: 2022/7/14
     * Time: 15:54
     * docs:
     */
    public function projectInfoCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                $projectInfo = CcAdminProjectModel::where('project_name', $all['project_name'])->first();
                if ($projectInfo) {
                    throw new ApiException(11018);
                }
                //处理数据
                //成交金额
                $all['clinch_price'] = bcmul($all['clinch_price'], 100);
                //尾款
                $all['balance_payment'] = bcmul($all['balance_payment'], 100);
                //到账金额
                $all['already_account_price'] = bcmul($all['already_account_price'], 100);
                //成本价格
                $all['cost_price'] = bcmul($all['cost_price'], 100);
                if (array_key_exists('staff_project_ids_str', $all)) {
                    //处理传递过来的字符串
                    $staffProjectIdsArr = explode(',', trim($all['staff_project_ids_str'], ','));
                    unset($all['staff_project_ids_str']);
                    $id = CcAdminProjectModel::insertGetId($all);
                    if ($id <= 0) {
                        throw new ApiException(11019);
                    }
                    //循环
                    foreach ($staffProjectIdsArr as $value) {
                        CcStaffProjectModel::insert([
                            'staff_user_id' => $value,
                            'project_id' => $id,
                        ]);
                    }
                } else {
                    $id = CcAdminProjectModel::insertGetId($all);
                    if ($id <= 0) {
                        throw new ApiException(11019);
                    }
                }
                DB::commit();
                return ['id' => $id];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11020);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:项目修改
     * User: XinLau
     * Date: 2022/7/14
     * Time: 16:25
     * docs:
     */
    public function projectInfoUpdate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                $projectInfo = CcAdminProjectModel::where('id', $all['id'])->first();
                if (!$projectInfo) {
                    throw new ApiException(11022);
                }

                $projectInfo = CcAdminProjectModel::where('project_name', $all['project_name'])->where('id', '!=', $all['id'])->first();
                if ($projectInfo) {
                    throw new ApiException(11021);
                }
                //处理数据
                //成交金额
                $all['clinch_price'] = bcmul($all['clinch_price'], 100);
                //尾款
                $all['balance_payment'] = bcmul($all['balance_payment'], 100);
                //到账金额
                $all['already_account_price'] = bcmul($all['already_account_price'], 100);
                //成本价格
                $all['cost_price'] = bcmul($all['cost_price'], 100);
                if (array_key_exists('staff_project_ids_str', $all)) {
                    //处理传递过来的字符串
                    $staffProjectIdsArr = explode(',', trim($all['staff_project_ids_str'], ','));
                    unset($all['staff_project_ids_str']);
                    //修改
                    $res = CcAdminProjectModel::where('id', $all['id'])->update($all);
                    if (!$res) {
                        throw new ApiException(11023);
                    }
                    $res = CcStaffProjectModel::where('project_id', $all['id'])->delete();
                    if (!$res) {
                        throw new ApiException(11024);
                    }
                    //循环
                    foreach ($staffProjectIdsArr as $value) {
                        CcStaffProjectModel::insert([
                            'staff_user_id' => $value,
                            'project_id' => $all['id'],
                        ]);
                    }
                } else {
                    //修改
                    $res = CcAdminProjectModel::where('id', $all['id'])->update($all);
                    if (!$res) {
                        throw new ApiException(11023);
                    }
                }
                DB::commit();
                return ['id' => $all['id']];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11025);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:项目列表
     * User: XinLau
     * Date: 2022/7/15
     * Time: 9:17
     * docs:
     */
    public function getProjectList($page, $size, $project_name, $project_type, $project_state, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $user_id = $tokenInfo['worker_id'];
        //获取到这个用户的角色
        $userRoleInfo = CcUserRoleModel::where('user_id', $user_id)->get()->toArray();
        //查询项目库
        $projectQuery = CcAdminProjectModel::select('id', 'project_name', 'project_type', 'clinch_price', 'balance_payment',
            'already_account_price', 'cost_price', 'if_loss', 'contract_begin_time', 'contract_end_time', 'predict_hour', 'practical_hour', 'project_state')
            ->when($project_state != 0, function ($info) use ($project_state) {
                $info->where('project_state', "{$project_state}");
            })
            //项目名称搜索
            ->when($project_name != '', function ($info) use ($project_name) {
                $info->where('project_name', 'like', "%{$project_name}%");
            })
            ->when($project_type != '', function ($info) use ($project_type) {
                $info->where('project_type', 'like', "%{$project_type}%");
            });
        //总条数
        $count = $projectQuery->count();
        //每页数据  根据ID进行倒序
        $projectList = $projectQuery->orderBy('id', 'desc')
            ->take($size)
            ->offset(($page - 1) * $size)
            ->get()
            ->toArray();
        $userRoleInfo = array_column($userRoleInfo, 'role_id');
        $roleInfo = CcAdminRoleModel::whereIn('role_id', $userRoleInfo)->get()->toArray();
        $roleNameInfo = array_column($roleInfo, 'role_name');
        //循环，获取到项目人数
        foreach ($projectList as &$value) {
            //展示钱处理 分转换成元
            $value['clinch_price'] = bcdiv($value['clinch_price'], 100);
            $value['balance_payment'] = bcdiv($value['balance_payment'], 100);
            $value['already_account_price'] = bcdiv($value['already_account_price'], 100);
            $value['cost_price'] = bcdiv($value['cost_price'], 100);

            //判断登录用户是不是超级管理员
            if (!in_array(CommonConst::ADMINISTRATORS, $roleNameInfo)) {
                $value['clinch_price'] = CommonConst::CONCEAL;
                $value['balance_payment'] = CommonConst::CONCEAL;
                $value['already_account_price'] = CommonConst::CONCEAL;
                $value['cost_price'] = CommonConst::CONCEAL;
            }
            $value['project_number_people'] = CcStaffProjectModel::where('project_id', $value['id'])->count();
            $value['if_loss'] = $value['if_loss'] == CommonConst::PROJECT_LOSS_CODE ? CommonConst::PROJECT_LOSS : CommonConst::PROJECT_PROFIT;
            if ($value['project_state'] != CommonConst::PROJECT_STATIC_CODE_ONE) {
                if ($value['project_state'] == CommonConst::PROJECT_STATIC_CODE_TWO) {
                    $value['project_state'] = CommonConst::PROJECT_STATIC_TWO;
                } elseif ($value['project_state'] == CommonConst::PROJECT_STATIC_CODE_THREE) {
                    $value['project_state'] = CommonConst::PROJECT_STATIC_THREE;
                } else {
                    $value['project_state'] = CommonConst::PROJECT_STATIC_FOUR;
                }
            } else {
                $value['project_state'] = CommonConst::PROJECT_STATIC_ONE;
            }
        }
        return ['count' => $count, 'data' => $projectList];
    }


    /**
     * Brief:根据传递过来的项目ID，获取到项目详情
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:37
     * docs:
     */
    public function getProjectDetails($project_id, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            //获取到这个用户的角色
            $userRoleInfo = CcUserRoleModel::where('user_id', $tokenInfo['worker_id'])->get()->toArray();
            $userRoleInfo = array_column($userRoleInfo, 'role_id');
            $roleInfo = CcAdminRoleModel::whereIn('role_id', $userRoleInfo)->get()->toArray();
            $roleNameInfo = array_column($roleInfo, 'role_name');
            //查询信息
            $projectInfo = CcAdminProjectModel::where('id', $project_id)->first();
            if (!$projectInfo) {
                throw new ApiException(11026);
            }
            $projectInfo = $projectInfo->toArray();
            //展示钱处理 分转换成元
            $projectInfo['clinch_price'] = bcdiv($projectInfo['clinch_price'], 100);
            $projectInfo['balance_payment'] = bcdiv($projectInfo['balance_payment'], 100);
            $projectInfo['already_account_price'] = bcdiv($projectInfo['already_account_price'], 100);
            $projectInfo['cost_price'] = bcdiv($projectInfo['cost_price'], 100);

            //判断登录用户是不是超级管理员
            if (!in_array(CommonConst::ADMINISTRATORS, $roleNameInfo)) {
                $projectInfo['clinch_price'] = CommonConst::CONCEAL;
                $projectInfo['balance_payment'] = CommonConst::CONCEAL;
                $projectInfo['already_account_price'] = CommonConst::CONCEAL;
                $projectInfo['cost_price'] = CommonConst::CONCEAL;
            }
            $projectInfo['project_number_people'] = CcStaffProjectModel::where('project_id', $projectInfo['id'])->count();
            $projectInfo['if_loss'] = $projectInfo['if_loss'] == CommonConst::PROJECT_LOSS_CODE ? CommonConst::PROJECT_LOSS : CommonConst::PROJECT_PROFIT;
            if ($projectInfo['project_state'] != CommonConst::PROJECT_STATIC_CODE_ONE) {
                if ($projectInfo['project_state'] == CommonConst::PROJECT_STATIC_CODE_TWO) {
                    $projectInfo['project_state'] = CommonConst::PROJECT_STATIC_TWO;
                } elseif ($projectInfo['project_state'] == CommonConst::PROJECT_STATIC_CODE_THREE) {
                    $projectInfo['project_state'] = CommonConst::PROJECT_STATIC_THREE;
                } else {
                    $projectInfo['project_state'] = CommonConst::PROJECT_STATIC_FOUR;
                }
            } else {
                $projectInfo['project_state'] = CommonConst::PROJECT_STATIC_ONE;
            }
            return ['data' => $projectInfo];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:项目删除，支持批量删除
     * User: XinLau
     * Date: 2022/7/15
     * Time: 10:57
     * docs:
     */
    public function projectInfoDelete($projectIdsStr, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            try {
                DB::beginTransaction();
                //处理传递过来的字符串
                $projectIdsArr = explode(',', trim($projectIdsStr));
                $projectInfo = CcAdminProjectModel::whereIn('id', $projectIdsArr)->get()->toArray();
                if (!$projectInfo) {
                    throw new ApiException(11027);
                }
                $req = CcStaffProjectModel::whereIn('project_id', $projectIdsArr)->delete();
                if (!$req) {
                    throw new ApiException(11028);
                }
                $res = CcAdminProjectModel::whereIn('id', $projectIdsArr)->delete();
                if (!$res) {
                    throw new ApiException(11029);
                }
                DB::commit();
                return ['id' => $projectIdsStr];
            } catch (ApiException $e) {
                DB::rollBack();
                throw new ApiException(11030);
            }
        }
        throw new ApiException(666);
    }


    /**
     * Brief:根据传递过来的项目ID，获取到做项目的人员
     * User: XinLau
     * Date: 2022/7/15
     * Time: 11:23
     * docs:
     */
    public function getProjectStaff($project_id, $token, $page, $size): array
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            //先检查数据存不存在
            $projectInfo = CcAdminProjectModel::where('id', $project_id)->first();
            if (!$projectInfo) {
                throw new ApiException(11031);
            }
            $projectInfo = $projectInfo->toArray();
            //联查数据
            $projectStaffInfo = CcStaffProjectModel::where('project_id', $project_id)->get()->toArray();
            if ($projectStaffInfo) {
                $staffIdArr = array_column($projectStaffInfo, 'staff_user_id');
                $staffInfo = CcStaffUserModel::whereIn('staff_user_id', $staffIdArr)->take($size)
                    ->offset(($page - 1) * $size)
                    ->get()
                    ->toArray();
                //循环 处理钱
                foreach ($staffInfo as $key => &$value) {
                    $value['man_hour'] = 0;
                    $value['staff_monthly_salary'] = bcdiv($value['staff_monthly_salary'], 100);
                    //查询一下他的这个项目的工时
                    $manHourInfo = CcStaffManHourModel::where('staff_user_id', $value['staff_user_id'])->where('project_id', $project_id)->get();
                    if (!$manHourInfo) {
                        $value['man_hour'] = 0;
                    } else {
                        $manHourInfo = $manHourInfo->toArray();
                        foreach ($manHourInfo as $k => $v) {
                            $value['man_hour'] += bcdiv($v['man_hour'], 10);
                        }
                    }
                }
            } else {
                $staffInfo = [];
                $list = [];
            }
            $count = count($staffInfo);
            $list['project_name'] = $projectInfo['project_name'];
            $list['staff_info'] = $staffInfo;
            return ['count' => $count, 'data' => $list];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:员工工时管理
     * User: XinLau
     * Date: 2022/7/18
     * Time: 13:58
     * docs:
     */
    public function staffManHourSave($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $res = CcStaffManHourModel::updateOrCreate(['staff_user_id' => $all['staff_user_id'], 'create_date' => $all['create_date']], $all);
            if (!$res) {
                throw new ApiException(11044);
            }
            return ['data' => []];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:展示员工工时
     * User: XinLau
     * Date: 2022/7/18
     * Time: 14:36
     * docs:
     */
    public function getStaffManHour($start, $end, $all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $info = CcStaffManHourModel::select('staff_user_id', 'create_date', 'man_hour')->when($start != '' && $end != '', function ($info) use ($start, $end) {
                $info->whereBetween('create_date', [$start, $end]);
            })->where('staff_user_id', $all['staff_user_id'])->where('project_id', $all['project_id'])->orderBy('create_date')->get()->toArray();
            foreach ($info as $key => &$value) {
                $value['man_hour'] = bcdiv($value['man_hour'], 10);
            }
            return ['data' => $info];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:给项目分配员工
     * User: XinLau
     * Date: 2022/7/20
     * Time: 11:21
     * docs:
     */
    public function projectStaffCreate($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $info = CcStaffProjectModel::where('staff_user_id', $all['staff_user_id'])->where('project_id', $all['project_id'])->first();
            if (!is_null($info)) {
                throw new ApiException(110557);
            }
            $res = CcStaffProjectModel::insert($all);
            if (!$res) {
                throw new ApiException(110558);
            }
            return ['id' => $all['staff_user_id']];
        }
        throw new ApiException(666);
    }

}
