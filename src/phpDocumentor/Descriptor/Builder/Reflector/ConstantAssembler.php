<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @copyright 2010-2014 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Descriptor\Builder\Reflector;

use phpDocumentor\Descriptor\ConstantDescriptor;
use phpDocumentor\Descriptor\Tag\VarDescriptor;
use phpDocumentor\Reflection\ConstantReflector;
use phpDocumentor\Reflection\DocBlock;

/**
 * Assembles a ConstantDescriptor from a ConstantReflector.
 */
class ConstantAssembler extends AssemblerAbstract
{
    /**
     * Creates a Descriptor from the provided data.
     *
     * @param ConstantReflector $data
     *
     * @return ConstantDescriptor
     */
    public function create($data)
    {
        $constantDescriptor = new ConstantDescriptor();
        $constantDescriptor->setName($data->getShortName());
        $constantDescriptor->setValue($data->getValue());
        // Reflection library formulates namespace as global but this is not wanted for phpDocumentor itself
        $constantDescriptor->setNamespace(
            '\\' . (strtolower($data->getNamespace()) == 'global' ? '' :$data->getNamespace())
        );
        $constantDescriptor->setFullyQualifiedStructuralElementName(
            (trim($constantDescriptor->getNamespace(), '\\') ? $constantDescriptor->getNamespace() : '')
            . '\\' . $data->getShortName()
        );

        $this->assembleDocBlock($data->getDocBlock(), $constantDescriptor);

        $constantDescriptor->setLine($data->getLinenumber());

        if ($constantDescriptor->getSummary() === '') {
            $this->extractSummaryAndDescriptionFromVarTag($constantDescriptor);
        }

        return $constantDescriptor;
    }

    /**
     * @param ConstantDescriptor $constantDescriptor
     */
    private function extractSummaryAndDescriptionFromVarTag($constantDescriptor)
    {
        /** @var VarDescriptor $var */
        foreach ($constantDescriptor->getVar() as $var) {
            // check if the first part of the description matches the constant name; an additional character is
            // extracted to see if it is followed by a space.
            $name = substr($var->getDescription(), 0, strlen($constantDescriptor->getName()) + 1);

            if ($name === $constantDescriptor->getName() . ' ') {
                $docBlock = new DocBlock(substr($var->getDescription(), strlen($constantDescriptor->getName()) + 1));
                $constantDescriptor->setSummary($docBlock->getShortDescription());
                $constantDescriptor->setDescription($docBlock->getLongDescription());
                break;
            }
        }
    }
}
