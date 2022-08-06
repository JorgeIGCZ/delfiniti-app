<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservacionTicket extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservacion_id',
        'ticket'
    ];
    protected $primaryKey = 'id';

    public function reservacion()
    {
        return $this->belongsTo(Reservacion::class,'reservacion_id');
    }

    protected $table = 'reservaciones_tickets';
}
