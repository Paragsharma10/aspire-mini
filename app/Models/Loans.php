<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loans extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function loanDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoanDetails::class, 'loan_id', 'id');
    }
}
