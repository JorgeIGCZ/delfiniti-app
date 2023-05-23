<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaPedidoDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'PPU',
        'subtotal'
    ];
    protected $primaryKey = 'id';

    public function producto()
    {
        return $this->hasOne(TiendaProducto::class,'id','producto_id');
    }
}
