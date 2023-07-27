<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'nombre',
        'precio_venta',
        'tipo',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';
}
