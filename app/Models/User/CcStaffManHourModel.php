<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcStaffManHourModel extends Model
{
    use HasFactory;

    protected $table = 'cc_staff_man_hour';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'staff_user_id',
        'create_date',
        'man_hour',
        'created_at',
        'updated_at',
        'project_id'
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
