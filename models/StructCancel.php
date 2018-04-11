<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\realvirtual\models;

use yii\base\Model;

class StructCancel extends Model
{
    /**
     * SALIDAS DEL WS.
     */
    public $RfcSolicitante;
    public $Fecha;
    public $UUIDs;
    public $NoSerieFirmante;
    public $Descripcion;
    public $state;

    public function rules()
    {
        return [
            [['RfcSolicitante', 'Fecha', 'UUIDs', 'NoSerieFirmante', 'Descripcion', 'state'], 'safe'],
        ];
    }
}
