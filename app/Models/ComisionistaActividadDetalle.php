<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionistaActividadDetalle extends Model
{
    use HasFactory;
    protected $fillable = [
        'comisionista_id',
        'actividad_id',
        'comision',
        'descuento_impuesto'
    ];
    
    protected $primaryKey = 'id';
    protected $table = 'comisionista_actividad_detalle';
}
