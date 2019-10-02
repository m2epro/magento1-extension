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
    const LIST_MODE_NONE = 0;
    const LIST_MODE_YES = 1;

    const LIST_STATUS_ENABLED_NONE = 0;
    const LIST_STATUS_ENABLED_YES  = 1;

    const LIST_IS_IN_STOCK_NONE = 0;
    const LIST_IS_IN_STOCK_YES  = 1;

    const LIST_QTY_NONE    = 0;
    const LIST_QTY_LESS    = 1;
    const LIST_QTY_BETWEEN = 2;
    const LIST_QTY_MORE    = 3;

    const REVISE_UPDATE_QTY_NONE = 0;
    const REVISE_UPDATE_QTY_YES  = 1;

    const REVISE_MAX_AFFECTED_QTY_MODE_OFF = 0;
    const REVISE_MAX_AFFECTED_QTY_MODE_ON = 1;

    const REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT = 5;

    const REVISE_UPDATE_PRICE_NONE = 0;
    const REVISE_UPDATE_PRICE_YES  = 1;

    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF = 0;
    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON  = 1;

    const REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT = 3;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const REVISE_UPDATE_IMAGES_NONE = 0;
    const REVISE_UPDATE_IMAGES_YES  = 1;

    const REVISE_UPDATE_CATEGORIES_NONE = 0;
    const REVISE_UPDATE_CATEGORIES_YES  = 1;

    const REVISE_UPDATE_SHIPPING_NONE = 0;
    const REVISE_UPDATE_SHIPPING_YES  = 1;

    const REVISE_UPDATE_PAYMENT_NONE = 0;
    const REVISE_UPDATE_PAYMENT_YES  = 1;

    const REVISE_UPDATE_RETURN_NONE = 0;
    const REVISE_UPDATE_RETURN_YES  = 1;

    const REVISE_UPDATE_OTHER_NONE = 0;
    const REVISE_UPDATE_OTHER_YES  = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES  = 1;

    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES  = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES  = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES  = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const STOP_MODE_NONE = 0;
    const STOP_MODE_YES  = 1;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

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
                            ->addFieldToFilter(
                                'template_synchronization_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
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
        return $this->getData('list_mode') != self::LIST_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != self::LIST_STATUS_ENABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != self::LIST_IS_IN_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyMagentoHasValue()
    {
        return $this->getData('list_qty_magento') != self::LIST_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated') != self::LIST_QTY_NONE;
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
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOff()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_OFF;
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
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviationMode()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOn()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode() == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOff()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode()
                    == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviation()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation');
    }

    // ---------------------------------------

    public function isPriceChangedOverAllowedDeviation($onlinePrice, $currentPrice)
    {
        if ((float)$onlinePrice == (float)$currentPrice) {
            return false;
        }

        if ((float)$onlinePrice <= 0) {
            return true;
        }

        if ($this->isReviseUpdatePriceMaxAllowedDeviationModeOff()) {
            return true;
        }

        $deviation = round(abs($onlinePrice - $currentPrice) / $onlinePrice * 100, 2);

        return $deviation > $this->getReviseUpdatePriceMaxAllowedDeviation();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseUpdateQty()
    {
        return $this->getData('revise_update_qty') != self::REVISE_UPDATE_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePrice()
    {
        return $this->getData('revise_update_price') != self::REVISE_UPDATE_PRICE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateTitle()
    {
        return $this->getData('revise_update_title') != self::REVISE_UPDATE_TITLE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateSubtitle()
    {
        return $this->getData('revise_update_sub_title') != self::REVISE_UPDATE_SUB_TITLE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateDescription()
    {
        return $this->getData('revise_update_description') != self::REVISE_UPDATE_DESCRIPTION_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateImages()
    {
        return $this->getData('revise_update_images') != self::REVISE_UPDATE_IMAGES_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateCategories()
    {
        return $this->getData('revise_update_categories') != self::REVISE_UPDATE_CATEGORIES_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateShipping()
    {
        return $this->getData('revise_update_shipping') != self::REVISE_UPDATE_SHIPPING_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePayment()
    {
        return $this->getData('revise_update_payment') != self::REVISE_UPDATE_PAYMENT_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateReturn()
    {
        return $this->getData('revise_update_return') != self::REVISE_UPDATE_RETURN_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateOther()
    {
        return $this->getData('revise_update_other') != self::REVISE_UPDATE_OTHER_NONE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRelistMode()
    {
        return $this->getData('relist_mode') != self::RELIST_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyMagentoHasValue()
    {
        return $this->getData('relist_qty_magento') != self::RELIST_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated') != self::RELIST_QTY_NONE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isStopMode()
    {
        return $this->getData('stop_mode') != self::STOP_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyMagentoHasValue()
    {
        return $this->getData('stop_qty_magento') != self::STOP_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated') != self::STOP_QTY_NONE;
    }

    //########################################

    public function getListWhenQtyMagentoHasValueType()
    {
        return $this->getData('list_qty_magento');
    }

    public function getListWhenQtyMagentoHasValueMin()
    {
        return $this->getData('list_qty_magento_value');
    }

    public function getListWhenQtyMagentoHasValueMax()
    {
        return $this->getData('list_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getListWhenQtyCalculatedHasValueType()
    {
        return $this->getData('list_qty_calculated');
    }

    public function getListWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('list_qty_calculated_value');
    }

    public function getListWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('list_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyMagentoHasValueType()
    {
        return $this->getData('relist_qty_magento');
    }

    public function getRelistWhenQtyMagentoHasValueMin()
    {
        return $this->getData('relist_qty_magento_value');
    }

    public function getRelistWhenQtyMagentoHasValueMax()
    {
        return $this->getData('relist_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyCalculatedHasValueType()
    {
        return $this->getData('relist_qty_calculated');
    }

    public function getRelistWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('relist_qty_calculated_value');
    }

    public function getRelistWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('relist_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyMagentoHasValueType()
    {
        return $this->getData('stop_qty_magento');
    }

    public function getStopWhenQtyMagentoHasValueMin()
    {
        return $this->getData('stop_qty_magento_value');
    }

    public function getStopWhenQtyMagentoHasValueMax()
    {
        return $this->getData('stop_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyCalculatedHasValueType()
    {
        return $this->getData('stop_qty_calculated');
    }

    public function getStopWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('stop_qty_calculated_value');
    }

    public function getStopWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('stop_qty_calculated_value_max');
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettingsSimpleMode()
    {
        return array_merge(
            $this->getListDefaultSettingsSimpleMode(),
            $this->getReviseDefaultSettingsSimpleMode(),
            $this->getRelistDefaultSettingsSimpleMode(),
            $this->getStopDefaultSettingsSimpleMode()
        );
    }

    /**
     * @return array
     */
    public function getDefaultSettingsAdvancedMode()
    {
        return array_merge(
            $this->getListDefaultSettingsAdvancedMode(),
            $this->getReviseDefaultSettingsAdvancedMode(),
            $this->getRelistDefaultSettingsAdvancedMode(),
            $this->getStopDefaultSettingsAdvancedMode()
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getListDefaultSettingsSimpleMode()
    {
        return array(
            'list_mode'           => self::LIST_MODE_NONE,
            'list_status_enabled' => self::LIST_STATUS_ENABLED_YES,
            'list_is_in_stock'    => self::LIST_IS_IN_STOCK_YES,

            'list_qty_magento'           => self::LIST_QTY_NONE,
            'list_qty_magento_value'     => '1',
            'list_qty_magento_value_max' => '10',

            'list_qty_calculated'           => self::LIST_QTY_NONE,
            'list_qty_calculated_value'     => '1',
            'list_qty_calculated_value_max' => '10',
        );
    }

    /**
     * @return array
     */
    public function getReviseDefaultSettingsSimpleMode()
    {
        return array(
            'revise_update_qty'                              => self::REVISE_UPDATE_QTY_YES,
            'revise_update_qty_max_applied_value_mode'       => self::REVISE_MAX_AFFECTED_QTY_MODE_OFF,
            'revise_update_qty_max_applied_value'            => self::REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT,
            'revise_update_price'                            => self::REVISE_UPDATE_PRICE_YES,
            'revise_update_price_max_allowed_deviation_mode' => self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF,
            'revise_update_price_max_allowed_deviation'      => self::REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT,
            'revise_update_title'                            => self::REVISE_UPDATE_TITLE_NONE,
            'revise_update_sub_title'                        => self::REVISE_UPDATE_SUB_TITLE_NONE,
            'revise_update_description'                      => self::REVISE_UPDATE_DESCRIPTION_NONE,
            'revise_update_images'                           => self::REVISE_UPDATE_IMAGES_NONE,
            'revise_update_categories'                       => self::REVISE_UPDATE_CATEGORIES_NONE,
            'revise_update_shipping'                         => self::REVISE_UPDATE_SHIPPING_NONE,
            'revise_update_payment'                          => self::REVISE_UPDATE_PAYMENT_NONE,
            'revise_update_return'                           => self::REVISE_UPDATE_RETURN_NONE,
            'revise_update_other'                           => self::REVISE_UPDATE_OTHER_NONE,
        );
    }

    /**
     * @return array
     */
    public function getRelistDefaultSettingsSimpleMode()
    {
        return array(
            'relist_mode'             => self::RELIST_MODE_YES,
            'relist_filter_user_lock' => self::RELIST_FILTER_USER_LOCK_YES,
            'relist_status_enabled'   => self::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock'      => self::RELIST_IS_IN_STOCK_YES,

            'relist_qty_magento'           => self::RELIST_QTY_NONE,
            'relist_qty_magento_value'     => '1',
            'relist_qty_magento_value_max' => '10',

            'relist_qty_calculated'           => self::RELIST_QTY_NONE,
            'relist_qty_calculated_value'     => '1',
            'relist_qty_calculated_value_max' => '10'
        );
    }

    /**
     * @return array
     */
    public function getStopDefaultSettingsSimpleMode()
    {
        return array(
            'stop_mode' => self::STOP_MODE_YES,

            'stop_status_disabled' => self::STOP_STATUS_DISABLED_YES,
            'stop_out_off_stock'   => self::STOP_OUT_OFF_STOCK_YES,

            'stop_qty_magento'           => self::STOP_QTY_NONE,
            'stop_qty_magento_value'     => '0',
            'stop_qty_magento_value_max' => '10',

            'stop_qty_calculated'           => self::STOP_QTY_NONE,
            'stop_qty_calculated_value'     => '0',
            'stop_qty_calculated_value_max' => '10'
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getListDefaultSettingsAdvancedMode()
    {
        $simpleSettings = $this->getListDefaultSettingsSimpleMode();

        $simpleSettings['list_mode'] = self::LIST_MODE_YES;

        return $simpleSettings;
    }

    /**
     * @return array
     */
    public function getReviseDefaultSettingsAdvancedMode()
    {
        $simpleSettings = $this->getReviseDefaultSettingsSimpleMode();

        $simpleSettings['revise_update_qty_max_applied_value_mode'] = self::REVISE_MAX_AFFECTED_QTY_MODE_ON;

        $simpleSettings['revise_update_price_max_allowed_deviation_mode']
            = self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON;

        return $simpleSettings;
    }

    /**
     * @return array
     */
    public function getRelistDefaultSettingsAdvancedMode()
    {
        return $this->getRelistDefaultSettingsSimpleMode();
    }

    /**
     * @return array
     */
    public function getStopDefaultSettingsAdvancedMode()
    {
        return $this->getStopDefaultSettingsSimpleMode();
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
