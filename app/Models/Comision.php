<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    use HasFactory;

    protected $fillable = [
        'comisionista_id',
        'reservacion_id',
        'pago_total',
        'cantidad_comision_bruta',
        'iva',
        'descuento_impuesto',
        'cantidad_comision_neta',
        'estatus'
    ];
    protected $primaryKey = 'id';
    protected $table = 'comisiones';

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class,'reservacion_id');
    }

    public function comisionista()
    {
        return $this->belongsTo(Comisionista::class,'comisionista_id');
    }

    public function comisionistaTipo()
    {
        return $this->hasOneThrough(
            ComisionistaTipo::class,
            Comisionista::class,
            'id', // Foreign key on the cars table...
            'id', // Foreign key on the owners table...
            'id', // Local key on the mechanics table...
            'tipo_id' // Local key on the cars table...
        );

    }
}