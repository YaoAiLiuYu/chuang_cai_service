<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcUserRoleModel extends Model
{
    protected $table = 'cc_user_role';
    protected $fillable = [
        'role_id',
        'user_id',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
