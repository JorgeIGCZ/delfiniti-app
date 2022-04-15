<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenciacredito extends Model
{
    use HasFactory;
    protected $fillable = [
        'codigo',
        'nombre',
        'comision',
        'iva',
        'representante',
        'direccion',
        'telefono'
    ];
    protected $primaryKey = 'id';
} 
