<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class CcMonthWorkModel extends Model
{
    use HasFactory;

    protected $table = 'cc_month_work';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'date_data',
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
