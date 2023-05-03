<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'nombre_cliente',
        'email',
        'origen',
        'direccion',
        'RFC',
        'fecha',
        'fecha_creacion',
        'usuario_id',
        'estatus_pago',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function ventaDetalle()
    {
        return $this->hasMany(FotoVideoVentaDetalle::class,'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(FotoVideoVentaPago::class,'venta_id');
    }

    public function tipoPago()
    {
        return $this->hasOneThrough(
            TipoPago::class,
            FotoVideoVentaPago::class,
            'venta_id', // FK Pago como comunica a Ventas
            'id', // FK Pago como comunica a TipoPago
            'id', //local key TipoPago
            'tipo_pago_id' //local key Pago
        );
    }

    public function usuario()
    {
        return $this->hasOne(User::class,'id','usuario_id');
    }
}
