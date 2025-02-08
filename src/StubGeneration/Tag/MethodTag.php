<?php

declare(strict_types=1);

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 */

namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag;

use GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\ParamTag as SoapParamTag;
use Laminas\Code\Generator\DocBlock\Tag\MethodTag as BaseMethodTag;
use Laminas\Code\Generator\DocBlock\Tag\ParamTag;

class MethodTag extends BaseMethodTag
{
    /**
     * @var array
     */
    protected $params = [];

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function generateParams(): string
    {
        $params = array_map(static function (ParamTag $paramTag) {
            if ($paramTag instanceof SoapParamTag) {
                return $paramTag->generateForMethod();
            }

            return (!empty($paramTag->getTypes()) ? $paramTag->getTypesAsString() : '')
                . (!empty($paramTag->getVariableName()) ? ' $' . $paramTag->getVariableName() : '');
        }, $this->params);

        return implode(', ', $params);
    }

    public function generate(): string
    {
        $params = $this->generateParams();

        return '@method'
            . ($this->isStatic ? ' static' : '')
            . (!empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (!empty($this->methodName) ? ' ' . $this->methodName . '(' . $params . ')' : '')
            . (!empty($this->description) ? ' ' . $this->description : '');
    }
}
