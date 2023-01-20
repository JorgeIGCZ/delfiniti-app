<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadComisionDetalle extends Model
{

    use HasFactory;
    protected $fillable = [
        'actividad_id',
        'canal_venta_id',
        'comision',
        'descuento_impuesto'
    ];
    
    protected $primaryKey = 'id';
    protected $table = 'actividad_comision_detalle';
}
