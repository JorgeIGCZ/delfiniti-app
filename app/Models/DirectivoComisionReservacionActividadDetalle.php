<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionReservacionActividadDetalle extends Model
{
    use HasFactory;
    protected $fillable = [
        'actividad_id',
        'comision',
        'descuento_impuesto'
    ];
    
    protected $primaryKey = 'id';
    protected $table = 'directivo_comisiones_reservacion_actividad_detalle';
}
