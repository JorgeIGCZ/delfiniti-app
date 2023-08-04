<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorComisionFotoVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'venta_id',
        'pago_total',
        'pago_total_sin_iva',
        'cantidad_comision_bruta',
        'iva',
        'descuento_impuesto',
        'cantidad_comision_neta',
        'estatus',
        'created_at'
    ];

    protected $primaryKey = 'id';

    protected $table = 'supervisor_comisiones_foto_video';

    public function venta()
    {
        return $this->belongsTo(FotoVideoVenta::class,'venta_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class,'supervisor_id');
    }
}
