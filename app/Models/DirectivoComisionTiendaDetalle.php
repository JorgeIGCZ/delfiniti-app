<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionTiendaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'directivo_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];
    protected $primaryKey = 'id';
    protected $table = 'directivo_comisiones_tienda_detalle';
}
