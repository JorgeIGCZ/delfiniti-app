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
        return $this->hasMany(ProductoImpuesto::class,'producto_id');
    }
}
