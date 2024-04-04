<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryStat extends Model
{
    protected $fillable = [
        'stream_name',
        'country_code',
        'country_name',
        'total_visits',
        'created_at',
        'updated_at',
    ];
}
