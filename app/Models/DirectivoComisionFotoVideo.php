<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionFotoVideo extends Model
{
    use HasFactory;
    protected $fillable = [
        'directivo_id',
        'venta_id',
        'pago_total_sin_iva',
        'cantidad_comision_bruta',
        'iva',
        'descuento_impuesto',
        'cantidad_comision_neta',
        'estatus',
        'created_at'
    ];
    protected $primaryKey = 'id';
    protected $table = 'directivo_comisiones_foto_video';
}
