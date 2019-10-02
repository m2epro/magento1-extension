<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Revise_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        return $this->getResponseObject()->getSuccessfulMessage();
    }

    //########################################

    protected function processCompleted(array $data = array(), array $params = array())
    {
        if (!empty($data['already_stop'])) {
            $this->getResponseObject()->processAlreadyStopped($data, $params);

            // M2ePro_TRANSLATIONS
            // Item was already Stopped on eBay
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item was already Stopped on eBay',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->_listingProduct, $message
            );

            return;
        }

        parent::processCompleted($data, $params);
    }

    public function eventAfterExecuting()
    {
        $responseMessages = $this->getResponse()->getMessages()->getEntities();

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH &&
            (!$this->getConfigurator()->isExcludingMode()) &&
            $this->isNewRequiredSpecificNeeded($responseMessages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__(
                    'eBay Category assigned to this Item requires the Product Identifier to be specified
                (UPC, EAN, ISBN, etc.). The related data will be automatically submitted to the Channel based
                on eBay Catalog Identifiers settings in the Description Policy.'
                ),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

            $this->processAdditionalAction($this->getActionType(), $configurator);
        }

        $additionalData = $this->_listingProduct->getAdditionalData();

        if ($this->isVariationErrorAppeared($responseMessages) &&
            $this->getRequestDataObject()->hasVariations() &&
            !isset($additionalData['is_variation_mpn_filled'])
        ) {
            $this->tryToResolveVariationMpnErrors();
        }

        parent::eventAfterExecuting();

        if ($this->_isSuccess) {
            return;
        }

        $additionalData = $this->_listingProduct->getAdditionalData();
        $additionalData['need_full_synchronization_template_recheck'] = true;
        $this->_listingProduct->setSettings('additional_data', $additionalData)->save();
    }

    //########################################
}