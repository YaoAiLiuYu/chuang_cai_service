<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcRolePowerModel extends Model
{
    use HasFactory;

    protected $table = 'cc_role_power';
    protected $fillable = [
        'id',
        'user_name',
        'account',
        'password',
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
