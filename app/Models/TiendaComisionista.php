<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaComisionista extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'comision',
        'iva',
        'descuento_impuesto'
    ];
    protected $primaryKey = 'id';

    public function usuario()
    {
        return $this->hasOne(User::class,'id','usuario_id');
    }

    public function comisiones()
    {
        return $this->hasMany(TiendaComision::class, 'comisionista_id', 'usuario_id');
    }
}