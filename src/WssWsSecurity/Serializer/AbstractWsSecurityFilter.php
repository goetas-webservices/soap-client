<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer;

use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\SecurityKeyPair;

abstract class AbstractWsSecurityFilter
{
    /**
     * Web Services Security Utility namespace.
     */
    const NS_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

    /**
     * Web Services Security Extension namespace.
     */
    const NS_WSS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * Service SecurityKeyPair.
     *
     * @var SecurityKeyPair
     */
    protected $serviceSecurityKey;

    /**
     * User SecurityKeyPair.
     *
     * @var SecurityKeyPair
     */
    protected $userSecurityKey;

    /**
     * Set service security key.
     *
     * @param SecurityKeyPair $serviceSecurityKey Service security key
     *
     * @return void
     */
    public function setServiceSecurityKeyObject(SecurityKeyPair $serviceSecurityKey = null)
    {
        $this->serviceSecurityKey = $serviceSecurityKey;
    }

    /**
     * Set user security key.
     *
     * @param SecurityKeyPair $userSecurityKey User security key
     *
     * @return void
     */
    public function setUserSecurityKeyObject(SecurityKeyPair $userSecurityKey = null)
    {
        $this->userSecurityKey = $userSecurityKey;
    }
}
