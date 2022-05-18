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
        'fecha_inicial',
        'fecha_final'
    ];
    protected $primaryKey = 'id';
    
    protected $table = 'actividades';

    public function horarios()
    {
        return $this->hasMany(ActividadHorario::class,'actividad_id');
    }
}
