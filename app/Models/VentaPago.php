<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'venta_id',
        'cantidad',
        'tipo_pago_id',
        'tipo_cambio_usd',
        'valor',
        'tipo_valor'
    ];
    protected $primaryKey = 'id';
}   