<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comisionista extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'comision',
        'iva',
    ];
    protected $primaryKey = 'id';
}
