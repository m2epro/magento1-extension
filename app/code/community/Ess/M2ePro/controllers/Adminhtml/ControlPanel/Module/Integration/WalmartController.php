<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Module_Integration_WalmartController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @title "Reset 3rd Party"
     * @description "Clear all 3rd party items for all Accounts"
     */
    public function resetOtherListingsAction()
    {
        $listingOther = Mage::getModel('M2ePro/Listing_Other');
        $walmartListingOther = Mage::getModel('M2ePro/Walmart_Listing_Other');

        $stmt = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other')->getSelect()->query();

        $SKUs = array();
        foreach ($stmt as $row) {
            $listingOther->setData($row);
            $walmartListingOther->setData($row);

            $listingOther->setChildObject($walmartListingOther);
            $walmartListingOther->setParentObject($listingOther);
            $SKUs[] = $walmartListingOther->getSku();

            $listingOther->deleteInstance();
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_walmart_item');
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        foreach (array_chunk($SKUs, 1000) as $chunkSKUs) {
            $writeConnection->delete($tableName, array('sku IN (?)' => $chunkSKUs));
        }

        $this->_getSession()->addSuccess('Successfully removed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageModuleTabUrl());
    }

    //########################################
}
