<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    public function isAllowed()
    {
        if (!parent::isAllowed()) {
            return false;
        }

        $listingProduct = $this->_input->getListingProduct();

        if (!$listingProduct->isListable() || !$listingProduct->isNotListed()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        $searchGeneralId   = $amazonListingProduct->getListingSource()->getSearchGeneralId();
        $searchWorldwideId = $amazonListingProduct->getListingSource()->getSearchWorldwideId();

        if ($variationManager->isVariationProduct()) {
            if ($variationManager->isPhysicalUnit() &&
                !$variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                return false;
            }

            if ($variationManager->isRelationParentType() && $amazonListingProduct->getGeneralId()) {
                return false;
            }

            if ($variationManager->isRelationChildType()) {
                if (!$amazonListingProduct->getGeneralId() && !$amazonListingProduct->isGeneralIdOwner()) {
                    return false;
                }
            }

            if ($variationManager->isIndividualType()) {
                if (!$amazonListingProduct->getGeneralId() &&
                    ($listingProduct->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
                        $listingProduct->getMagentoProduct()->isBundleType() ||
                        $listingProduct->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks())
                ) {
                    return false;
                }
            }

            if ($variationManager->isRelationParentType() &&
                empty($searchGeneralId) &&
                !$amazonListingProduct->isGeneralIdOwner()
            ) {
                return false;
            }
        }

        if (!$amazonListingProduct->getGeneralId() && !$amazonListingProduct->isGeneralIdOwner() &&
            empty($searchGeneralId) && empty($searchWorldwideId)
        ) {
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
                'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isListMode()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        $additionalData = $listingProduct->getAdditionalData();

        if ($amazonSynchronizationTemplate->isListStatusEnabled()) {
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

        if ($amazonSynchronizationTemplate->isListIsInStock()) {
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

        if ($amazonSynchronizationTemplate->isListWhenQtyCalculatedHasValue() &&
            !$variationManager->isRelationParentType()
        ) {
            $result = false;
            $productQty = (int)$amazonListingProduct->getQty(false);
            $minQty = (int)$amazonSynchronizationTemplate->getListWhenQtyCalculatedHasValue();

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

        if ($amazonSynchronizationTemplate->isListAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($amazonSynchronizationTemplate->getListAdvancedRulesFilters());

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
