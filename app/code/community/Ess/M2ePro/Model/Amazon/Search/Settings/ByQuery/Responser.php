<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ByQuery_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Search_ByQuery_ItemsResponser
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

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $logModel->addProductMessage(
            $this->getListingProduct()->getListingId(),
            $this->getListingProduct()->getProductId(),
            $this->getListingProduct()->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
            $messageText,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        $this->getListingProduct()->setData('search_settings_status', null);
        $this->getListingProduct()->setData('search_settings_data', null);
        $this->getListingProduct()->save();
    }

    //########################################

    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();

        /** @var Ess_M2ePro_Model_Amazon_Search_Settings $settingsSearch */
        $settingsSearch = Mage::getModel('M2ePro/Amazon_Search_Settings');
        $settingsSearch->setListingProduct($this->getListingProduct());
        $settingsSearch->setStep($this->params['step']);
        if (!empty($responseData)) {
            $settingsSearch->setStepData(array(
                'params' => $this->params,
                'result' => $responseData,
            ));
        }

        $settingsSearch->process();
    }

    //########################################
}