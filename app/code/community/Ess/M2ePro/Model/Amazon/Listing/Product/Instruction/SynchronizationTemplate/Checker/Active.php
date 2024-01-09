<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getStopInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Amazon_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            Ess_M2ePro_Model_Amazon_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            Ess_M2ePro_Model_Amazon_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            Ess_M2ePro_Model_Amazon_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
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
                        'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
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

        $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
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

        $priceInstructionTypes = array_merge(
            $this->getRevisePriceRegularInstructionTypes(),
            $this->getRevisePriceBusinessInstructionTypes()
        );

        if ($this->_input->hasInstructionWithTypes($priceInstructionTypes)) {
            if ($this->isMeetRevisePriceRegularRequirements() || $this->isMeetRevisePriceBusinessRequirements()) {
                $configurator->allowRegularPrice()->allowBusinessPrice();
                $tags['price'] = true;
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

        if ($this->_input->hasInstructionWithTypes($this->getReviseImagesInstructionTypes())) {
            if ($this->isMeetReviseImagesRequirements()) {
                $configurator->allowImages();
                $tags['images'] = true;
            } else {
                $configurator->disallowImages();
                unset($tags['images']);
            }
        }

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
                'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if ($amazonListingProduct->isAfnChannel()) {
            return false;
        }

        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if (!$amazonSynchronizationTemplate->isStopMode()) {
            return false;
        }

        if ($amazonSynchronizationTemplate->isStopStatusDisabled()) {
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

        if ($amazonSynchronizationTemplate->isStopOutOfStock()) {
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

        if ($amazonSynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {
            $productQty = (int)$amazonListingProduct->getQty(false);
            $minQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyCalculatedHasValue();

            if ($productQty <= $minQty) {
                return true;
            }
        }

        if ($amazonSynchronizationTemplate->isStopAdvancedRulesEnabled()) {
            /** @var \Ess_M2ePro_Model_Magento_Product_Rule $ruleModel */
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($amazonSynchronizationTemplate->getStopAdvancedRulesFilters());
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        if (!$amazonSynchronizationTemplate->isReviseUpdateQty() || $amazonListingProduct->isAfnChannel()) {
            return false;
        }

        $currentHandlingTime = $amazonListingProduct->getListingSource()->getHandlingTime();
        $onlineHandlingTime  = $amazonListingProduct->getOnlineHandlingTime();

        if ($currentHandlingTime != $onlineHandlingTime) {
            return true;
        }

        $currentRestockDate = $amazonListingProduct->getListingSource()->getRestockDate();
        $onlineRestockDate  = $amazonListingProduct->getOnlineRestockDate();

        if ($currentRestockDate != $onlineRestockDate) {
            return true;
        }

        $isMaxAppliedValueModeOn = $amazonSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $amazonSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $amazonListingProduct->getQty();
        $channelQty = $amazonListingProduct->getOnlineQty();

        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty != $channelQty) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetRevisePriceRegularRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        if (!$amazonListingProduct->isAllowedForRegularCustomers()) {
            return false;
        }

        if ($amazonListingProduct->isRepricingManaged()) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isReviseUpdatePrice()) {
            return false;
        }

        $currentPrice = $amazonListingProduct->getRegularPrice();
        $onlinePrice  = $amazonListingProduct->getOnlineRegularPrice();

        if ($currentPrice != $onlinePrice) {
            return true;
        }

        $currentSalePriceInfo = $amazonListingProduct->getRegularSalePriceInfo();
        if ($currentSalePriceInfo !== false) {
            $currentSalePrice          = $currentSalePriceInfo['price'];
            $currentSalePriceStartDate = $currentSalePriceInfo['start_date'];
            $currentSalePriceEndDate   = $currentSalePriceInfo['end_date'];
        } else {
            $currentSalePrice          = 0;
            $currentSalePriceStartDate = null;
            $currentSalePriceEndDate   = null;
        }

        $onlineSalePrice = $amazonListingProduct->getOnlineRegularSalePrice();

        if (!$currentSalePrice && !$onlineSalePrice) {
            return false;
        }

        if (($currentSalePrice === null && $onlineSalePrice !== null) ||
            ($currentSalePrice !== null && $onlineSalePrice === null)
        ) {
            return true;
        }

        if ($onlineSalePrice != $currentSalePrice) {
            return true;
        }

        $onlineSalePriceStartDate  = $amazonListingProduct->getOnlineRegularSalePriceStartDate();
        $onlineSalePriceEndDate    = $amazonListingProduct->getOnlineRegularSalePriceEndDate();

        if ($currentSalePriceStartDate != $onlineSalePriceStartDate ||
            $currentSalePriceEndDate   != $onlineSalePriceEndDate
        ) {
            return true;
        }

        return false;
    }

    public function isMeetRevisePriceBusinessRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();
        if ($variationManager->isRelationParentType()) {
            return false;
        }

        if (!$amazonListingProduct->isAllowedForBusinessCustomers()) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isReviseUpdatePrice()) {
            return false;
        }

        $currentPrice = $amazonListingProduct->getBusinessPrice();
        $onlinePrice  = $amazonListingProduct->getOnlineBusinessPrice();

        if ($currentPrice != $onlinePrice) {
            return true;
        }

        $currentDiscounts = $amazonListingProduct->getBusinessDiscounts();
        $onlineDiscounts  = $amazonListingProduct->getOnlineBusinessDiscounts();

        // amazon does not support disabling discounts, so revise should not be allowed
        if (empty($currentDiscounts)) {
            return false;
        }

        if (count($currentDiscounts) != count($onlineDiscounts)) {
            return true;
        }

        foreach ($currentDiscounts as $qty => $currentDiscount) {
            if (!isset($onlineDiscounts[$qty])) {
                return true;
            }

            $onlineDiscount = $onlineDiscounts[$qty];

            if ($onlineDiscount != $currentDiscount) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseDetailsRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        if (!$amazonSynchronizationTemplate->isReviseWhenChangeDetails()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_DataBuilder_Details');
        $actionDataBuilder->setListingProduct($listingProduct);

        $hashDetailsData = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($actionDataBuilder->getData()),
            'md5'
        );
        if ($hashDetailsData != $amazonListingProduct->getOnlineDetailsData()) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseImagesRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        if (!$amazonSynchronizationTemplate->isReviseWhenChangeImages()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_DataBuilder_Images');
        $actionDataBuilder->setListingProduct($listingProduct);

        $hashImagesData = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($actionDataBuilder->getData()),
            'md5'
        );
        if ($hashImagesData != $amazonListingProduct->getOnlineImagesData()) {
            return true;
        }

        return false;
    }

    //########################################
}
