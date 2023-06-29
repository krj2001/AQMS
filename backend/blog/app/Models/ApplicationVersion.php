<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationVersion extends Model
{
    use HasFactory;
    protected $table = "application_versions";
     protected $fillable = [
          'versionNumber',
          'summary'
];
}
