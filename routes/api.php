<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//后端登录
Route::any('admin/login', 'App\Http\Controllers\Login\LoginController@login');


//公用方法，文件上传
Route::any('common/FileUpload', 'App\Http\Controllers\Common\CommonController@FileUpload');


/**
 * 后端需要进行Token验证的路由
 */
Route::group(['namespace' => 'App\Http\Controllers\Admin', 'middleware' => ['token']], function () {

    /**
     * 后端用户模块
     */
    //后端用户添加修改
    Route::any('admin/adminUserSave', 'AdminController@adminUserSave');
    //后端用户展示
    Route::any('admin/adminUserList', 'AdminController@adminUserList');
    //后端用户删除
    Route::any('admin/adminUserDelete', 'AdminController@adminUserDelete');
    //管理员修改自己信息
    Route::any('admin/userAdminMySave', 'AdminController@userAdminMySave');

    /**
     * 后端角色模块
     */
    //后端角色添加修改
    Route::any('admin/roleInfoSave', 'RoleController@roleInfoSave');
    //后端角色列表
    Route::any('admin/getRoleList', 'RoleController@getRoleList');
    //后端角色删除
    Route::any('admin/roleInfoDelete', 'RoleController@roleInfoDelete');

    /**
     * 后端权限模块
     */
    //后端权限添加修改
    Route::any('admin/powerInfoSave', 'PowerController@powerInfoSave');
    //后端权限展示列表
    Route::any('admin/getPowerList', 'PowerController@getPowerList');
    //后端权限删除
    Route::any('admin/powerInfoDelete', 'PowerController@powerInfoDelete');
    //获取到权限的父类权限  （parent_id为0的）
    Route::any('admin/getParentPowerInfo', 'PowerController@getParentPowerInfo');
    //以树结构的形式返回权限列表
    Route::any('admin/getPowerTreeList', 'PowerController@getPowerTreeList');


    /**
     * 后端用户与角色模块
     */
    //给用户添加角色
    Route::any('admin/userRoleCreate', 'AdminController@userRoleCreate');
    //给用户修改角色
    Route::any('admin/userRoleUpdate', 'AdminController@userRoleUpdate');
    //根据某个用户ID获取到用户的角色
    Route::any('admin/getUserRoleInfo', 'AdminController@getUserRoleInfo');


    /**
     * 后端角色与权限模块
     */

    //给某个角色赋权限
    Route::any('admin/rolePowerCreate', 'AdminController@rolePowerCreate');
    //修改某一个角色的权限
    Route::any('admin/rolePowerUpdate', 'AdminController@rolePowerUpdate');
    //根据某个用户ID获取到用户的角色
    Route::any('admin/getRolePowerInfo', 'AdminController@getRolePowerInfo');

    //用户登录成功返回的权限
    Route::any('admin/userPowerInfo', 'AdminController@userPowerInfo');

    /**
     * 后端项目模块
     */
    //项目数据创建和修改
    Route::any('admin/projectInfoSave', 'ProjectController@projectInfoSave');
    //项目数据列表
    Route::any('admin/getProjectList', 'ProjectController@getProjectList');
    //根据项目ID获取到项目详情
    Route::any('admin/getProjectDetails', 'ProjectController@getProjectDetails');
    //根据项目ID进行删除，支持批量删除
    Route::any('admin/projectInfoDelete', 'ProjectController@projectInfoDelete');
    //根据项目ID获取到项目参与制作人员
    Route::any('admin/getProjectStaff', 'ProjectController@getProjectStaff');
    //给员工写入工时
    Route::any('admin/staffManHourSave', 'ProjectController@staffManHourSave');
    ///展示员工工时
    Route::any('admin/getStaffManHour', 'ProjectController@getStaffManHour');
    //给项目添加员工
    Route::any('admin/projectStaffCreate', 'ProjectController@projectStaffCreate');


    /**
     * 后端员工模块
     */
    //后端员工添加修改
    Route::any('admin/staffInfoSave', 'StaffController@staffInfoSave');
    //后端员工列表 包含搜索和分页
    Route::any('admin/getStaffInfo', 'StaffController@getStaffInfo');
    //后端员工管理删除（单删）
    Route::any('admin/staffInfoDelete', 'StaffController@staffInfoDelete');
    //后端员工管理删除（批量删除）
    Route::any('admin/staffInfoBatchDelete', 'StaffController@staffInfoBatchDelete');
    //获取员工月时薪列表
    Route::any('admin/getStaffHoursLog', 'StaffController@getStaffHoursLog');


    /**
     * 后端设置模块
     */
    //月工作添加修改
    Route::any('admin/monthWorkSave', 'SettingController@monthWorkSave');
    //月工作单条数据
    Route::any('admin/getMonthWorkInfo', 'SettingController@getMonthWorkInfo');
    //月工作列表
    Route::any('admin/monthWorkList', 'SettingController@monthWorkList');
    //删除
    Route::any('admin/monthWorkDelete', 'SettingController@monthWorkDelete');
});

