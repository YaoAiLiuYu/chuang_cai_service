<?php

namespace App\Http\Controllers\Admin;

use App\Consts\CommonConst;
use App\Http\Controllers\Controller;
use App\Repositories\Service\Admin\SettingSaveService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Brief:月工作添加修改
     * User: XinLau
     * Date: 2022/7/19
     * Time: 14:03
     * docs:
     */
    public function monthWorkSave(Request $request)
    {
        $this->validateData([
            'id' => 'integer',
            'year' => 'integer',
            'date_data' => 'array',
        ], $request->all(), [
            'id' => '月工作序号',
            'year' => '年份',
            'date_data' => '日期数据',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        if (!array_key_exists('id', $all)) {
            $res = (new SettingSaveService())->monthWorkCreate($all, $token);
        } else {
            $res = (new SettingSaveService())->monthWorkUpdate($all, $token);
        }
        return $this->responseData($res);
    }


    /**
     * Brief:数据展示
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:44
     * docs:
     */
    public function getMonthWorkInfo(Request $request)
    {
        $this->validateData([
            'id' => 'integer',
        ], $request->all(), [
            'id' => '月工作序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $id = $all['id'] ?? 0;
        $info = (new SettingSaveService())->getMonthWorkInfo($id, $token);
        return $this->responseData($info);
    }


    /**
     * Brief:月工作日列表
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:51
     * docs:
     */
    public function monthWorkList(Request $request)
    {
        $this->validateData([
            'page' => 'integer',
            'size' => 'integer',
        ], $request->all(), [
            'page' => '页数',
            'size' => '当前条数',
        ]);
        $all = $request->all();
        $page = $all['page'] ?? CommonConst::DEFAULT_SHOW_PAGE;
        $size = $all['size'] ?? CommonConst::DEFAULT_SHOW_SIZE;
        $info = (new SettingSaveService())->monthWorkList($page, $size);
        return $this->responseData($info);
    }

    /**
     * Brief:月工作列表删除
     * User: XinLau
     * Date: 2022/7/19
     * Time: 13:58
     * docs:
     */
    public function monthWorkDelete(Request $request)
    {
        $this->validateData([
            'id' => 'required',
        ], $request->all(), [
            'id' => '数据序号',
        ]);
        $all = $request->all();
        $token = $request->header('token');
        $res = (new SettingSaveService())->monthWorkDelete($all, $token);
        return $this->responseData($res);
    }
}
