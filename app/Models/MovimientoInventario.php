<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'accion',
        'usuario_id',
        'comentarios'
    ];
    protected $primaryKey = 'id';

    protected $table = 'movimientos_inventario';
}
