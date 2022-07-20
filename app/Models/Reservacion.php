<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'folio',
        'nombre_cliente',
        'email',
        'alojamiento',
        'origen',
        'agente_id',
        'comisionista_id',
        'cerrador_id',
        'comentarios',
        'estatus',
        'fecha',
        'fecha_creacion'
    ];
    protected $primaryKey = 'id';  
    
    protected $table = 'reservaciones';

    public function facturas()
    {
        return $this->hasOne(Factura::class,'reservacion_id');
    }
    public function pagos()
    {
        return $this->hasMany(Pago::class,'reservacion_id');
    }
    //public function articulos()
    //{
    //    return $this->hasMany(Factura::class,'reservacion_id');
    //}
    public function reservacionDetalle()
    {
        return $this->hasMany(ReservacionDetalle::class,'reservacion_id');
    }

    public function alojamiento()
    {
        return $this->hasOne(Alojamiento::class,'id','alojamiento');
    }
    
    public function actividad()
    {
        return $this->hasManyThrough(
            Actividad::class,
            ReservacionDetalle::class,
            'reservacion_id', // FK ReservacionDetalle como comunica a Reservacion
            'id', // FK Actividad como comunica a ReservacionDetalle
            'id', //local key Reservacion
            'id' //local key ReservacionDetalle
        );
    }
    public function horario()
    {
        return $this->hasManyThrough(
            ActividadHorario::class,
            ReservacionDetalle::class,
            'reservacion_id', // FK ReservacionDetalle como comunica a Reservacion
            'actividad_id', // FK Actividad como comunica a ReservacionDetalle
            'id', //local key Reservacion
            'id' //local key ReservacionDetalle
        );
    }
    public function tipoPago()
    {
        return $this->hasOneThrough(
            TipoPago::class,
            Pago::class,
            'reservacion_id', // FK Pago como comunica a Reservacion
            'id', // FK TipoPago como comunica a Pago
            'id', //local key TipoPago
            'id' //local key Pago
        );
    }
}
