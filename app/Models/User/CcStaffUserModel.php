<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcStaffUserModel extends Model
{
    use HasFactory;

    protected $table = 'cc_staff_user';
    protected $fillable = [
        'staff_user_id',
        'staff_user_name',
        'staff_user_position',
        'staff_monthly_salary',
        'staff_time_limit',
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
