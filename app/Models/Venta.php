<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'nombre_cliente',
        'email',
        'origen',
        'direccion',
        'RFC',
        'fecha',
        'fecha_creacion',
        'usuario_id',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function ventaDetalle()
    {
        return $this->hasMany(VentaDetalle::class,'venta_id');
    }
} 