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

class RedisConst extends BaseConst
{
    const TOKEN_OUT_TIME = 7200;                                                                                        //TOKEN到期时间
    const USER_PUBLIC_LOCK = 'USER_PUBLIC_LOCK:';                                                                        //原子锁标识
    const LOCK_EXPIRE = 1;                                                                                           //原子锁时间
}
