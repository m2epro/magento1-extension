<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Account_Repricing
    extends Ess_M2ePro_Model_Resource_Component_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Account_Repricing', 'account_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function setProcessRequired($newData, $oldData, $listingsProducts)
    {
        if (empty($listingsProducts)) {
            return;
        }

        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = $listingProduct['id'];
        }

        if (!$this->isDifferent($newData, $oldData)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->markAsProcessRequired(
            $listingsProductsIds
        );
    }

    // ---------------------------------------

    public function isDifferent($newData, $oldData)
    {
        $ignoreFields = array(
            $this->getIdFieldName(),
            'account_id', 'email', 'token',
            'total_products', 'create_date', 'update_date',
        );

        foreach ($ignoreFields as $ignoreField) {
            unset($newData[$ignoreField], $oldData[$ignoreField]);
        }

        $diff = array_diff_assoc($newData, $oldData);
        return !empty($diff);
    }

    //########################################
}
