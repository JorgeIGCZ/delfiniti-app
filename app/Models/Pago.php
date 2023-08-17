<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'reservacion_id',
        'cantidad',
        'tipo_pago_id',
        'tipo_cambio_usd',
        'valor',
        'tipo_valor',
        'descuento_codigo_id',
        'comision_creada',
        'usuario_id'
    ];
    protected $primaryKey = 'id';

    public function tipoPago()
    {
        return $this->hasOne(TipoPago::class,'id','tipo_pago_id');
    }

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class,'reservacion_id', 'id');
    }

    public function descuentoCodigo()
    {
        return $this->hasOne(DescuentoCodigo::class,'id','descuento_codigo_id');
    }
}
