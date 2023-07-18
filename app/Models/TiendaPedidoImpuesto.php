<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaPedidoImpuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'impuesto_id',
        'total'
    ];
    protected $primaryKey = 'id';

    public function pedido()
    {
        return $this->hasOne(TiendaPedido::class,'id','pedido_id');
    }

    public function impuesto()
    {
        return $this->hasOne(TiendaImpuesto::class,'id','impuesto_id');
    }
}