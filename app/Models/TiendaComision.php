<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaComision extends Model
{
    use HasFactory;

    protected $fillable = [
        'comisionista_id',
        'venta_id',
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
    protected $table = 'tienda_comisiones';

    public function venta()
    {
        return $this->belongsTo(TiendaVenta::class,'venta_id');
    }

    public function comisionista()
    {
        return $this->belongsTo(TiendaComisionista::class,'comisionista_id' ,'usuario_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'comisionista_id', 'id');
    }
}
