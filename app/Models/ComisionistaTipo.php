<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionistaTipo extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre'
    ];
    protected $primaryKey = 'id';

    public function comisionistas()
    {
        return $this->hasMany(Comisionista::class,'tipo_id');
    }

    // public function comisiones()
    // {
    //     return $this->hasManyThrough(
    //         Comision::class,
    //         Comisionista::class,
    //         'id', // FK Comisionista como comunica a ComisionistaTipo
    //         'id', // FK Comisionista como comunica a Comision
    //         'id', //local key ComisionistaTipo
    //         'id' //local key Comision
    //     );
    // }
}
