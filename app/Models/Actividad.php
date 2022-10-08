<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'nombre',
        'precio',
        'capacidad',
        'duracion',
        'reporte_orden',
        'comisionable',
        'fecha_inicial',
        'fecha_final'
    ];
    protected $primaryKey = 'id';

    protected $table = 'actividades';

    public function reservacionDetalle()
    {
        return $this->hasMany(ReservacionDetalle::class,'actividad_id','id');
    }

    public function horarios()
    {
        return $this->hasMany(ActividadHorario::class, 'actividad_id');
    }

    public function pagos()
    {
        return $this->hasManyThrough(
            Pago::class,
            ReservacionDetalle::class,
            'actividad_id', // FK ReservacionDetalle como comunica a Actividad
            'reservacion_id', // FK pago como comunica a ReservacionDetalle
            'id', //local key Actividad
            'reservacion_id' //ReservacionDetalle como comunica a pagp
        );
    }

    public function reservaciones()
    {
        return $this->hasManyThrough(
            Reservacion::class,
            ReservacionDetalle::class,
            'actividad_id', // FK ReservacionDetalle como comunica a Actividad
            'id', // FK Reservacion como comunica a ReservacionDetalle
            'id', //local key ReservacionDetalle
            'reservacion_id' //ReservacionDetalle como comunica a Reservacion
        );
    }
}
