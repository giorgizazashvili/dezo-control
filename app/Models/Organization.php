<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'legal_form',
        'name',
        'identification',
        'address',
        'director',
    ];

    public function monitorings(): HasMany
    {
        return $this->hasMany(Monitoring::class);
    }

    public function monitoringLogs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class);
    }

    public static function legalForms(): array
    {
        return [
            'შპს' => 'შეზღუდული პასუხისმგებლობის საზოგადოება (შპს)',
            'სს' => 'სააქციო საზოგადოება (სს)',
            'კოოპ' => 'კოოპერატივი',
            'იპ' => 'ინდივიდუალური მეწარმე (იმ)',
            'ანს' => 'არამეწარმე (არაკომერციული) იურიდიული პირი',
            'სხვა' => 'სხვა',
        ];
    }
}
