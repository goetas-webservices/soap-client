<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag;

use Zend\Code\Generator\DocBlock\Tag\MethodTag as BaseMethodTag;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;

class MethodTag extends BaseMethodTag
{
    protected $params = [];

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function generateParams()
    {
        $params = array_map(function (ParamTag $paramTag) {
            if ($paramTag instanceof \GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\ParamTag){
                return $paramTag->generateForMethod();
            }
            return
                ((!empty($paramTag->getTypes())) ? $paramTag->getTypesAsString() : '')
                . ((!empty($paramTag->getVariableName())) ? ' $' . $paramTag->getVariableName() : '');
        }, $this->params );


        return implode(', ', $params);
    }

    /**
     * @return string
     */
    public function generate()
    {
        $params = $this->generateParams();
        $output = '@method'
            . (($this->isStatic) ? ' static' : '')
            . ((!empty($this->types)) ? ' ' . $this->getTypesAsString() : '')
            . ((!empty($this->methodName)) ? ' ' . $this->methodName . "($params)" : '')
            . ((!empty($this->description)) ? ' ' . $this->description : '');

        return $output;
    }
}
