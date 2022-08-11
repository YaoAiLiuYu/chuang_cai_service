<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcAdminProjectModel extends Model
{
    use HasFactory;

    protected $table = 'cc_admin_project';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'project_name',
        'project_type',
        'clinch_price',
        'balance_payment',
        'already_account_price',
        'if_loss',
        'contract_begin_time',
        'contract_end_time',
        'predict_hour',
        'practical_hour',
        'project_state',
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
