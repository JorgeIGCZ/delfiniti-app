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
        'pago_total_sin_iva',
        'cantidad_comision_bruta',
        'iva',
        'descuento_impuesto',
        'cantidad_comision_neta',
        'estatus',
        'created_at'
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

    public function canalVenta()
    {
        return $this->comisionista->tipo;
    }
}