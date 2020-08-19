<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Response getResponseObject()
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_List_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $parts = array(
            sprintf('Product was Listed with QTY %d', $this->_listingProduct->getChildObject()->getOnlineQty())
        );

        if ($regularPrice = $this->_listingProduct->getChildObject()->getOnlineRegularPrice()) {
            $parts[] = sprintf('Regular Price %s', $currency->toCurrency($regularPrice));
        }

        if ($businessPrice = $this->_listingProduct->getChildObject()->getOnlineBusinessPrice()) {
            $parts[] = sprintf('Business Price %s', $currency->toCurrency($businessPrice));
        }

        return implode(', ', $parts);
    }

    //########################################

    protected function processSuccess(array $params = array())
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_listingProduct->getChildObject();

        if ($amazonListingProduct->getVariationManager()->isRelationMode() &&
            !$this->getRequestDataObject()->hasProductId() &&
            empty($params['general_id'])
        ) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Unexpected error. The ASIN/ISBN for Parent or Child Product was not returned from Amazon.
                 Operation cannot be finished correctly.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
            return;
        }

        parent::processSuccess($params);
    }

    protected function getSuccessfulParams()
    {
        $responseData = $this->getPreparedResponseData();

        if (empty($responseData['asins'])) {
            return array();
        }

        return array('general_id' => $responseData['asins']);
    }

    //########################################
}
