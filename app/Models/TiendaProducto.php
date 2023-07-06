<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'codigo',
        'proveedor_id',
        'nombre',
        'costo',
        'precio_venta',
        'margen_ganancia',
        'stock_minimo',
        'stock_maximo',
        'ultima_entrada',
        'ultima_salida',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function productoImpuesto()
    {
        return $this->hasMany(TiendaProductoImpuesto::class,'producto_id');
    }
    public function proveedor()
    {
        return $this->hasOne(TiendaProveedor::class,'id','proveedor_id');
    }
}
