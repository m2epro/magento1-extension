<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Request
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request
{
    //########################################

    protected function beforeBuildDataEvent()
    {
        parent::beforeBuildDataEvent();

        $additionalData = $this->getListingProduct()->getAdditionalData();

        unset($additionalData['synch_template_list_rules_note']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->save();
    }

    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku'  => $this->getWalmartListingProduct()->getSku(),
                'wpid' => $this->getWalmartListingProduct()->getWpid(),
            ),
            $this->getQtyData(),
            $this->getLagTimeData(),
            $this->getPriceData(),
            $this->getPromotionsData()
        );

        return $data;
    }

    //########################################
}
