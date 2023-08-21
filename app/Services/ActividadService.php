<?php

namespace App\Services;

class ActividadService
{
    private $id;
    private $clave;
    private $precio;
    private $numeroPersonas;
    private $nombre;
    private $cantidadPagada;
 
    public function __construct($id = 0, $clave = "", $precio = 0, $numeroPersonas = 0, $nombre = "", $cantidadPagada = 0)
    {
        $this->id = $id;
        $this->clave = $clave;
        $this->precio = $precio;
        $this->numeroPersonas = $numeroPersonas;
        $this->nombre = $nombre;
        $this->cantidadPagada = $cantidadPagada;
    
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getClave()
    {
        return $this->clave;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    public function getNumeroPersonas()
    {
        return $this->numeroPersonas;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getCantidadPagada()
    {
        return $this->cantidadPagada;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setClave($clave)
    {
        $this->clave = $clave;
    }

    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    public function setNumeroPersonas($numeroPersonas)
    {
        $this->numeroPersonas = $numeroPersonas;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setCantidadPagada($cantidadPagada)
    {
        $this->cantidadPagada = $cantidadPagada;
    }
}