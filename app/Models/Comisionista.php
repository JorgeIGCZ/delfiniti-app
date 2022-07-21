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
        'descuento_impuesto',
        'descuentos',
        'representante',
        'direccion',
        'telefono',
        'tipo_id'
    ];
    protected $primaryKey = 'id';

    public function tipo()
    {
        return $this->belongsTo(ComisionistaTipo::class,'tipo_id');
    }
}
