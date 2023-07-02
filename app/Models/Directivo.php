<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Directivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'nombre',
        'direccion',
        'telefono',
        'estatus'
    ];
    protected $primaryKey = 'id';
    
    public function directivoComisionReservacionCanalDetalle()
    {
        return $this->hasMany(DirectivoComisionReservacionCanalDetalle::class,'directivo_id');
    }

    public function directivoComisionTiendaDetalle()
    {
        return $this->hasOne(DirectivoComisionTiendaDetalle::class,'directivo_id');
    }

    public function directivoComisionFotoVideoDetalle()
    {
        return $this->hasOne(DirectivoComisionFotoVideoDetalle::class,'directivo_id');
    }

    public function directivoComisionReservacion()
    {
        return $this->hasMany(DirectivoComisionReservacion::class,'directivo_id');
    }
    public function directivoComisionTienda()
    {
        return $this->hasMany(DirectivoComisionTienda::class,'directivo_id');
    }
    public function directivoComisionFotoVideo()
    {
        return $this->hasMany(DirectivoComisionFotoVideo::class,'directivo_id');
    }
}
