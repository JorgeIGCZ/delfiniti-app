<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'folio',
        'nombre_cliente',
        'email',
        'origen',
        'direccion',
        'RFC',
        'fecha',
        'fecha_creacion',
        'usuario_id',
        'estatus_pago',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function ventaDetalle()
    {
        return $this->hasMany(TiendaVentaDetalle::class,'venta_id');
    }

    public function productos()
    {
        return $this->belongsToMany(TiendaProducto::class, 'tienda_venta_detalles', 'venta_id', 'producto_id')
                    ->withPivot('id', 'factura_id', 'numero_productos', 'PPU');
    }

    public function pagos()
    {
        return $this->hasMany(TiendaVentaPago::class,'venta_id');
    }

    public function tipoPago()
    {
        return $this->belongsToMany(TipoPago::class, 'tienda_venta_pagos', 'venta_id', 'tipo_pago_id')
                    ->withPivot('factura_id','venta_id','cantidad','tipo_pago_id','tipo_cambio_usd','valor','tipo_valor','comision_creada');
    }

    public function usuario()
    {
        return $this->hasOne(User::class,'id','usuario_id');
    }
} 