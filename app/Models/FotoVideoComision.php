<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoComision extends Model
{
    use HasFactory;

    protected $fillable = [
        'comisionista_id',
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
    protected $table = 'foto_video_comisiones';

    public function venta()
    {
        return $this->belongsTo(FotoVideoVenta::class,'venta_id');
    }

    public function comisionista()
    {
        return $this->belongsTo(FotoVideoComisionista::class,'comisionista_id');
    }
}
