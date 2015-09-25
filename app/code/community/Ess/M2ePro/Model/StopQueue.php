<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_StopQueue extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/StopQueue');
    }

    //####################################

    public function getItemData()
    {
        return $this->getData('item_data');
    }

    public function getDecodedItemData()
    {
        return json_decode($this->getItemData(),true);
    }

    //------------------------------------

    public function getAccountHash()
    {
        return $this->getData('account_hash');
    }

    public function getMarketplaceId()
    {
        return $this->getData('marketplace_id');
    }

    public function getComponentMode()
    {
        return $this->getData('component_mode');
    }

    public function isProcessed()
    {
        return (bool)$this->getData('is_processed');
    }

    //####################################

    public function add(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isStoppable()) {
            return false;
        }

        $itemData = $this->getItemDataByListingProduct($listingProduct);

        if (is_null($itemData)) {
            return false;
        }

        $marketplaceNativeId = $listingProduct->isComponentModeEbay() ?
                                        $listingProduct->getMarketplace()->getNativeId() : NULL;

        $addedData = array(
            'item_data' => json_encode($itemData),
            'account_hash' => $listingProduct->getAccount()->getChildObject()->getServerHash(),
            'marketplace_id' => $marketplaceNativeId,
            'component_mode' => $listingProduct->getComponentMode(),
            'is_processed' => 0
        );

        Mage::getModel('M2ePro/StopQueue')->setData($addedData)->save();

        return true;
    }

    private function getItemDataByListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $connectorClassName = 'Ess_M2ePro_Model_Connector_'.ucfirst($listingProduct->getComponentMode()).'_';
        $connectorClassName .= $listingProduct->isComponentModeEbay() ? 'Item' : 'Product';
        $connectorClassName .= '_Stop_Multiple'.($listingProduct->isComponentModeEbay() ? '' : 'Requester');

        $connectorParams = array(
            'logs_action_id' => 0,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN,
        );

        try {
            $connector = new $connectorClassName($connectorParams, array($listingProduct));
            $itemData = $connector->getRequestDataPackage();
        } catch (Exception $exception) {
            return NULL;
        }

        if (!isset($itemData['data']['items'])) {
            return NULL;
        }

        return array_shift($itemData['data']['items']);
    }

    //####################################
}