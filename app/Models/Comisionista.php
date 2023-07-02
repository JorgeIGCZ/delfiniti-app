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
        'canal_venta_id',
        'comisiones_canal'
    ];
    protected $primaryKey = 'id';

    public function tipo()
    {
        return $this->belongsTo(CanalVenta::class,'canal_venta_id');
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class,'comisionista_id');
    }
}
