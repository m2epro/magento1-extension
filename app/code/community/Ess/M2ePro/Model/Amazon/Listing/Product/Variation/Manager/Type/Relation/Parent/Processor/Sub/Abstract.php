<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor $processor  */
    private $processor = null;

    //########################################

    public function getProcessor()
    {
        return $this->processor;
    }

    public function setProcessor($processor)
    {
        $this->processor = $processor;
        return $this;
    }

    //########################################

    public function process()
    {
        $this->validate();

        $this->check();
        $this->execute();
    }

    //########################################

    protected function validate()
    {
        if (is_null($this->getProcessor())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Processor was not set.');
        }
    }

    // ---------------------------------------

    abstract protected function check();

    abstract protected function execute();

    //########################################
}