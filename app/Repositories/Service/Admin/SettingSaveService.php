<?php

namespace App\Repositories\Service\Admin;

use App\Consts\RedisConst;
use App\Exceptions\ApiException;
use App\Models\User\CcMonthWorkModel;
use App\Repositories\Service\BaseService;
use App\Repositories\Db\Admin\UserAdminDb;
use App\Repositories\Db\Admin\RoleAdminDb;
use App\Repositories\Db\Admin\PowerAdminDb;
use App\Repositories\Db\Admin\ProjectAdminDb;
use App\Repositories\Db\Admin\StaffAdminDb;
use App\Tools\Jwt;
use Illuminate\Support\Facades\Cache;

class SettingSaveService extends BaseService
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
     * Brief:添加工作月
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:36
     * docs:
     */
    public function monthWorkCreate($all, $token)
    {
        $all['created_at'] = time_format(time());
        $all['updated_at'] = time_format(time());
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $all['date_data'] = serialize($all['date_data']);
            $id = CcMonthWorkModel::insertGetId($all);
            if ($id <= 0) {
                throw new ApiException(11051);
            }
            return ['id' => $id];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:修改工作月
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:36
     * docs:
     */
    public function monthWorkUpdate($all, $token)
    {
        $all['updated_at'] = time_format(time());
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $all['date_data'] = serialize($all['date_data']);
            $info = CcMonthWorkModel::where('id', $all['id'])->first();
            if (!$info) {
                throw new ApiException(11052);
            }
            $res = CcMonthWorkModel::where('id', $all['id'])->update($all);
            if (!$res) {
                throw new ApiException(11053);
            }
            return ['id' => $all['id']];
        }
        throw new ApiException(666);
    }


    /**
     * Brief:数据展示
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:44
     * docs:
     */
    public function getMonthWorkInfo($id)
    {
        $query = CcMonthWorkModel::when($id != 0, function ($info) use ($id) {
            $info->where('id', "{$id}");
        });
        $info = $query->first();
        if (!$info) {
            throw new ApiException(11054);
        }
        $info = $info->toArray();
        $info['date_data'] = unserialize($info['date_data']);
        return ['data' => $info];
    }


    /**
     * Brief:月工作列表
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:53
     * docs:
     */
    public function monthWorkList($page, $size)
    {
        $query = CcMonthWorkModel::orderBy('updated_at', 'desc');
        $count = $query->count();
        $info = $query->take($size)
            ->offset(($page - 1) * $size)
            ->get()
            ->toArray();
        foreach ($info as $key => &$value) {
            $value['date_data'] = unserialize($value['date_data']);
        }
        return ['count' => $count, 'data' => $info];
    }


    /**
     * Brief:删除
     * User: XinLau
     * Date: 2022/7/19
     * Time: 14:01
     * docs:
     */
    public function monthWorkDelete($all, $token)
    {
        $tokenInfo = Jwt::GetTokenData($token);
        $lock = Cache::lock(RedisConst::USER_PUBLIC_LOCK . 'RECEIPT:' . $tokenInfo['worker_id'], RedisConst::LOCK_EXPIRE);
        if ($lock->get()) {
            $info = CcMonthWorkModel::where('id', $all['id'])->first();
            if (!$info) {
                throw new ApiException(110555);
            }
            $res = CcMonthWorkModel::where('id', $all['id'])->delete();
            if (!$res) {
                throw new ApiException(110556);
            }
            return ['id' => $all['id']];
        }
        throw new ApiException(666);
    }
}

