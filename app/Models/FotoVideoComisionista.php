<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoComisionista extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'comision',
        'iva',
        'descuento_impuesto',
        'direccion',
        'telefono',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function comisiones()
    {
        return $this->hasMany(FotoVideoComision::class,'comisionista_id');
    }
}
