<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FotoVideoVentaFactura extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'total',
        'pagado',
        'adeudo'
    ];
    protected $primaryKey = 'id';
}
