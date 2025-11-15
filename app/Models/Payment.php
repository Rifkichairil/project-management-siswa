<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_package_id',
        'amount',
        'status',
        'method',
        'proof'
    ];

    /**
     * Relasi ke StudentPackage
     */
    public function studentPackage()
    {
        return $this->belongsTo(StudentPackage::class);
    }

    /**
     * Helper: Cek apakah pembayaran sudah lunas
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }
}
