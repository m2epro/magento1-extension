<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Synchronization getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Synchronization getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Synchronization extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const LIST_ADVANCED_RULES_PREFIX   = 'ebay_template_synchronization_list_advanced_rules';
    const RELIST_ADVANCED_RULES_PREFIX = 'ebay_template_synchronization_relist_advanced_rules';
    const STOP_ADVANCED_RULES_PREFIX   = 'ebay_template_synchronization_stop_advanced_rules';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Synchronization');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter(
                                'template_synchronization_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize();
    }

    //########################################

    /**
     * @return bool
     */
    public function isListMode()
    {
        return $this->getData('list_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != 0;
    }

    /**
     * @return bool
     */
    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isListAdvancedRulesEnabled()
    {
        $rules = $this->getListAdvancedRulesFilters();
        return $this->getData('list_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getListAdvancedRulesFilters()
    {
        return $this->getData('list_advanced_rules_filters');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValueMode()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOn()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == 1;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOff()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == 0;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValue()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseUpdateQty()
    {
        return $this->getData('revise_update_qty') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePrice()
    {
        return $this->getData('revise_update_price') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateTitle()
    {
        return $this->getData('revise_update_title') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateSubtitle()
    {
        return $this->getData('revise_update_sub_title') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateDescription()
    {
        return $this->getData('revise_update_description') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateImages()
    {
        return $this->getData('revise_update_images') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateCategories()
    {
        return $this->getData('revise_update_categories') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateShipping()
    {
        return $this->getData('revise_update_shipping') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePayment()
    {
        return $this->getData('revise_update_payment') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateReturn()
    {
        return $this->getData('revise_update_return') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateOther()
    {
        return $this->getData('revise_update_other') != 0;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRelistMode()
    {
        return $this->getData('relist_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistAdvancedRulesEnabled()
    {
        $rules = $this->getRelistAdvancedRulesFilters();
        return $this->getData('relist_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getRelistAdvancedRulesFilters()
    {
        return $this->getData('relist_advanced_rules_filters');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isStopMode()
    {
        return $this->getData('stop_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != 0;
    }

    /**
     * @return bool
     */
    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStopAdvancedRulesEnabled()
    {
        $rules = $this->getStopAdvancedRulesFilters();
        return $this->getData('stop_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getStopAdvancedRulesFilters()
    {
        return $this->getData('stop_advanced_rules_filters');
    }

    //########################################

    public function getListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated_value');
    }

    public function getRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated_value');
    }

    public function getStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated_value');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    //########################################
}
