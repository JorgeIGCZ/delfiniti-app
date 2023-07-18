<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaImpuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'impuesto',
        'estatus'
    ];
    

    public function tiendaPedidoImpuesto()
    {
        return $this->belongsTo(TiendaPedidoImpuesto::class,'id','impuesto_id');
    }
    
    protected $primaryKey = 'id';
}
