<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoTipo extends Model
{ 
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];
    protected $primaryKey = 'id';
}
