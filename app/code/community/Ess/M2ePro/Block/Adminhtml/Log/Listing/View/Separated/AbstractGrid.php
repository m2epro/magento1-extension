<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Listing_View_Separated_AbstractGrid extends
    Ess_M2ePro_Block_Adminhtml_Log_Listing_View_AbstractGrid
{
    //########################################

    protected function getViewMode()
    {
        return Ess_M2ePro_Block_Adminhtml_Log_Listing_View_ModeSwitcher::VIEW_MODE_SEPARATED;
    }

    // ---------------------------------------

    protected function _prepareCollection()
    {
        /** @var  Ess_M2ePro_Model_Resource_Listing_Log_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Log')->getCollection();

        $this->applyFilters($collection);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################
}
