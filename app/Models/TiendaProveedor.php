<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiendaProveedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'clave',
        'razon_social',
        'RFC',
        'nombre_contacto',
        'cargo_contacto',
        'direccion',
        'ciudad',
        'estado',
        'cp',
        'pais',
        'telefono',
        'email',
        'comentarios',
        'estatus'
    ];
    protected $primaryKey = 'id';  
    
    protected $table = 'tienda_proveedores';
}
