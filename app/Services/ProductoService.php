<?php

namespace App\Services;

class ProductoService
{
    private $id;
    private $clave;
    private $precio;
    private $numeroProductos;
    private $nombre;
    private $cantidadPagada;
    private $tipo;
 
    public function __construct($id = 0, $clave = "", $precio = 0, $numeroProductos = 0, $nombre = "", $cantidadPagada = 0, $tipo = "")
    {
        $this->id = $id;
        $this->clave = $clave;
        $this->precio = $precio;
        $this->numeroProductos = $numeroProductos;
        $this->nombre = $nombre;
        $this->cantidadPagada = $cantidadPagada;
        $this->tipo = $tipo;
    
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

    public function getNumeroProductos()
    {
        return $this->numeroProductos;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getCantidadPagada()
    {
        return $this->cantidadPagada;
    }

    public function getTipo()
    {
        return $this->tipo;
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

    public function setNumeroProductos($numeroProductos)
    {
        $this->numeroProductos = $numeroProductos;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function setCantidadPagada($cantidadPagada)
    {
        $this->cantidadPagada = $cantidadPagada;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }
}