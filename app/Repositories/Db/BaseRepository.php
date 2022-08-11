<?php
/**
 * @author  kai.chen
 * Date: 2019/12/24
 * Time: 10:45
 * Source: BaseRepository.php
 * Project: 7ddv2
 */

namespace App\Repositories\Db;

use App\Repositories\Repository;

class BaseRepository extends Repository
{
    public $page_size = 15;
    public $where = '';
    public $bindings = [];
    public static $user = [];
    public function __construct()
    {
        parent::__construct();
    }

}
