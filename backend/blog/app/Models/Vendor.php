<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $table = "vendors";
    protected $fillable = [
        'vendorName',
        'companyCode',
        'phone',
        'email',
        'address',
        'contactPerson'

    ];
}
