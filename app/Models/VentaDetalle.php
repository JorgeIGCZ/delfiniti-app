<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'factura_id',
        'venta_id',
        'producto_id',
        'numero_productos',
        'PPU'
    ];
    protected $primaryKey = 'id';
}