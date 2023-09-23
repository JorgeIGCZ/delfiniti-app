<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoVenta extends Model
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
        'comisionista_id',
        'estatus_pago',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function ventaDetalle()
    {
        return $this->hasMany(FotoVideoVentaDetalle::class,'venta_id');
    }

    public function productos()
    {
        return $this->belongsToMany(FotoVideoProducto::class, 'foto_video_venta_detalles', 'venta_id', 'producto_id')
                    ->withPivot('id', 'factura_id', 'numero_productos', 'PPU');
    }

    public function fotografo()
    {
        return $this->hasOne(FotoVideoComisionista::class,'id','comisionista_id');
    }

    public function pagos()
    {
        return $this->hasMany(FotoVideoVentaPago::class,'venta_id');
    }

    public function tipoPago()
    {
        return $this->belongsToMany(TipoPago::class, 'foto_video_venta_pagos', 'venta_id', 'tipo_pago_id')
                    ->withPivot('factura_id','venta_id','cantidad','tipo_pago_id','tipo_cambio_usd','valor','tipo_valor','comision_creada');
    }

    public function usuario()
    {
        return $this->hasOne(User::class,'id','usuario_id');
    }
}
