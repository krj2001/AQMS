<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class emailTemplate extends Model
{
    use HasFactory;
    protected $table = "email_templates";
    protected $fillable = [
        'calibrartionSubject',
        'calibrartionBody',
        'bumpTestSubject',
        'bumpTestBody',
        'stelSubject',
        'stelBody',
        'twaSubject',
        'twaBody',
        'warningSubject',
        'warningBody',
        'criticalSubject',
        'criticalBody',
        'outOfRangeSubject',
        'outOfRangeBody',
        'periodicitySubject',
        'periodicityBody'       
    ];
}
