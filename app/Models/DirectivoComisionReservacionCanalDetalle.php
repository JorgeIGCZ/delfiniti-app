<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionReservacionCanalDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'directivo_id',
        'canal_venta_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];
    protected $primaryKey = 'id';
    protected $table = 'directivo_comisiones_reservacion_canal_detalle';
}