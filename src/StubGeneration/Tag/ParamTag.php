<?php

declare(strict_types=1);

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 */

namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag;

use Zend\Code\Generator\DocBlock\Tag\ParamTag as ParamTagTag;

class ParamTag extends ParamTagTag
{
    /**
     * @var string
     */
    protected $default;

    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    public function generate(): string
    {
        return '@param'
            . (!empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (!empty($this->variableName) ? ' $' . $this->variableName : '')
            . (!empty($this->default) ? ' = ' . $this->default : '')
            . (!empty($this->description) ? ' ' . $this->description : '');
    }

    public function generateForMethod(): string
    {
        return (!empty($this->types) ? $this->getTypesAsString() : '')
            . (!empty($this->variableName) ? ' $' . $this->variableName : '')
            . (!empty($this->default) ? ' = ' . $this->default : '');
    }
}
