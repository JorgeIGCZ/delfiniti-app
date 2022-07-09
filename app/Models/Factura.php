<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservacion_id',
        'total',
        'pagado',
        'adeudo'
    ];
    protected $primaryKey = 'id';

    public function pagos()
    {
        return $this->hasMany(Pagos::class,'factura_id');
    }
   //public function articulos()
   //{
   //    return $this->hasMany(Factura::class,'factura_id');
   //}
}
