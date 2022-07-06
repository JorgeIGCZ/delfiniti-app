<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cerrador extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'comision',
        'iva',
        'direccion',
        'telefono'
    ];
    protected $primaryKey = 'id';
    protected $table = 'cerradores';
}
