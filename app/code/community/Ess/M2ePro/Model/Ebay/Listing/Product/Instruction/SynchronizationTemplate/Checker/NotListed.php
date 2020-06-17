<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    public function isAllowed()
    {
        $listingProduct = $this->_input->getListingProduct();

        if (!$listingProduct->isListable() || !$listingProduct->isNotListed()) {
            return false;
        }

        return true;
    }

    //########################################

    public function process(array $params = array())
    {
        if (!$this->isMeetListRequirements()) {
            if ($this->_input->getScheduledAction() && !$this->_input->getScheduledAction()->isForce()) {
                $this->getScheduledActionManager()->deleteAction($this->_input->getScheduledAction());
            }

            return;
        }

        if ($this->_input->getScheduledAction() && $this->_input->getScheduledAction()->isActionTypeList()) {
            return;
        }

        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode(array('params' => $params)),
            )
        );

        if ($scheduledAction->getId()) {
            $this->getScheduledActionManager()->updateAction($scheduledAction);
        } else {
            $this->getScheduledActionManager()->addAction($scheduledAction);
        }
    }

    //########################################

    public function isMeetListRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isListMode()) {
            return false;
        }

        $additionalData = $listingProduct->getAdditionalData();

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($ebaySynchronizationTemplate->isListStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed as it has Disabled Status in Magento.
                    The Product Status condition in the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not Listed as this Product Variation has Disabled Status in Magento.
                        The Product Status condition in the List Rules was not met.'
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isListIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed as it is Out of Stock in Magento.
                    The Stock Availability condition in the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not Listed as this Product Variation is Out of Stock in Magento.
                        The Stock Availability condition in the List Rules was not met.'
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {
            $result = false;
            $productQty = (int)$ebayListingProduct->getQty();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValue();

            $note = '';

            if ($productQty >= $minQty) {
                $result = true;
            } else {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed as its Quantity is %product_qty% in Magento.
                    The Calculated Quantity condition in the List Rules was not met.',
                    array(
                        '!product_qty' => $productQty,
                    )
                );
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($ebaySynchronizationTemplate->isListAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($ebaySynchronizationTemplate->getListAdvancedRulesFilters());

            if (!$ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed. Advanced Conditions in the List Rules were not met.'
                );

                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            }
        }

        return true;
    }

    //########################################
}
