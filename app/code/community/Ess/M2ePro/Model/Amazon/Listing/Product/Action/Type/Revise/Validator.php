<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty as QtyBuilder;

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Validator
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProduct()) {
            return false;
        }

        $params = $this->getParams();

        if (!empty($params['switch_to']) && !$this->getConfigurator()->isQtyAllowed()) {
            $this->addMessage('Fulfillment mode can not be switched if QTY feed is not allowed.');
            return false;
        }

        if ($this->getConfigurator()->isQtyAllowed()) {
            if ($this->getAmazonListingProduct()->isAfnChannel()) {
                if (empty($params['switch_to'])) {
                    $this->getConfigurator()->disallowQty();

                    $this->addMessage(
                        'This Product is an FBA Item, so it’s Quantity updating will change it to MFN. Thus QTY feed,
                        Production Time and Restock Date Values will not be updated. Inventory management for FBA
                        Items is currently unavailable in M2E Pro. However, you can do that directly in your Amazon
                        Seller Central.',
                        Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
                    );
                } else {
                    if ($params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_AFN) {
                        $this->addMessage('You cannot switch Fulfillment because it is applied now.');
                        return false;
                    }
                }
            } else {
                if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_MFN) {
                    $this->addMessage('You cannot switch Fulfillment because it is applied now.');
                    return false;
                }
            }
        }

        if ($this->getAmazonListingProduct()->isAfnChannel() &&
            $this->getAmazonListingProduct()->isExistShippingTemplate()) {
            $this->addMessage(
                'The Shipping Settings will not be sent for this Product because it is an FBA Item.
                Amazon will handle the delivery of the Order.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->getAmazonListingProduct()->isAfnChannel()
            && ($this->isChangerUser() && !$this->getListingProduct()->isBlocked())
            && (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isRevisable())
        ) {
            $this->addMessage('Item is not Listed or not available');
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validateRegularPrice() || !$this->validateBusinessPrice()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function validateParentListingProduct()
    {
        if ((!$this->getConfigurator()->isDetailsAllowed() && !$this->getConfigurator()->isImagesAllowed()) ||
            !$this->getAmazonListingProduct()->isExistDescriptionTemplate()
        ) {
            $this->addMessage('There was no need for this action. It was skipped.');
            return false;
        }

        return true;
    }

    //########################################
}
