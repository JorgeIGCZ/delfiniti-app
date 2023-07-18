<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaPedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'fecha_pedido',
        'fecha_autorizacion',
        'comentarios',
        'estatus_proceso',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function pedidoDetalle()
    {
        return $this->hasMany(TiendaPedidoDetalle::class,'pedido_id');
    }

    public function pedidoImpuesto()
    {
        return $this->hasMany(TiendaPedidoImpuesto::class,'sail php artisan migrate');
    }

    // public function producto()
    // {
    //     return $this->hasManyThrough(
    //         Producto::class,
    //         PedidoDetalle::class,
    //         'pedido_id', // FK ReservacionDetalle como comunica a Reservacion
    //         'id', // FK Actividad como comunica a ReservacionDetalle
    //         'id', //local key Reservacion
    //         'producto_id' //local key ReservacionDetalle
    //     );
    // }

    public function proveedor()
    {
        return $this->hasOne(TiendaProveedor::class,'id','proveedor_id');
    }
}
