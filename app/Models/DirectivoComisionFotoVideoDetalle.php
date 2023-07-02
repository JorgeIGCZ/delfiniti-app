<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectivoComisionFotoVideoDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'directivo_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];
    protected $primaryKey = 'id';
    protected $table = 'directivo_comisiones_foto_video_detalle';
}
