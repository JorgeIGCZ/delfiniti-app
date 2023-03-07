<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'codigo',
        'precio',
        'nombre',
        'precio_venta',
        'estatus'
    ];
    protected $primaryKey = 'id';
}
