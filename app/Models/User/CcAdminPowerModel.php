<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcAdminPowerModel extends Model
{
    use HasFactory;
    protected $table = 'cc_admin_power';
    protected $primaryKey = 'power_id';
    protected $fillable = [
        'power_id',
        'power_name',
        'parent_id',
        'url',
        'icon',
        'created_at',
        'updated_at',
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
