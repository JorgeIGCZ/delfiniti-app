<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoDescuento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'descuento'
    ];
    protected $primaryKey = 'id';
}
