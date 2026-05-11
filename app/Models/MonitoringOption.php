<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringOption extends Model
{
    /** @use HasFactory<\Database\Factories\MonitoringOptionFactory> */
    use HasFactory;

    protected $fillable = ['type', 'name'];
}
