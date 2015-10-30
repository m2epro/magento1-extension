<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_Sku_Existence
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    private $existenceResult = array();

    //########################################

    /**
     * @param array $result
     * @return $this
     */
    public function setExistenceResult(array $result)
    {
        $this->existenceResult = $result;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (empty($this->existenceResult['general_id'])) {
            return true;
        }

        if ($this->getBuyListingProduct()->getGeneralId() &&
            $this->getBuyListingProduct()->getGeneralId() != $this->existenceResult['general_id']
        ) {
            // M2ePro_TRANSLATIONS
            // Product with the same Reference ID is found on Rakuten.com but the Rakuten.com SKU is different in Magento and on Rakuten.com.
            $this->addMessage(
                'Product with the same Reference ID is found on Rakuten.com but the Rakuten.com SKU
                 is different in Magento and on Rakuten.com.'
            );
            return false;
        }

        $this->link($this->existenceResult['general_id'], $this->data['sku']);

        return false;
    }

    //########################################

    private function link($generalId, $sku)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Buy_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());
        $linkingObject->setGeneralId($generalId);
        $linkingObject->setSku($sku);

        $linkingObject->link();

        // M2ePro_TRANSLATIONS
        // Product was successfully found in Rakuten.com Inventory by Reference ID and linked to your Magento Product.
        $this->addMessage(
            'Product was successfully found in Rakuten.com Inventory
             by Reference ID and linked to your Magento Product.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
        );
    }

    //########################################
}