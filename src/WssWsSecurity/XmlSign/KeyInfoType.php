<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing KeyInfoType
 *
 *
 * XSD Type: KeyInfoType
 */
class KeyInfoType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property string[] $keyName
     */
    private $keyName = 'array()';

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyValue[]
     * $keyValue
     */
    private $keyValue = 'array()';

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\RetrievalMethod[]
     * $retrievalMethod
     */
    private $retrievalMethod = 'array()';

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509Data[]
     * $x509Data
     */
    private $x509Data = 'array()';

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\PGPData[]
     * $pGPData
     */
    private $pGPData = 'array()';

    /**
     * @property mixed[] $sPKIData
     */
    private $sPKIData = null;

    /**
     * @property string[] $mgmtData
     */
    private $mgmtData = 'array()';

    /**
     * @property mixed[] $anyElement
     */
    private $anyElement = array(
        
    );

    /**
     * Gets as id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a new id
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Adds as keyName
     *
     * @return self
     * @param string $keyName
     */
    public function addToKeyName($keyName)
    {
        $this->keyName[] = $keyName;
        return $this;
    }

    /**
     * isset keyName
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetKeyName($index)
    {
        return isset($this->keyName[$index]);
    }

    /**
     * unset keyName
     *
     * @param scalar $index
     * @return void
     */
    public function unsetKeyName($index)
    {
        unset($this->keyName[$index]);
    }

    /**
     * Gets as keyName
     *
     * @return string[]
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Sets a new keyName
     *
     * @param string $keyName
     * @return self
     */
    public function setKeyName(array $keyName)
    {
        $this->keyName = $keyName;
        return $this;
    }

    /**
     * Adds as keyValue
     *
     * @return self
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyValue
     * $keyValue
     */
    public function addToKeyValue(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyValue $keyValue)
    {
        $this->keyValue[] = $keyValue;
        return $this;
    }

    /**
     * isset keyValue
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetKeyValue($index)
    {
        return isset($this->keyValue[$index]);
    }

    /**
     * unset keyValue
     *
     * @param scalar $index
     * @return void
     */
    public function unsetKeyValue($index)
    {
        unset($this->keyValue[$index]);
    }

    /**
     * Gets as keyValue
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyValue[]
     */
    public function getKeyValue()
    {
        return $this->keyValue;
    }

    /**
     * Sets a new keyValue
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyValue[]
     * $keyValue
     * @return self
     */
    public function setKeyValue(array $keyValue)
    {
        $this->keyValue = $keyValue;
        return $this;
    }

    /**
     * Adds as retrievalMethod
     *
     * @return self
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\RetrievalMethod
     * $retrievalMethod
     */
    public function addToRetrievalMethod(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\RetrievalMethod $retrievalMethod)
    {
        $this->retrievalMethod[] = $retrievalMethod;
        return $this;
    }

    /**
     * isset retrievalMethod
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetRetrievalMethod($index)
    {
        return isset($this->retrievalMethod[$index]);
    }

    /**
     * unset retrievalMethod
     *
     * @param scalar $index
     * @return void
     */
    public function unsetRetrievalMethod($index)
    {
        unset($this->retrievalMethod[$index]);
    }

    /**
     * Gets as retrievalMethod
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\RetrievalMethod[]
     */
    public function getRetrievalMethod()
    {
        return $this->retrievalMethod;
    }

    /**
     * Sets a new retrievalMethod
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\RetrievalMethod[]
     * $retrievalMethod
     * @return self
     */
    public function setRetrievalMethod(array $retrievalMethod)
    {
        $this->retrievalMethod = $retrievalMethod;
        return $this;
    }

    /**
     * Adds as x509Data
     *
     * @return self
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509Data
     * $x509Data
     */
    public function addToX509Data(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509Data $x509Data)
    {
        $this->x509Data[] = $x509Data;
        return $this;
    }

    /**
     * isset x509Data
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetX509Data($index)
    {
        return isset($this->x509Data[$index]);
    }

    /**
     * unset x509Data
     *
     * @param scalar $index
     * @return void
     */
    public function unsetX509Data($index)
    {
        unset($this->x509Data[$index]);
    }

    /**
     * Gets as x509Data
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509Data[]
     */
    public function getX509Data()
    {
        return $this->x509Data;
    }

    /**
     * Sets a new x509Data
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\X509Data[]
     * $x509Data
     * @return self
     */
    public function setX509Data(array $x509Data)
    {
        $this->x509Data = $x509Data;
        return $this;
    }

    /**
     * Adds as pGPData
     *
     * @return self
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\PGPData
     * $pGPData
     */
    public function addToPGPData(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\PGPData $pGPData)
    {
        $this->pGPData[] = $pGPData;
        return $this;
    }

    /**
     * isset pGPData
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetPGPData($index)
    {
        return isset($this->pGPData[$index]);
    }

    /**
     * unset pGPData
     *
     * @param scalar $index
     * @return void
     */
    public function unsetPGPData($index)
    {
        unset($this->pGPData[$index]);
    }

    /**
     * Gets as pGPData
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\PGPData[]
     */
    public function getPGPData()
    {
        return $this->pGPData;
    }

    /**
     * Sets a new pGPData
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\PGPData[]
     * $pGPData
     * @return self
     */
    public function setPGPData(array $pGPData)
    {
        $this->pGPData = $pGPData;
        return $this;
    }

    /**
     * Adds as sPKISexp
     *
     * @return self
     * @param mixed $sPKISexp
     */
    public function addToSPKIData($sPKISexp)
    {
        $this->sPKIData[] = $sPKISexp;
        return $this;
    }

    /**
     * isset sPKIData
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetSPKIData($index)
    {
        return isset($this->sPKIData[$index]);
    }

    /**
     * unset sPKIData
     *
     * @param scalar $index
     * @return void
     */
    public function unsetSPKIData($index)
    {
        unset($this->sPKIData[$index]);
    }

    /**
     * Gets as sPKIData
     *
     * @return mixed[]
     */
    public function getSPKIData()
    {
        return $this->sPKIData;
    }

    /**
     * Sets a new sPKIData
     *
     * @param mixed $sPKIData
     * @return self
     */
    public function setSPKIData(array $sPKIData)
    {
        $this->sPKIData = $sPKIData;
        return $this;
    }

    /**
     * Adds as mgmtData
     *
     * @return self
     * @param string $mgmtData
     */
    public function addToMgmtData($mgmtData)
    {
        $this->mgmtData[] = $mgmtData;
        return $this;
    }

    /**
     * isset mgmtData
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetMgmtData($index)
    {
        return isset($this->mgmtData[$index]);
    }

    /**
     * unset mgmtData
     *
     * @param scalar $index
     * @return void
     */
    public function unsetMgmtData($index)
    {
        unset($this->mgmtData[$index]);
    }

    /**
     * Gets as mgmtData
     *
     * @return string[]
     */
    public function getMgmtData()
    {
        return $this->mgmtData;
    }

    /**
     * Sets a new mgmtData
     *
     * @param string $mgmtData
     * @return self
     */
    public function setMgmtData(array $mgmtData)
    {
        $this->mgmtData = $mgmtData;
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

