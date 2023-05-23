<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaImpuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'impuesto',
        'estatus'
    ];
    
    protected $primaryKey = 'id';
}
