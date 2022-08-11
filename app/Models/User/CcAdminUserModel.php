<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcAdminUserModel extends Model
{
    protected $table = 'cc_admin_user';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'user_name',
        'password',
        'created_at',
        'updated_at',
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
