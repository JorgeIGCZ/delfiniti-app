<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoAutorizacionPeticion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'reservacion_id',
        'codigo_descuento_id',
        'nombre_cliente',
        'fecha_peticion'
    ];
    protected $primaryKey = 'id';
    protected $table = 'codigo_autorizacion_peticiones';
}
 