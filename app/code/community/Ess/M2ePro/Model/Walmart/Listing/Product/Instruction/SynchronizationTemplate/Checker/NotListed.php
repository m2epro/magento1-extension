<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    public function isAllowed()
    {
        $listingProduct = $this->_input->getListingProduct();

        if (!$listingProduct->isListable() || !$listingProduct->isNotListed()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {
            if ($variationManager->isPhysicalUnit() &&
                !$variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                return false;
            }

            if ($variationManager->isRelationParentType()) {
                return false;
            }
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
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
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

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();

        if (!$walmartSynchronizationTemplate->isListMode()) {
            return false;
        }

        if (!$walmartListingProduct->isExistCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        $additionalData = $listingProduct->getAdditionalData();

        if ($walmartSynchronizationTemplate->isListStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed as it has Disabled Status in Magento.
                    The Product Status condition in the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
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

        if ($walmartSynchronizationTemplate->isListIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not Listed as it is Out of Stock in Magento.
                    The Stock Availability condition in the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
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

        if ($walmartSynchronizationTemplate->isListWhenQtyMagentoHasValue()) {
            $result = false;

            if ($variationManager->isRelationParentType()) {
                $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);
            } else {
                $productQty = (int)$walmartListingProduct->getQty(true);
            }

            $typeQty = (int)$walmartSynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$walmartSynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$walmartSynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not Listed as its Quantity is %product_qty% in Magento.
                        The Magento Quantity condition in the List Rules was not met.',
                        array(
                            '!product_qty' => $productQty,
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not Listed as its Quantity is %product_qty% in Magento.
                        The Magento Quantity condition in the List Rules was not met.',
                        array(
                            '!product_qty' => $productQty,
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not Listed as its Quantity is %product_qty% in Magento.
                        The Magento Quantity condition in the List Rules was not met.',
                        array(
                            '!product_qty' => $productQty,
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($walmartSynchronizationTemplate->isListWhenQtyCalculatedHasValue() &&
            !$variationManager->isRelationParentType()
        ) {
            $result = false;
            $productQty = (int)$walmartListingProduct->getQty(false);

            $typeQty = (int)$walmartSynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$walmartSynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$walmartSynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_LESS) {
                if ($productQty <= $minQty) {
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
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_MORE) {
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
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
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
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($walmartSynchronizationTemplate->isListAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Walmart_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($walmartSynchronizationTemplate->getListAdvancedRulesFilters());

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
