<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDeduction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'employee_id',
        'deduction_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function deduction()
    {
        return $this->belongsTo(Deduction::class);
    }
}
