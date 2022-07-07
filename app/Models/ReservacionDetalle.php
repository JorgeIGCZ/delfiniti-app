<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservacionDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'reservacion_id',
        'actividad_id',
        'actividad_horario_id',
        'actividad_fecha',
        'numero_personas',
        'PPU'
    ];
    protected $primaryKey = 'id';

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class,'reservacion_id');
    }

    public function actividad()
    {
        return $this->hasOne(Actividad::class,'id','actividad_id');
    }

    public function horario()
    {
        return $this->hasOne(ActividadHorario::class,'id','actividad_horario_id');
    }
}
