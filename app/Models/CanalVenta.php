<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanalVenta extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'comisionista_canal',
        'comisionista_actividad'
    ];
    protected $primaryKey = 'id';

    protected $table = 'canales_ventas';

    public function comisionistas()
    {
        return $this->hasMany(Comisionista::class,'canal_venta_id');
    }

    public function comisionistaCanalDetalle()
    {
        return $this->hasMany(ComisionistaCanalDetalle::class,'canal_venta_id');
    }

    // public function reservaciones(){
    //     return $this->hasManyThrough(
    //         Reservacion::class,
    //         Comisionista::class,
    //         'canal_venta_id', // FK Comisionista como comunica a CanalVenta
    //         'comisionista_id', // FK Comisionista como comunica a Reservacion
    //         'id', //local key CanalVenta
    //         'id' //local key Reservacion
    //     );
    // }

    // public function reservacionesCerrador(){
    //     return $this->hasManyThrough(
    //         Reservacion::class,
    //         Comisionista::class,
    //         'canal_venta_id', // FK Comisionista como comunica a CanalVenta
    //         'cerrador_id', // FK Comisionista como comunica a Reservacion
    //         'id', //local key CanalVenta
    //         'id' //local key Reservacion
    //     );
    // }

    public function comisiones()
    {
        return $this->hasManyThrough(
            Comision::class,
            Comisionista::class,
            'canal_venta_id', // FK Comisionista como comunica a CanalVenta
            'comisionista_id', // FK Comisionista como comunica a Comision
            'id', //local key CanalVenta
            'id' //local key Comision
        );
    }

    public function reservacionesTest(){
        return $this->hasManyThrough(
            Reservacion::class,
            Comision::class,
            'canal_venta_id', // FK Comision como comunica a CanalVenta
            'reservacion_id', // FK Comision como comunica a Reservacion
            'id', //local key CanalVenta
            'id' //local key Reservacion
        );
    }
}
