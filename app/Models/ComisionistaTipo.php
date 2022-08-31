<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionistaTipo extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'comisionista_canal'
    ];
    protected $primaryKey = 'id';

    public function comisionistas()
    {
        return $this->hasMany(Comisionista::class,'tipo_id');
    }

    public function comisionistaCanalDetalle()
    {
        return $this->hasMany(ComisionistaCanalDetalle::class,'comisionista_tipo_id');
    }

    // public function reservaciones(){
    //     return $this->hasManyThrough(
    //         Reservacion::class,
    //         Comisionista::class,
    //         'tipo_id', // FK Comisionista como comunica a ComisionistaTipo
    //         'comisionista_id', // FK Comisionista como comunica a Reservacion
    //         'id', //local key ComisionistaTipo
    //         'id' //local key Reservacion
    //     );
    // }

    // public function reservacionesCerrador(){
    //     return $this->hasManyThrough(
    //         Reservacion::class,
    //         Comisionista::class,
    //         'tipo_id', // FK Comisionista como comunica a ComisionistaTipo
    //         'cerrador_id', // FK Comisionista como comunica a Reservacion
    //         'id', //local key ComisionistaTipo
    //         'id' //local key Reservacion
    //     );
    // }

    public function comisiones()
    {
        return $this->hasManyThrough(
            Comision::class,
            Comisionista::class,
            'tipo_id', // FK Comisionista como comunica a ComisionistaTipo
            'comisionista_id', // FK Comisionista como comunica a Comision
            'id', //local key ComisionistaTipo
            'id' //local key Comision
        );
    }

    public function reservacionesTest(){
        return $this->hasManyThrough(
            Reservacion::class,
            Comision::class,
            'tipo_id', // FK Comision como comunica a ComisionistaTipo
            'reservacion_id', // FK Comision como comunica a Reservacion
            'id', //local key ComisionistaTipo
            'id' //local key Reservacion
        );
    }
}
