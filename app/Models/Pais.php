<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre'
    ];
    
    protected $primaryKey = 'id';
    protected $table = 'paises';

    public function estados()
    {
        return $this->hasMany(Estado::class,'pais_id');
    }
}
