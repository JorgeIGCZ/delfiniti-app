<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadHorario extends Model
{
    use HasFactory;
    protected $fillable = [
        'actividad_id',
        'horario_inicial',
        'horario_final'
    ];
    protected $primaryKey = 'id';
    protected $table = 'actividad_horarios';

    public function actividad()
    {
        return $this->belongsTo(Actividad::class,'actividad_id');
    }

    public function reservacionDetalle()
    {
        return $this->hasMany(ReservacionDetalle::class,'actividad_id','actividad_id');
    }
    
    public function reservacion()
    {
        return $this->hasManyThrough(
            Reservacion::class,
            ReservacionDetalle::class,
            'actividad_id',
            'id',
            'actividad_id',
            'reservacion_id');
    }
}