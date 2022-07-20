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
        return $this->hasMany(ReservacionDetalle::class,'actividad_horario_id','id');
    }
/*
    public function reservacion()
    {
        return $this->hasMany(
            Reservacion::class,
            'actividad_id',
            'id'
        );
    }
    */
    public function reservacion()
    {
        return $this->hasManyThrough(
            Reservacion::class,
            ReservacionDetalle::class,
            'actividad_horario_id', // FK ReservacionDetalle como comunica a ActividadHorario
            'id', // FK Reservacion como comunica a ReservacionDetalle
            'id', //local key ActividadHorario
            'reservacion_id' //ReservacionDetalle como comunica a Reservacion
        );
    }
}