<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\realvirtual\models;

use yii\base\Model;

class StructCfd extends Model
{
    /**
     * SALIDAS DEL WS.
     */
    public $RfcEmisor;
    public $RfcReceptor;
    public $Version;
    public $Serie;
    public $Folio;
    public $FechaExpedicion;
    public $MontoOperacion;
    public $MontoImpuesto;
    public $TipoComprobante;
    public $Cadena;
    public $Firma;
    public $SerieCertificado;
    public $Cfdi;
    public $Timbre;
    public $Descripcion;
    public $state;

    public function rules()
    {
        return [
            [['RfcEmisor', 'RfcReceptor', 'Version', 'Serie', 'Folio', 'FechaExpedicion', 'MontoOperacion', 'MontoImpuesto', 'TipoComprobante', 'Cadena', 'Firma', 'SerieCertificado', 'Cfdi', 'Timbre', 'Descripcion', 'state'], 'safe'],
        ];
    }
}
