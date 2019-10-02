<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Listing_Log
    extends Ess_M2ePro_Model_Resource_Log_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Log', 'id');
    }

    public function getLastActionIdConfigKey()
    {
        return 'listings';
    }

    public function getStatusByActionId($logsActionId)
    {
        $collection = Mage::getModel('M2ePro/Listing_Log')->getCollection();
        $collection->addFieldToFilter('action_id', $logsActionId);
        $collection->addOrder('type');
        $resultType = $collection->getFirstItem()->getData('type');

        if (empty($resultType)) {
            throw new Ess_M2ePro_Model_Exception('Log action ID does not exist.');
        }

        return Mage::helper('M2ePro/Module_Log')->getStatusByResultType($resultType);
    }

    //########################################

    public function updateListingTitle($listingId , $title)
    {
        if ($title == '') {
            return false;
        }

        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('listing_title'=>$title), array('listing_id = ?'=>(int)$listingId)
        );

        return true;
    }

    public function updateProductTitle($productId , $title)
    {
        if ($title == '') {
            return false;
        }

        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('product_title'=>$title), array('product_id = ?'=>(int)$productId)
        );

        return true;
    }

    //########################################
}
