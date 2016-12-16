<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing X509DataType
 *
 *
 * XSD Type: X509DataType
 */
class X509DataType
{

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509IssuerSerialType[]
     * $x509IssuerSerial
     */
    private $x509IssuerSerial = 'array()';

    /**
     * @property mixed[] $x509SKI
     */
    private $x509SKI = 'array()';

    /**
     * @property string[] $x509SubjectName
     */
    private $x509SubjectName = 'array()';

    /**
     * @property mixed[] $x509Certificate
     */
    private $x509Certificate = 'array()';

    /**
     * @property mixed[] $x509CRL
     */
    private $x509CRL = 'array()';

    /**
     * @property mixed[] $anyElement
     */
    private $anyElement = array(
        
    );

    /**
     * Adds as x509IssuerSerial
     *
     * @return self
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509IssuerSerialType
     * $x509IssuerSerial
     */
    public function addToX509IssuerSerial(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509IssuerSerialType $x509IssuerSerial)
    {
        $this->x509IssuerSerial[] = $x509IssuerSerial;
        return $this;
    }

    /**
     * isset x509IssuerSerial
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509IssuerSerial($index)
    {
        return isset($this->x509IssuerSerial[$index]);
    }

    /**
     * unset x509IssuerSerial
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509IssuerSerial($index)
    {
        unset($this->x509IssuerSerial[$index]);
    }

    /**
     * Gets as x509IssuerSerial
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509IssuerSerialType[]
     */
    public function getX509IssuerSerial()
    {
        return $this->x509IssuerSerial;
    }

    /**
     * Sets a new x509IssuerSerial
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509IssuerSerialType[]
     * $x509IssuerSerial
     * @return self
     */
    public function setX509IssuerSerial(array $x509IssuerSerial)
    {
        $this->x509IssuerSerial = $x509IssuerSerial;
        return $this;
    }

    /**
     * Adds as x509SKI
     *
     * @return self
     * @param mixed $x509SKI
     */
    public function addToX509SKI($x509SKI)
    {
        $this->x509SKI[] = $x509SKI;
        return $this;
    }

    /**
     * isset x509SKI
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509SKI($index)
    {
        return isset($this->x509SKI[$index]);
    }

    /**
     * unset x509SKI
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509SKI($index)
    {
        unset($this->x509SKI[$index]);
    }

    /**
     * Gets as x509SKI
     *
     * @return mixed[]
     */
    public function getX509SKI()
    {
        return $this->x509SKI;
    }

    /**
     * Sets a new x509SKI
     *
     * @param mixed $x509SKI
     * @return self
     */
    public function setX509SKI(array $x509SKI)
    {
        $this->x509SKI = $x509SKI;
        return $this;
    }

    /**
     * Adds as x509SubjectName
     *
     * @return self
     * @param string $x509SubjectName
     */
    public function addToX509SubjectName($x509SubjectName)
    {
        $this->x509SubjectName[] = $x509SubjectName;
        return $this;
    }

    /**
     * isset x509SubjectName
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509SubjectName($index)
    {
        return isset($this->x509SubjectName[$index]);
    }

    /**
     * unset x509SubjectName
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509SubjectName($index)
    {
        unset($this->x509SubjectName[$index]);
    }

    /**
     * Gets as x509SubjectName
     *
     * @return string[]
     */
    public function getX509SubjectName()
    {
        return $this->x509SubjectName;
    }

    /**
     * Sets a new x509SubjectName
     *
     * @param string[] $x509SubjectName
     * @return self
     */
    public function setX509SubjectName(array $x509SubjectName)
    {
        $this->x509SubjectName = $x509SubjectName;
        return $this;
    }

    /**
     * Adds as x509Certificate
     *
     * @return self
     * @param mixed $x509Certificate
     */
    public function addToX509Certificate($x509Certificate)
    {
        $this->x509Certificate[] = $x509Certificate;
        return $this;
    }

    /**
     * isset x509Certificate
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509Certificate($index)
    {
        return isset($this->x509Certificate[$index]);
    }

    /**
     * unset x509Certificate
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509Certificate($index)
    {
        unset($this->x509Certificate[$index]);
    }

    /**
     * Gets as x509Certificate
     *
     * @return mixed[]
     */
    public function getX509Certificate()
    {
        return $this->x509Certificate;
    }

    /**
     * Sets a new x509Certificate
     *
     * @param mixed $x509Certificate
     * @return self
     */
    public function setX509Certificate(array $x509Certificate)
    {
        $this->x509Certificate = $x509Certificate;
        return $this;
    }

    /**
     * Adds as x509CRL
     *
     * @return self
     * @param mixed $x509CRL
     */
    public function addToX509CRL($x509CRL)
    {
        $this->x509CRL[] = $x509CRL;
        return $this;
    }

    /**
     * isset x509CRL
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509CRL($index)
    {
        return isset($this->x509CRL[$index]);
    }

    /**
     * unset x509CRL
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509CRL($index)
    {
        unset($this->x509CRL[$index]);
    }

    /**
     * Gets as x509CRL
     *
     * @return mixed[]
     */
    public function getX509CRL()
    {
        return $this->x509CRL;
    }

    /**
     * Sets a new x509CRL
     *
     * @param mixed $x509CRL
     * @return self
     */
    public function setX509CRL(array $x509CRL)
    {
        $this->x509CRL = $x509CRL;
        return $this;
    }

    /**
     * Adds as array
     *
     * @return self
     * @param mixed $array
     */
    public function addToAnyElement($array)
    {
        $this->anyElement[] = $array;
        return $this;
    }

    /**
     * isset anyElement
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetAnyElement($index)
    {
        return isset($this->anyElement[$index]);
    }

    /**
     * unset anyElement
     *
     * @param scalar $index
     * @return void
     */
    public function unsetAnyElement($index)
    {
        unset($this->anyElement[$index]);
    }

    /**
     * Gets as anyElement
     *
     * @return mixed[]
     */
    public function getAnyElement()
    {
        return $this->anyElement;
    }

    /**
     * Sets a new anyElement
     *
     * @param mixed[] $anyElement
     * @return self
     */
    public function setAnyElement(array $anyElement)
    {
        $this->anyElement = $anyElement;
        return $this;
    }


}

