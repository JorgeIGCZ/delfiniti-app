<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaVentaTicket extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'ticket'
    ];
    protected $primaryKey = 'id';

    public function venta()
    {
        return $this->belongsTo(TiendaVenta::class,'venta_id');
    }

    protected $table = 'tienda_venta_tickets';
}
