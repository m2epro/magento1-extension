<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Attributes
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        if (!$this->getProcessor()->getTypeModel()->isActualRealProductAttributes()) {
            $this->getProcessor()->getTypeModel()->resetProductAttributes(false);
        }

        if (!$this->getProcessor()->getTypeModel()->isActualVirtualProductAttributes()) {
            $this->getProcessor()->getTypeModel()->resetProductAttributes(false);
        }

        if (!$this->getProcessor()->getTypeModel()->isActualVirtualChannelAttributes()) {
            $this->getProcessor()->getTypeModel()->resetProductAttributes(false);
        }

        if (count($this->getProcessor()->getTypeModel()->getRealChannelAttributes()) ==
            count($this->getProcessor()->getTypeModel()->getRealProductAttributes())
        ) {
            $this->getProcessor()->getTypeModel()->setVirtualProductAttributes(array(), false);
            $this->getProcessor()->getTypeModel()->setVirtualChannelAttributes(array(), false);
        }
    }

    protected function execute()
    {

    }

    //########################################
}