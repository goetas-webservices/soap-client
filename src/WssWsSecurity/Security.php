<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

class Security
{
    /**
     * (UT 3.1) Password type: plain text.
     */
    const PASSWORD_TYPE_TEXT = 0;

    /**
     * (UT 3.1) Password type: digest.
     */
    const PASSWORD_TYPE_DIGEST = 1;

    /**
     * (UT 3.1) Password.
     *
     * @var string
     */
    protected $password;

    /**
     * (UT 3.1) Password type: text or digest.
     *
     * @var int
     */
    protected $passwordType = self::PASSWORD_TYPE_DIGEST;

    /**
     * (UT 3.1) Username.
     *
     * @var string
     */
    protected $username;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @param int $passwordType
     */
    public function setPassword($password, $passwordType = self::PASSWORD_TYPE_DIGEST)
    {
        $this->password = $password;
        $this->passwordType = $passwordType;
    }

    public function isPasswordDigest()
    {
        return $this->passwordType === self::PASSWORD_TYPE_DIGEST;
    }

    public function isPasswordPlain()
    {
        return $this->passwordType === self::PASSWORD_TYPE_TEXT;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
