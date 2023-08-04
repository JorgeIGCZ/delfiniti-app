<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
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

    protected $table = 'supervisores';

    public function supervisorComisionTiendaDetalle()
    {
        return $this->hasOne(SupervisorComisionTiendaDetalle::class,'supervisor_id');
    }

    public function supervisorComisionFotoVideoDetalle()
    {
        return $this->hasOne(SupervisorComisionFotoVideoDetalle::class,'supervisor_id');
    }

    public function supervisorComisionTienda()
    {
        return $this->hasMany(SupervisorComisionTienda::class,'supervisor_id');
    }
    
    public function supervisorComisionFotoVideo()
    {
        return $this->hasMany(SupervisorComisionFotoVideo::class,'supervisor_id');
    }
}
