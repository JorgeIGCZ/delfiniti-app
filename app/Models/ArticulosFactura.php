<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticulosFactura extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'reservacion_id',
        'actividad_id',
        'actividad_horario_id',
        'actividad_fecha',
        'numero_personas'
    ];
    protected $primaryKey = 'id';
}
