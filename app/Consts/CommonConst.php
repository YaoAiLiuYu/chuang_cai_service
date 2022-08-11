<?php
/*
 |--------------------------------------------------------------------------
 | Redis键名常量
 |--------------------------------------------------------------------------
 | 命名规范：
 |
 | 1. 键值对统一大写
 | 2. 字段分级用下划线隔开
 | 3. 统一加个QDD前缀
 | 4. 本文件只存储所有的Redis常量
 | 5. 格式：前缀_分组名_模块名_功能名
 | 6. 分隔符统一用 ":"
*/

namespace App\Consts;

class CommonConst extends BaseConst
{
    const SMS_OUT_TIME = 300;                                                                                           //短信到期时间

    const DEFAULT_SHOW_PAGE = 1;                                                                                        //默认页数
    const DEFAULT_SHOW_SIZE = 15;                                                                                       //默认每页显示条数

    const ADMINISTRATORS = '超级管理员';                                                                                  //超级管理员常量

    const CONCEAL = '*';                                                                                                //除了超级管理员之外不可见的隐藏

    const PROJECT_LOSS_CODE = 0;                                                                                        //项目状态0   亏损
    const PROJECT_LOSS = '亏损';
    const PROJECT_PROFIT_CODE = 1;                                                                                      //项目状态1   盈利
    const PROJECT_PROFIT = '盈利';

    /**
     * 1是未开始2是进行中3是暂停中4是已完成
     */
    const PROJECT_STATIC_CODE_ONE = 1;
    const PROJECT_STATIC_CODE_TWO = 2;
    const PROJECT_STATIC_CODE_THREE = 3;
    const PROJECT_STATIC_CODE_FOUR = 4;

    const PROJECT_STATIC_ONE = '未开始';
    const PROJECT_STATIC_TWO = '进行中';
    const PROJECT_STATIC_THREE = '暂停中';
    const PROJECT_STATIC_FOUR = '已完成';


    const NOT_START = 0;                                                                                                //管理员是否能登录   1是0 否


    const PARENT_ID = 0;                                                                                                //默认父类ID

    const DEFAULT_HOURS = 8;                                                                                                    //默认工时

}
