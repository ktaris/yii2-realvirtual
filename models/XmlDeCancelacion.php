<?php

/**
 * @copyright Copyright (c) 2017 Carlos Ramos
 * @package ktaris-cfdi
 * @version 0.1.0
 */

namespace ktaris\realvirtual\models;

class XmlDeCancelacion
{
    public static function crear($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        $xmlObj = new self;
        return $xmlObj->generarXml($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj);
    }

    public function generarXml($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        $serieCompleta = $csdObj->leerPropiedad('serialNumber');
        $sello = $this->generarSello($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj);
        $issuer = $csdObj->leerPropiedad('issuer');
        $cadenaOid = $this->generarCadenaOid($issuer);
        $certificado = $csdObj->getCertificado();
        $digest = $this->generarDigestParaCancelacion($rfcEmisor, $folioFiscal, $fechaCancelacion);

        $cadenaCompleta = '<Cancelacion xmlns="http://cancelacfd.sat.gob.mx" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Fecha="'.$fechaCancelacion.'" RfcEmisor="'.$rfcEmisor.'"><Folios><UUID>'.$folioFiscal.'</UUID></Folios><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" /><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1" /><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" /></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1" /><DigestValue>'.$digest.'</DigestValue></Reference></SignedInfo><SignatureValue>'.$sello.'</SignatureValue><KeyInfo><X509Data><X509IssuerSerial><X509IssuerName>'.$cadenaOid.'</X509IssuerName><X509SerialNumber>'.$serieCompleta.'</X509SerialNumber></X509IssuerSerial><X509Certificate>'.$certificado.'</X509Certificate></X509Data></KeyInfo></Signature></Cancelacion>';

        return base64_encode($cadenaCompleta);
    }

    protected function generarSello($rfcEmisor, $folioFiscal, $fechaCancelacion, $csdObj)
    {
        $digest = $this->generarDigestParaCancelacion($rfcEmisor, $folioFiscal, $fechaCancelacion);

        $cadena ='<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></CanonicalizationMethod><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"></Transform></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod><DigestValue>'.$digest.'</DigestValue></Reference></SignedInfo>';

        return $csdObj->generarSelloConSha1($cadena);
    }

    protected function generarDigestParaCancelacion($rfcEmisor, $folioFiscal, $fechaCancelacion)
    {
        $xmlString = $this->generarXmlParaDigestDeCancelacion($rfcEmisor, $folioFiscal, $fechaCancelacion);
        $dom = new \DOMDocument();
        $dom->loadXML($xmlString);
        $canonicalized = $dom->C14N();
        $digest = base64_encode(pack("H*", sha1($xmlString)));

        return $digest;
    }

    protected function generarXmlParaDigestDeCancelacion($rfcEmisor, $folioFiscal, $fechaCancelacion)
    {
        return '<Cancelacion xmlns="http://cancelacfd.sat.gob.mx" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" Fecha="'.$fechaCancelacion.'" RfcEmisor="'.$rfcEmisor.'"><Folios><UUID>'.$folioFiscal.'</UUID></Folios></Cancelacion>';
    }

    protected function generarCadenaOid($issuer)
    {
        $cadena  = 'OID.1.2.840.113549.1.9.2=';
        $cadena .= $issuer['unstructuredName'];
        $cadena .= ', L='.$issuer['L'];
        $cadena .= ', S='.$issuer['ST'];
        $cadena .= ', C='.$issuer['C'];
        $cadena .= ', PostalCode='.$issuer['postalCode'];
        $cadena .= ', STREET="'.$issuer['street'].'"';
        $cadena .= ', E='.$issuer['emailAddress'];
        $cadena .= ', OU='.$issuer['OU'];
        $cadena .= ', O='.$issuer['O'];
        $cadena .= ', CN='.$issuer['CN'];

        return $cadena;
    }
}
