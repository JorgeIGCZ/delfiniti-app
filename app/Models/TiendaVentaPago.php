<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaVentaPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'factura_id',
        'venta_id',
        'cantidad',
        'tipo_pago_id',
        'tipo_cambio_usd',
        'valor',
        'tipo_valor',
        'comision_creada',
        'created_at'
    ];
    protected $primaryKey = 'id'; 

    public function tipoPago()
    {
        return $this->hasOne(TipoPago::class,'id','tipo_pago_id');
    }
}   