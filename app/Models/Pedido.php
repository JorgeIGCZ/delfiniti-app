<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'fecha_creacion',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';

    public function pedidoDetalle()
    {
        return $this->hasMany(PedidoDetalle::class,'pedido_id');
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
        return $this->hasOne(Proveedor::class,'id','proveedor_id');
    }
}
