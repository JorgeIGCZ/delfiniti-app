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
        'tipo_cambio_usd'
    ];
    protected $primaryKey = 'id';

    public function tipoPago()
    {
        return $this->hasOne(TipoPago::class,'id');
    }
}
