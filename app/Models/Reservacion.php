<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre_cliente',
        'email',
        'localizacion',
        'origen',
        'agente_id',
        'comisionista_id',
        'comentarios',
        'estatus',
        'fecha_creacion'
    ];
    protected $primaryKey = 'id';
    
    protected $table = 'reservaciones';

    public function facturas()
    {
        return $this->hasOne(Factura::class,'reservacion_id');
    }
    public function pagos()
    {
        return $this->hasMany(Pago::class,'reservacion_id');
    }
    public function articulos()
    {
        return $this->hasMany(Factura::class,'reservacion_id');
    }
}
