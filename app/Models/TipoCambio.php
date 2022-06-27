<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCambio extends Model
{
    use HasFactory;

    protected $fillable = [
        'seccion_uso',
        'divisa',
        'precio_compra',
        'precio_venta'
    ];
    protected $primaryKey = 'id';

    protected $table = 'tipos_cambio';
}
