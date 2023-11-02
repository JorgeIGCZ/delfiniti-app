<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DescuentoCodigo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'descuento',
        'cupon'
    ];
    protected $primaryKey = 'id';
}
