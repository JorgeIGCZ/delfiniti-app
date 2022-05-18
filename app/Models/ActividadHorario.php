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
}
 