<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Nonce;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Password;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Security as SecextSecurity;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\UsernameToken;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Timestamp;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\XmlSerializationVisitor;

class WssSecurityHeaderHandler implements SubscribingHandlerInterface
{
    const WSS_UTP = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.000\Z';

    protected $nonce;

    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => Security::class,
                'method' => 'serializeHeader'
            )
        );
    }

    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

    public function serializeHeader(XmlSerializationVisitor $visitor, Security $data, array $type, SerializationContext $context)
    {
        $dt = $data->getTimestamp() ?: new \DateTime('now', new \DateTimeZone('UTC'));
        $security = new SecextSecurity();

        if ($data->isAddTimestamp() || $data->getExpires() > 0) {
            $security->addToAnyElement($this->handleTimestamp($data, $dt));
        }
        if (null !== $data->getUsername()) {
            $security->addToAnyElement($this->handleUsername($data, $dt));
        }
        $context->getNavigator()->accept($security, null, $context);
    }

    /**
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security $data
     * @param \DateTime $dt
     * @return UsernameToken
     */
    private function handleUsername(Security $data, \DateTime $dt)
    {
        $usernameToken = new UsernameToken();
        $usernameToken->setUsername(new AttributedStringType($data->getUsername()));

        if (null !== $data->getPassword()) {

            if (Security::PASSWORD_TYPE_DIGEST === $data->getPasswordType()) {
                $nonce = $this->nonce ?: mt_rand();
                $password = base64_encode(sha1($nonce . $dt->format(self::DATETIME_FORMAT) . $data->getPassword(), true));
                $passwordType = self::WSS_UTP . '#PasswordDigest';

                $usernameToken->addToAnyElement(new Nonce(base64_encode($nonce)));
                $usernameToken->addToAnyElement(new Created($dt->format(self::DATETIME_FORMAT)));

            } else {
                $password = $data->getPassword();
                $passwordType = self::WSS_UTP . '#PasswordText';
            }

            $passwordItem = new Password($password);
            $passwordItem->setType($passwordType);

            $usernameToken->addToAnyElement($passwordItem);
        }
        return $usernameToken;
    }

    /**
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security $data
     * @param \DateTime $dt
     * @return Timestamp
     */
    private function handleTimestamp(Security $data, \DateTime $dt)
    {
        $timestamp = new Timestamp();

        $timestamp->setCreated(new Created($dt->format(self::DATETIME_FORMAT)));

        if ($data->getExpires() > 0) {
            $expireDate = clone $dt;
            $expireDate->modify('+' . $data->getExpires() . ' seconds');

            $timestamp->setExpires(new Expires($expireDate->format(self::DATETIME_FORMAT)));
        }
        return $timestamp;
    }
}
