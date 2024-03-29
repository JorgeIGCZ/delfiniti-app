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
        'usuario_id',
        'comisionista_id',
        'comisionista_actividad_id',
        'cerrador_id',
        'comentarios',
        'estatus_pago',
        'estatus',
        'fecha',
        'fecha_creacion',
        'comisionable',
        'comisiones_especiales',
        'comisiones_canal',
        'num_cupon'
    ];
    protected $primaryKey = 'id';  
    
    protected $table = 'reservaciones';

    public function facturas()
    {
        return $this->hasOne(Factura::class,'reservacion_id','id');
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
        return $this->hasOne(Alojamiento::class,'alojamiento');
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class,'reservacion_id');
    }

    public function comisionista()
    {
        return $this->hasOne(Comisionista::class,'id','comisionista_id');
    }

    public function comisionesDirectivos()
    {
        return $this->hasMany(DirectivoComisionReservacion::class,'reservacion_id');
    }

    public function directivo()
    {
        return $this->hasOne(Directivo::class,'id','directivo_id');
    }

    public function usuario()
    {
        return $this->hasOne(User::class,'id','usuario_id');
    }
    
    public function actividad()
    {
        return $this->hasManyThrough(
            Actividad::class,
            ReservacionDetalle::class,
            'reservacion_id', // FK ReservacionDetalle como comunica a Reservacion
            'id', // FK Actividad como comunica a ReservacionDetalle
            'id', //local key Reservacion
            'actividad_id' //local key ReservacionDetalle
        );
    }
    // está mal
    // public function horario()
    // {
    //     return $this->hasManyThrough(
    //         ActividadHorario::class,
    //         ReservacionDetalle::class,
    //         'reservacion_id', // FK ReservacionDetalle como comunica a Reservacion
    //         'actividad_id', // FK Actividad como comunica a ReservacionDetalle
    //         'id', //local key Reservacion
    //         'id' //local key ReservacionDetalle
    //     );
    // }
    public function descuentoCodigo()
    {
        return $this->hasOneThrough(
            DescuentoCodigo::class,
            Pago::class,
            'reservacion_id', // FK Pago como comunica a Reservacion
            'id', // FK  como comunica a DescuentoCodigo
            'id', //local key Reservaciones
            'descuento_codigo_id' //local key Pago
        );
    }
    public function tipoPago()
    {
        return $this->belongsToMany(TipoPago::class, 'pagos', 'reservacion_id', 'tipo_pago_id');
    }
}
