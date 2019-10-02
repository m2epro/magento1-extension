<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Model_Resource_Listing_Product_Instruction extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product_Instruction', 'id');
    }

    //########################################

    public function add(array $instructionsData)
    {
        if (empty($instructionsData)) {
            return;
        }

        $listingsProductsIds = array();

        foreach ($instructionsData as $instructionData) {
            $listingsProductsIds[] = $instructionData['listing_product_id'];
        }

        $listingsProductsCollection = Mage::getResourceModel('M2ePro/Listing_Product_Collection');
        $listingsProductsCollection->addFieldToFilter('id', array('in' => array_unique($listingsProductsIds)));

        foreach ($instructionsData as $index => &$instructionData) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($instructionData['listing_product_id']);
            if ($listingProduct === null) {
                unset($instructionsData[$index]);
                continue;
            }

            $instructionData['component']   = $listingProduct->getComponentMode();
            $instructionData['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $instructionsData);
    }

    public function remove(array $instructionsIds)
    {
        if (empty($instructionsIds)) {
            return;
        }

        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array(
                'id IN (?)' => $instructionsIds,
                'skip_until IS NULL OR ? > skip_until' => Mage::helper('M2ePro')->getCurrentGmtDate()
            )
        );
    }

    //########################################
}
