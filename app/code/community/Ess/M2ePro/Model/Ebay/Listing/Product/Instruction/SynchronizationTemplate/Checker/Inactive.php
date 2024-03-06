<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Inactive
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getRelistInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_RELIST_MODE_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_RELIST_MODE_DISABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
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

        if (!$listingProduct->isRelistable() && !$listingProduct->isHidden()) {
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

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->_input->getListingProduct()->getChildObject();
        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->disableAll()->allowQty()->allowVariations();

        $tags = array('qty');

        if ($ebaySynchronizationTemplate->isReviseUpdatePrice()) {
            $configurator->allowPrice();
            $tags[] = 'price';
        }

        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        $actionType = Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;

        if ($this->_input->getListingProduct()->isHidden()) {
            $actionType = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
            $params['replaced_action'] = Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
        }

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'action_type'        => $actionType,
                'tag'                => '/'.implode('/', $tags).'/',
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode(
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

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        // Correct synchronization
        // ---------------------------------------
        if (!$ebaySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($listingProduct->isInactive() &&
            $ebaySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        // ---------------------------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        // ---------------------------------------
        if ($ebaySynchronizationTemplate->isRelistStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isRelistIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {
            $result = false;
            $productQty = (int)$ebayListingProduct->getQty();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValue();

            if ($productQty >= $minQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($ebaySynchronizationTemplate->isRelistAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($ebaySynchronizationTemplate->getRelistAdvancedRulesFilters());

            if (!$ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                return false;
            }
        }

        return true;
    }

    //########################################
}
