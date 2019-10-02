<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Input
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Listing_Product_Instruction[] */
    protected $_instructions = array();

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    public function getListingProduct()
    {
        return $this->_listingProduct;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Instruction[] $instructions
     * @return $this
     */
    public function setInstructions(array $instructions)
    {
        $this->_instructions = $instructions;
        return $this;
    }

    // ---------------------------------------

    public function getInstructions()
    {
        return $this->_instructions;
    }

    // ---------------------------------------

    public function getUniqueInstructionTypes()
    {
        $types = array();

        foreach ($this->getInstructions() as $instruction) {
            $types[] = $instruction->getType();
        }

        return array_unique($types);
    }

    public function hasInstructionWithType($instructionType)
    {
        return in_array($instructionType, $this->getUniqueInstructionTypes());
    }

    public function hasInstructionWithTypes(array $instructionTypes)
    {
        return array_intersect($this->getUniqueInstructionTypes(), $instructionTypes);
    }

    //########################################
}
