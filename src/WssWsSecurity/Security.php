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
     * (SMS 10) Add security timestamp.
     *
     * @var boolean
     */
    protected $addTimestamp = true;

    /**
     * (UT 3.1) Username.
     *
     * @var \DateTime
     */
    protected $timestamp;

    /**
     * (SMS 10) Security timestamp expires time in seconds.
     *
     * @var int
     */
    protected $expires = 300;

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp)
    {
        $this->timestamp = $timestamp;
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

    /**
     * @return int
     */
    public function getPasswordType()
    {
        return $this->passwordType;
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

    /**
     * @return bool
     */
    public function isAddTimestamp()
    {
        return $this->addTimestamp;
    }

    /**
     * @param bool $addTimestamp
     */
    public function setAddTimestamp($addTimestamp)
    {
        $this->addTimestamp = $addTimestamp;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }


}
