<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag;

use Zend\Code\Generator\DocBlock\Tag\ParamTag as ParamTagTag;

class ParamTag extends ParamTagTag
{
    protected $default;

    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '@param'
            . ((!empty($this->types)) ? ' ' . $this->getTypesAsString() : '')
            . ((!empty($this->variableName)) ? ' $' . $this->variableName : '')
            . ((!empty($this->default)) ? ' = ' . $this->default : '')
            . ((!empty($this->description)) ? ' ' . $this->description : '');

        return $output;
    }

    public function generateForMethod()
    {
        return ((!empty($this->types)) ? $this->getTypesAsString() : '')
        . ((!empty($this->variableName)) ? ' $' . $this->variableName : '')
        . ((!empty($this->default)) ? ' = ' . $this->default : '');
    }
}
