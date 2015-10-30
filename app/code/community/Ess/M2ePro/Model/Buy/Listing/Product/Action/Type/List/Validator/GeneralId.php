<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_GeneralId
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    private $cachedData = array();

    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        $generalId = $this->getBuyListingProduct()->getGeneralId();
        if (empty($generalId)) {
            $generalId = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

            if (!empty($generalId)) {
                $this->data['general_id_mode'] = $this->getBuyListing()->getGeneralIdMode();
            }
        }

        // M2ePro_TRANSLATIONS
        // Product cannot be Listed because Rakuten.com SKU is not specified.
        if (empty($generalId)) {
            $this->addMessage('Product cannot be Listed because Rakuten.com SKU is not specified.');
            return false;
        }

        // M2ePro_TRANSLATIONS
        // The value "%general_id%" provided for Rakuten.com SKU in Listing Search Settings is invalid. Please set the correct value and try again.
        if (!Mage::helper('M2ePro/Component_Buy')->isGeneralId($generalId)) {
            $this->addMessage(Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'The value "%general_id%" provided for Rakuten.com SKU in Listing Search Settings is invalid.
                 Please set the correct value and try again.',
                array('!general_id' => $generalId)
            ));

            return false;
        }

        $buyData = $this->getDataFromBuy($generalId);

        if (empty($buyData)) {
            // M2ePro_TRANSLATIONS
            // Rakuten.com SKU %general_id% provided in Listing Search Settings is not found on Buy. Please set the correct value and try again. Note: Due to Buy API restrictions M2E Pro might not see all the existing Products on Buy.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Rakuten.com SKU %general_id% provided in Listing Search Settings
                     is not found on Rakuten.com.
                     Please set the correct value and try again.
                     Note: Due to Rakuten.com API restrictions M2E Pro
                     might not see all the existing Products on Rakuten.com.',
                    array('!general_id' => $generalId)
                )
            );

            return false;
        }

        if (count($buyData) > 1) {
            // M2ePro_TRANSLATIONS
            // There is more than one Product found on Buy using Search by Rakuten.com SKU %general_id%. First, you should select certain one using manual search.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'There is more than one Product found on Rakuten.com using Search
                     by Rakuten.com SKU %general_id%.
                     First, you should select certain one using manual search.',
                    array('!general_id' => $generalId)
                )
            );

            return false;
        }

        $this->data['general_id'] = $generalId;

        return true;
    }

    //########################################

    private function getDataFromBuy($identifier)
    {
        if (isset($this->cachedData['buy_data'][$identifier])) {
            return $this->cachedData['buy_data'][$identifier];
        }

        $idType = (Mage::helper('M2ePro/Component_Buy')->isGeneralId($identifier) ? 'SKU' : false);

        if (empty($idType)) {
            return array();
        }

        $params = array(
            'query' => $identifier,
        );

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');

        $connectorObj = $dispatcherObject->getVirtualConnector('product', 'search', 'byQuery',
            $params, null,
            $this->getListingProduct()->getListing()->getAccount());

        $result = $dispatcherObject->process($connectorObj);

        return $this->cachedData['buy_data'][$identifier] = $result['items'];
    }

    //########################################
}