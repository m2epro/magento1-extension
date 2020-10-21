<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ByIdentifier_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Search_ByIdentifier_ItemsResponser
{
    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Listing_Product',
            $this->_params['listing_product_id']
        );
    }

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $listingProduct = $this->getListingProduct();

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $actionId = $logModel->getResource()->getNextActionId();

        $logModel->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
            $actionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
            $messageText,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );

        $listingProduct->setData('search_settings_status', null);
        $listingProduct->setData('search_settings_data', null);
        $listingProduct->save();
    }

    //########################################

    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();

        /** @var Ess_M2ePro_Model_Amazon_Search_Settings $settingsSearch */
        $settingsSearch = Mage::getModel('M2ePro/Amazon_Search_Settings');
        $settingsSearch->setListingProduct($this->getListingProduct());
        $settingsSearch->setStep($this->_params['step']);
        if (!empty($responseData)) {
            $settingsSearch->setStepData(
                array(
                    'params' => $this->_params,
                    'result' => $responseData,
                )
            );
        }

        $settingsSearch->process();
    }

    //########################################
}