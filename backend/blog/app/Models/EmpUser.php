<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpUser extends Model
{
    use HasFactory;
    protected $table = "emp_users";
    protected $fillable = [
        'empId',
        'email',
        'mobileno',
        'empname',
        'emprole',
        'password',
        'companycode'       
    ];
}
