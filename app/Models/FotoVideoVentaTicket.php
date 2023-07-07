<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoVentaTicket extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'ticket'
    ];
    protected $primaryKey = 'id';

    public function venta()
    {
        return $this->belongsTo(FotoVideoVenta::class,'venta_id');
    }

    protected $table = 'foto_video_venta_tickets';
}
