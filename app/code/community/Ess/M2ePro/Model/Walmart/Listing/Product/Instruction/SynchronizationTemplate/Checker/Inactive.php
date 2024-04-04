<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_Synchronization_ChangeProcessor as SynchronizationChangeProcessor;
use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ProductChangeProcessor;

class Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Inactive
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getRelistInstructionTypes()
    {
        return array(
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_RELIST_MODE_ENABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_RELIST_MODE_DISABLED,
            SynchronizationChangeProcessor::INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED,

            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ProductChangeProcessor::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,

            Ess_M2ePro_Model_Walmart_Magento_Product_ChangeProcessor::INSTRUCTION_TYPE_PROMOTIONS_DATA_CHANGED,

            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,

            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,

            Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response::INSTRUCTION_TYPE_CHECK_QTY,

            Ess_M2ePro_Model_Walmart_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,

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

        if (!$this->_input->hasInstructionWithTypes($this->getRelistInstructionTypes()) &&
            !$this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())
        ) {
            return false;
        }

        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();

        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {
            if ($variationManager->isRelationParentType()) {
                return false;
            }

            if (!$variationManager->getTypeModel()->isVariationProductMatched()) {
                return false;
            }
        }

        if ($listingProduct->isBlocked() && $walmartListingProduct->isOnlinePriceInvalid()) {
            return true;
        }

        if (!$listingProduct->isRelistable() || !$listingProduct->isInactive()) {
            return false;
        }

        return true;
    }

    //########################################

    public function process(array $params = array())
    {
        if ($this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())) {
            $this->setPropertiesForRecheck($this->getPropertiesDataFromInputInstructions());
        }

        if (!$this->_input->hasInstructionWithTypes($this->getRelistInstructionTypes())) {
            return;
        }

        if (!$this->isMeetRelistRequirements()) {
            if ($this->_input->getScheduledAction() && !$this->_input->getScheduledAction()->isForce()) {
                $this->getScheduledActionManager()->deleteAction($this->_input->getScheduledAction());
            }

            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->_input->getListingProduct()->getChildObject();
        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();

        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->disableAll()->allowQty();

        $tags = array('qty');

        if ($walmartSynchronizationTemplate->isReviseUpdatePrice() ||
            ($this->_input->getListingProduct()->isBlocked() && $walmartListingProduct->isOnlinePriceInvalid())
        ) {
            $configurator->allowPrice();
            $tags[] = 'price';
        }

        if ($walmartSynchronizationTemplate->isReviseUpdatePromotions()) {
            // Due to the fact that "promotion feed" can be sent only 6 times a day,
            // we are forced to refuse on relist action.
            $this->setPropertiesForRecheck(array('promotions'));
        }

        $this->checkUpdatePriceOrPromotionsFeedsLock(
            $configurator, $tags, Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT
        );

        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                'tag'                => '/'.implode('/', $tags).'/',
                'additional_data' => Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'params'       => $params,
                        'configurator' => $configurator->getData(),
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

    public function isMeetRelistRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();

        if (!$walmartSynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if (
            $walmartSynchronizationTemplate->isRelistFilterUserLock()
            && $walmartListingProduct->isStoppedManually()
        ) {
            return false;
        }

        if (!$walmartListingProduct->isExistCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($walmartSynchronizationTemplate->isRelistStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return false;
                }
            }
        }

        if ($walmartSynchronizationTemplate->isRelistIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return false;
                }
            }
        }

        if ($walmartSynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {
            $result = false;
            $productQty = (int)$walmartListingProduct->getQty(false);
            $minQty = (int)$walmartSynchronizationTemplate->getRelistWhenQtyCalculatedHasValue();

            if ($productQty >= $minQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($walmartSynchronizationTemplate->isRelistAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Walmart_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($walmartSynchronizationTemplate->getRelistAdvancedRulesFilters());

            if (!$ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                return false;
            }
        }

        return true;
    }

    //########################################
}
