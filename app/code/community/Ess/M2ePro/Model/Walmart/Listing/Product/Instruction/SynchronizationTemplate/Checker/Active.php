<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ProductChangeProcessor;

class Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getStopInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Walmart_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            Ess_M2ePro_Model_Walmart_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            Ess_M2ePro_Model_Walmart_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    //########################################

    public function isAllowed()
    {
        if (!parent::isAllowed()) {
            return false;
        }

        if (!$this->_input->hasInstructionWithTypes($this->getStopInstructionTypes()) &&
            !$this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())
        ) {
            return false;
        }

        $listingProduct = $this->_input->getListingProduct();

        if (!$listingProduct->isStoppable() && !$listingProduct->isRevisable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        if (!$walmartListingProduct->isExistCategoryTemplate()) {
            return false;
        }

        if ($scheduledAction = $this->_input->getScheduledAction()) {
            if ($scheduledAction->isActionTypeDelete() && $scheduledAction->isForce()) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function process(array $params = array())
    {
        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        $variationManager = $this->_input->getListingProduct()->getChildObject()->getVariationManager();

        if ($this->_input->hasInstructionWithTypes($this->getStopInstructionTypes())) {
            if (!$this->isMeetStopRequirements()) {
                if ($scheduledAction->isActionTypeStop() && !$scheduledAction->isForce()) {
                    $this->getScheduledActionManager()->deleteAction($scheduledAction);
                    $scheduledAction->unsetData();
                }
            } else {
                if ($scheduledAction->isActionTypeRevise()) {
                    $this->setPropertiesForRecheck($this->getPropertiesDataFromInputScheduledAction());
                }

                $scheduledAction->addData(
                    array(
                        'listing_product_id' => $this->_input->getListingProduct()->getId(),
                        'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                        'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                        'additional_data'    => Mage::helper('M2ePro')->jsonEncode(
                            array(
                                'params' => $params,
                            )
                        ),
                    )
                );

                if ($scheduledAction->getId()) {
                    $this->getScheduledActionManager()->updateAction($scheduledAction);
                } else {
                    $this->getScheduledActionManager()->addAction($scheduledAction);
                }
            }
        }

        if ($scheduledAction->isActionTypeStop()) {
            if ($this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())) {
                $this->setPropertiesForRecheck($this->getPropertiesDataFromInputInstructions());
            }

            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->disableAll();

        $tags = array();

        if ($scheduledAction->isActionTypeRevise()) {
            if ($scheduledAction->isForce()) {
                return;
            }

            $additionalData = $scheduledAction->getAdditionalData();

            if (isset($additionalData['configurator'])) {
                $configurator->setData($additionalData['configurator']);
            } else {
                $configurator->enableAll();
            }

            $tags = explode('/', $scheduledAction->getTag());
        }

        $tags = array_flip($tags);

        if ($variationManager->isRelationParentType() &&
            $this->_input->getListingProduct()->getChildObject()->getSku() === null
        ) {
            return false;
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseQtyInstructionTypes())) {
            if ($this->isMeetReviseQtyRequirements()) {
                $configurator->allowQty();
                $tags['qty'] = true;
            } else {
                $configurator->disallowQty();
                unset($tags['qty']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseLagTimeInstructionTypes())) {
            if ($this->isMeetReviseLagTime()) {
                $configurator->allowLagTime();
                $tags['lag_time'] = true;
            } else {
                $configurator->disallowLagTime();
                unset($tags['lag_time']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceInstructionTypes())) {
            if ($this->isMeetRevisePriceRequirements()) {
                $configurator->allowPrice();
                $tags['price'] = true;
            } else {
                $configurator->disallowPrice();
                unset($tags['price']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePromotionsInstructionTypes())) {
            if ($this->isMeetRevisePromotionsRequirements()) {
                $configurator->allowPromotions();
                $tags['promotions'] = true;
            } else {
                $configurator->disallowPromotions();
                unset($tags['promotions']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseDetailsInstructionTypes())) {
            if ($this->isMeetReviseDetailsRequirements()) {
                $configurator->allowDetails();
                $tags['details'] = true;
            } else {
                $configurator->disallowDetails();
                unset($tags['details']);
            }
        }

        $this->checkUpdatePriceOrPromotionsFeedsLock(
            $configurator, $tags, Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT
        );

        $types = $configurator->getAllowedDataTypes();
        if (empty($types)) {
            if ($scheduledAction->getId()) {
                $this->getScheduledActionManager()->deleteAction($scheduledAction);
            }

            return;
        }

        $tags = array_keys($tags);

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                'tag'                => '/'.implode('/', $tags).'/',
                'additional_data' => Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'params'       => $params,
                        'configurator' => $configurator->getData()
                    )
                ),
            )
        );

        if ($scheduledAction->getId()) {
            $this->getScheduledActionManager()->updateAction($scheduledAction);
        } else {
            $this->getScheduledActionManager()->addAction($scheduledAction);
        }
    }

    //########################################

    public function isMeetStopRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();
        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if (!$walmartSynchronizationTemplate->isStopMode()) {
            return false;
        }

        if (!$walmartListingProduct->isExistCategoryTemplate()) {
            return false;
        }

        if ($walmartSynchronizationTemplate->isStopStatusDisabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return true;
                }
            }
        }

        if ($walmartSynchronizationTemplate->isStopOutOfStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return true;
                }
            }
        }

        if ($walmartSynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {
            $productQty = (int)$walmartListingProduct->getQty(false);
            $minQty = (int)$walmartSynchronizationTemplate->getStopWhenQtyCalculatedHasValue();

            if ($productQty <= $minQty) {
                return true;
            }
        }

        if ($walmartSynchronizationTemplate->isStopAdvancedRulesEnabled()) {
            /** @var \Ess_M2ePro_Model_Magento_Product_Rule $ruleModel */
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Walmart_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($walmartSynchronizationTemplate->getStopAdvancedRulesFilters());
            $conditions = $ruleModel->getConditions()->getConditions();

            if (empty($conditions)) {
                return false;
            }

            if ($ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseQtyRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();
        if (!$walmartSynchronizationTemplate->isReviseUpdateQty()) {
            return false;
        }

        $isMaxAppliedValueModeOn = $walmartSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $walmartSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $walmartListingProduct->getQty();
        $channelQty = $walmartListingProduct->getOnlineQty();

        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty != $channelQty) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseLagTime()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();
        if (!$walmartSynchronizationTemplate->isReviseUpdateQty()) {
            return false;
        }

        $currentLagTime = $walmartListingProduct->getSellingFormatTemplateSource()->getLagTime();
        $onlineLagTime  = $walmartListingProduct->getOnlineLagTime();

        if ($currentLagTime != $onlineLagTime) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetRevisePriceRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();
        if (!$walmartSynchronizationTemplate->isReviseUpdatePrice()) {
            return false;
        }

        $currentPrice = $walmartListingProduct->getPrice();
        $onlinePrice  = $walmartListingProduct->getOnlinePrice();

        if ($currentPrice != $onlinePrice) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetRevisePromotionsRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();
        if (!$walmartSynchronizationTemplate->isReviseUpdatePromotions()) {
            return false;
        }

        $promotionsActionDataBuilder = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_DataBuilder_Promotions');
        $promotionsActionDataBuilder->setListingProduct($listingProduct);

        $onlinePromotions = $walmartListingProduct->getOnlinePromotions();

        if (empty($onlinePromotions)) {
            $onlinePromotions = $hashDetailsData = Mage::helper('M2ePro')->hashString(
                Mage::helper('M2ePro')->jsonEncode(array('promotion_prices' => array())),
                'md5'
            );
        }

        $hashPromotionsData = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($promotionsActionDataBuilder->getData()),
            'md5'
        );
        return $hashPromotionsData != $onlinePromotions;
    }

    // ---------------------------------------

    public function isMeetReviseDetailsRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();

        if (!$walmartSynchronizationTemplate->isReviseUpdateDetails()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_DataBuilder_Details');
        $actionDataBuilder->setListingProduct($listingProduct);

        $currentDetailsData = $actionDataBuilder->getData();

        $hashDetailsData = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($currentDetailsData),
            'md5'
        );
        if ($hashDetailsData != $walmartListingProduct->getOnlineDetailsData()) {
            return true;
        }

        return false;
    }

    //########################################
}
