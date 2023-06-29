<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userLog extends Model
{
    use HasFactory;
    protected $table = "user_logs";
    protected $fillable = [
        'userId',
        'userEmail',
        'companyCode',
        'action',
        'Time'
    ];
}
