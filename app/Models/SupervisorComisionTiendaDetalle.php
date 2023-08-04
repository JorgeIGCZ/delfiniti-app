<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorComisionTiendaDetalle extends Model
{
    use HasFactory;
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];

    protected $primaryKey = 'id';

    protected $table = 'supervisor_comisiones_tienda_detalle';
}
