<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoVentaPago extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'factura_id',
        'venta_id',
        'cantidad',
        'tipo_pago_id',
        'tipo_cambio_usd',
        'valor',
        'tipo_valor',
        'comision_creada'
    ];
    protected $primaryKey = 'id'; 

    public function tipoPago()
    {
        return $this->hasOne(TipoPago::class,'id','tipo_pago_id');
    }
}
 