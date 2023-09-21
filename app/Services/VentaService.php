<?php

namespace App\Services;

class VentaService
{
    private $folio;
    private $productos;
    private $nombreCliente;
    private $pagoEfectivo;
    private $pagoEfectivoUsd;
    private $pagoTarjeta;
    private $pagoDeposito;
    private $pagoCupon;
    private $cambio;
 
    public function __construct($folio = "", $productos = [], $nombreCliente = '', $pagoEfectivo = 0, $pagoEfectivoUsd = 0, $pagoTarjeta = 0, $pagoDeposito = 0, $pagoCupon = 0, $cambio = 0)
    {
        $this->folio = $folio;
        $this->productos = $productos;
        $this->nombreCliente = $nombreCliente;
        $this->pagoEfectivo = $pagoEfectivo;
        $this->pagoEfectivoUsd = $pagoEfectivoUsd;
        $this->pagoTarjeta = $pagoTarjeta;
        $this->pagoDeposito = $pagoDeposito;
        $this->pagoCupon = $pagoCupon;
        $this->cambio = $cambio;
    
    }
    
    public function getFolio()
    {
        return $this->folio;
    }
    
    public function getProductos()
    {
        return $this->productos;
    }

    public function getNombreCliente()
    {
        return $this->nombreCliente;
    }

    public function getPagoEfectivo()
    {
        return $this->pagoEfectivo;
    }

    public function getPagoEfectivoUsd()
    {
        return $this->pagoEfectivoUsd;
    }

    public function getPagoTarjeta()
    {
        return $this->pagoTarjeta;
    }

    public function getPagoDeposito()
    {
        return $this->pagoDeposito;
    }

    public function getPagoCupon()
    {
        return $this->pagoCupon;
    }

    public function getCambio()
    {
        return $this->cambio;
    }
    
    public function setFolio($folio)
    {
        $this->folio = $folio;
    }

    public function setProductos($productos)
    {
        $this->productos = $productos;
    }

    public function setNombreCliente($nombreCliente)
    {
        $this->nombreCliente = $nombreCliente;
    }

    public function setPagoEfectivo($pagoEfectivo)
    {
        $this->pagoEfectivo = $pagoEfectivo;
    }

    public function setPagoEfectivoUsd($pagoEfectivoUsd)
    {
        $this->pagoEfectivoUsd = $pagoEfectivoUsd;
    }

    public function setPagoTarjeta($pagoTarjeta)
    {
        $this->pagoTarjeta = $pagoTarjeta;
    }

    public function setPagoDeposito($pagoDeposito)
    {
        $this->pagoDeposito = $pagoDeposito;
    }

    public function setPagoCupon($pagoCupon)
    {
        $this->pagoCupon = $pagoCupon;
    }

    public function setCambio($cambio)
    {
        $this->cambio = $cambio;
    }
}