<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Search_Settings_ByQuery_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Search_ByQuery_ItemsResponser
{
    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->getObjectByParam('Listing_Product', 'listing_product_id');
    }

    //########################################

    protected function processResponseData($response)
    {
        /** @var Ess_M2ePro_Model_Buy_Search_Settings $settingsSearch */
        $settingsSearch = Mage::getModel('M2ePro/Buy_Search_Settings');
        $settingsSearch->setListingProduct($this->getListingProduct());
        $settingsSearch->setStep($this->params['step']);
        if (!empty($response)) {
            $settingsSearch->setStepData(array(
                'params' => $this->params,
                'result' => $response,
            ));
        }

        $settingsSearch->process();
    }

    //########################################
}