<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaProductoImpuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'impuesto_id'
    ];
    
    protected $primaryKey = 'id';
}
