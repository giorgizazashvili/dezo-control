<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'legal_form',
        'name',
        'identification',
        'address',
        'director',
    ];

    public static function legalForms(): array
    {
        return [
            'შპს'   => 'შეზღუდული პასუხისმგებლობის საზოგადოება (შპს)',
            'სს'    => 'სააქციო საზოგადოება (სს)',
            'კოოპ'  => 'კოოპერატივი',
            'იპ'    => 'ინდივიდუალური მეწარმე (იმ)',
            'ანს'   => 'არამეწარმე (არაკომერციული) იურიდიული პირი',
            'სხვა'  => 'სხვა',
        ];
    }
}
