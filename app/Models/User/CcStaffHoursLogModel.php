<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcStaffHoursLogModel extends Model
{
    use HasFactory;

    protected $table = 'cc_staff_hours_log';
    protected $fillable = [
        'staff_user_id',
        'staff_hours_salary',
        'date',
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
