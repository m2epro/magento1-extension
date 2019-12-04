<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Log_View_Separated_Grid
    extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Listing_Log')->getCollection();

        $this->applyFilters($collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################
}
