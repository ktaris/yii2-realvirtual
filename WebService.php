<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\realvirtual;

use ktaris\realvirtual\models\StructCfd;
use ktaris\realvirtual\models\StructCancel;
use ktaris\realvirtual\models\XmlDeCancelacion;

class WebService
{
    /**
     * @var string URL del servicio web de prueba de Real Virtual.
     */
    protected $_ws_url_prueba = 'http://108.60.211.43/rvltimbrado/service1.asmx?WSDL';
    /**
     * @var string URL del servicio web de Real Virtual (de producción).
     */
    protected $_ws_url_produccion = 'http://generacfdi.com.mx/rvltimbrado/service1.asmx?WSDL';
    /**
     * @var SoapClient Cliente de servicio web de RealVirtual.
     */
    protected $_cliente;
    /**
     * @var string cadena que almacena el nombre de usuario para conexión con RealVirtual.
     */
    protected $_usuario;
    /**
     * @var string cadena que almacena la contraseña del usuario para conexión con RealVirtual.
     */
    protected $_contrasenia;

    // ==================================================================
    //
    // Métodos expuestos por RealVirtual
    //
    // ------------------------------------------------------------------

    public function establecerCredenciales($usuario, $contrasenia)
    {
        $this->_usuario = $usuario;
        $this->_contrasenia = $contrasenia;

        $this->setHeaders($this->_usuario, $this->_contrasenia);
    }

    public function GetTicket($preCfdi)
    {
        return $this->llamarATimbrado('GetTicket', $preCfdi);
    }

    public function TestCfd33($preCfdi)
    {
        return $this->llamarATimbrado('TestCfd33', $preCfdi);
    }

    public function CancelTest($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        return $this->llamarACancelacion('CancelTest', $rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj);
    }

    public function CancelTicket($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        return $this->llamarACancelacion('CancelTicket', $rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj);
    }

    // ==================================================================
    //
    // Llamadas internas a los métodos necesarios.
    //
    // ------------------------------------------------------------------

    protected function llamarATimbrado($metodo, $preCfdi)
    {
        $res = $this->_cliente->$metodo(['base64Cfd' => base64_encode($preCfdi)]);

        $obj = new StructCfd;
        $obj->attributes = $this->convertirObjetoAArreglo($res, $metodo);
        $obj->attributes = $this->base64Decode(['Timbre', 'Cfdi'], $obj);

        return $obj;
    }

    protected function llamarACancelacion($metodo, $rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        if ($metodo == 'CancelTest') {
            $dataVar = 'CanB64';
        } else {
            $dataVar = 'base64Cfd';
        }

        $base64Xml = XmlDeCancelacion::crear($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj);

        $res = $this->_cliente->$metodo([$dataVar => $base64Xml]);

        $obj = new StructCancel;
        $obj->attributes = $this->convertirObjetoAArreglo($res, $metodo);

        return $obj;
    }

    // ==================================================================
    //
    // Lógica interna.
    //
    // ------------------------------------------------------------------

    /**
     * Determina cuál URL se utilizará para llamar a RealVirtual en base
     * a la bandera modoPrueba.
     * @param boolean $modoPrueba determina si se utiliza el servicio de pruebas.
     */
    public function __construct($modoPrueba = false)
    {
        if ($modoPrueba == true) {
            $this->_cliente = new \SoapClient($this->_ws_url_prueba, ['encoding' => 'UTF-8']);
        } else {
            $this->_cliente = new \SoapClient($this->_ws_url_produccion, ['encoding' => 'UTF-8']);
        }
    }

    protected function setHeaders($usuario, $contrasenia)
    {
        $credenciales = [
            'strUserName' => $usuario,
            'strPassword' => $contrasenia
        ];
        $encabezado = new \SoapHeader('http://tempuri.org/', 'AuthSoapHd', $credenciales, true, 1);
        $this->_cliente->__setSoapHeaders($encabezado);
    }

    protected function convertirObjetoAArreglo($obj, $metodo)
    {
        $arregloOut = json_decode(json_encode($obj), true);
        $arregloOut = $arregloOut[$metodo.'Result'];

        return $arregloOut;
    }

    protected function base64Decode($arreglo, $obj)
    {
        $arregloOut = [];
        foreach ($arreglo as $index => $propiedad) {
            // Revisamos si la propiedad tiene contenido.
            if (empty($obj->$propiedad)) {
                continue;
            }

            $arregloOut[$propiedad] = base64_decode($obj->$propiedad);
        }

        return $arregloOut;
    }
}
