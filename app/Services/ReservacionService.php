<?php

namespace App\Services;

class ReservacionService
{
    private $folio;
    private $actividades;
    private $nombreCliente;
    private $pagoEfectivo;
    private $pagoEfectivoUsd;
    private $pagoTarjeta;
    private $pagoDeposito;
    private $pagoCupon;
 
    public function __construct($folio = "", $actividades = [], $nombreCliente = '', $pagoEfectivo = 0, $pagoEfectivoUsd = 0, $pagoTarjeta = 0, $pagoDeposito = 0, $pagoCupon = 0)
    {
        $this->folio = $folio;
        $this->actividades = $actividades;
        $this->nombreCliente = $nombreCliente;
        $this->pagoEfectivo = $pagoEfectivo;
        $this->pagoEfectivoUsd = $pagoEfectivoUsd;
        $this->pagoTarjeta = $pagoTarjeta;
        $this->pagoDeposito = $pagoDeposito;
        $this->pagoCupon = $pagoCupon;
    
    }
    
    public function getFolio()
    {
        return $this->folio;
    }
    
    public function getActividades()
    {
        return $this->actividades;
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
    
    public function setFolio($folio)
    {
        $this->folio = $folio;
    }

    public function setActividades($actividades)
    {
        $this->actividades = $actividades;
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
}