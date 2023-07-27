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


    public function pagos()
    {
        return $this->hasManyThrough(
            TiendaVentaPago::class,
            TiendaVentaDetalle::class,
            'producto_id', // FK TiendaVentaDetalle como comunica a TiendaProducto
            'venta_id', // FK pago como comunica a TiendaVentaDetalle
            'id', //local key Actividad
            'venta_id' //TiendaVentaDetalle como comunica a pagp
        );
    }

    public function ventas()
    {
        return $this->hasManyThrough(
            TiendaVenta::class,
            TiendaVentaDetalle::class,
            'producto_id', // FK TiendaVentaDetalle como comunica a Actividad
            'id', // FK Reservacion como comunica a TiendaVentaDetalle
            'id', //local key TiendaVentaDetalle
            'venta_id' //TiendaVentaDetalle como comunica a ventas
        );
    }
}
