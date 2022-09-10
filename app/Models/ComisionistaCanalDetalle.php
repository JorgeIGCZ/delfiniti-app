<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionistaCanalDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'comisionista_id',
        'canal_venta_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];
    protected $primaryKey = 'id';
    protected $table = 'comisionista_canal_detalle';
}