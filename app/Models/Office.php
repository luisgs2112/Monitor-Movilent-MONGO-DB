<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_code',
        'state',
        'city',
        'address',
        'description',
    ];

    //Nota, esto dice, Una oficina tiene muchos dispositivos
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}