<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAllowance extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'allowance_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function allowance()
    {
        return $this->belongsTo(Allowance::class);
    }
}
