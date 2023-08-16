<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionReservacion extends Model
{
    use HasFactory;
    protected $fillable = [
        'directivo_id',
        'reservacion_id',
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
    protected $table = 'directivo_comisiones_reservacion';

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class,'reservacion_id');
    }

    public function directivo()
    {
        return $this->belongsTo(Directivo::class,'directivo_id');
    }

}