<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Repricing extends Ess_M2ePro_Model_Component_Abstract
{
    const PRICE_MODE_MANUAL    = 0;
    const PRICE_MODE_PRODUCT   = 1;
    const PRICE_MODE_SPECIAL   = 2;
    const PRICE_MODE_ATTRIBUTE = 3;

    const REGULAR_PRICE_MODE_PRODUCT_POLICY  = 4;

    const MIN_PRICE_MODE_REGULAR_VALUE       = 4;
    const MIN_PRICE_MODE_REGULAR_PERCENT     = 5;

    const MAX_PRICE_MODE_REGULAR_VALUE       = 4;
    const MAX_PRICE_MODE_REGULAR_PERCENT     = 5;

    const PRICE_VARIATION_MODE_PARENT        = 1;
    const PRICE_VARIATION_MODE_CHILDREN      = 2;

    const DISABLE_MODE_MANUAL                = 0;
    const DISABLE_MODE_PRODUCT_STATUS        = 1;
    const DISABLE_MODE_ATTRIBUTE             = 2;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Account_Repricing');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_accountModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Account', $this->getAccountId()
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
        $this->_accountModel = $instance;
    }

    //########################################

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getData('token');
    }

    /**
     * @return bool
     */
    public function isInvalid()
    {
        return (bool)$this->getData('invalid');
    }

    /**
     * @return $this
     */
    public function markAsInvalid()
    {
        $this->setData('invalid', 1);

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalProducts()
    {
        return $this->getData('total_products');
    }

    //########################################

    /**
     * @return int
     */
    public function getRegularPriceMode()
    {
        return (int)$this->getData('regular_price_mode');
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeManual()
    {
        return $this->getRegularPriceMode() == self::PRICE_MODE_MANUAL;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeProductPolicy()
    {
        return $this->getRegularPriceMode() == self::REGULAR_PRICE_MODE_PRODUCT_POLICY;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeProduct()
    {
        return $this->getRegularPriceMode() == self::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeSpecial()
    {
        return $this->getRegularPriceMode() == self::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeAttribute()
    {
        return $this->getRegularPriceMode() == self::PRICE_MODE_ATTRIBUTE;
    }

    public function getRegularPriceCoefficient()
    {
        return $this->getData('regular_price_coefficient');
    }

    /**
     * @return array
     */
    public function getRegularPriceSource()
    {
        return array(
            'mode'        => $this->getRegularPriceMode(),
            'coefficient' => $this->getRegularPriceCoefficient(),
            'attribute'   => $this->getData('regular_price_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularPriceAttributes()
    {
        $attributes = array();
        $src = $this->getRegularPriceSource();

        if ($src['mode'] == self::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getRegularPriceVariationMode()
    {
        return (int)$this->getData('regular_price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isRegularPriceVariationModeParent()
    {
        return $this->getRegularPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isRegularPriceVariationModeChildren()
    {
        return $this->getRegularPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    /**
     * @return int
     */
    public function getMinPriceMode()
    {
        return (int)$this->getData('min_price_mode');
    }

    /**
     * @return bool
     */
    public function isMinPriceModeManual()
    {
        return $this->getMinPriceMode() == self::PRICE_MODE_MANUAL;
    }

    /**
     * @return bool
     */
    public function isMinPriceModeRegularValue()
    {
        return $this->getMinPriceMode() == self::MIN_PRICE_MODE_REGULAR_VALUE;
    }

    /**
     * @return bool
     */
    public function isMinPriceModeRegularPercent()
    {
        return $this->getMinPriceMode() == self::MIN_PRICE_MODE_REGULAR_PERCENT;
    }

    /**
     * @return bool
     */
    public function isMinPriceModeAttribute()
    {
        return $this->getMinPriceMode() == self::PRICE_MODE_ATTRIBUTE;
    }

    public function getMinPriceCoefficient()
    {
        return $this->getData('min_price_coefficient');
    }

    /**
     * @return array
     */
    public function getMinPriceSource()
    {
        return array(
            'mode'            => $this->getMinPriceMode(),
            'coefficient'     => $this->getMinPriceCoefficient(),
            'attribute'       => $this->getData('min_price_attribute'),
            'regular_value'   => $this->getData('min_price_value'),
            'regular_percent' => $this->getData('min_price_percent'),
        );
    }

    /**
     * @return array
     */
    public function getMinPriceAttributes()
    {
        $attributes = array();
        $src = $this->getMinPriceSource();

        if ($src['mode'] == self::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getMinPriceVariationMode()
    {
        return (int)$this->getData('min_price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isMinPriceVariationModeParent()
    {
        return $this->getMinPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isMinPriceVariationModeChildren()
    {
        return $this->getMinPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    /**
     * @return int
     */
    public function getMaxPriceMode()
    {
        return (int)$this->getData('max_price_mode');
    }

    /**
     * @return bool
     */
    public function isMaxPriceModeManual()
    {
        return $this->getMaxPriceMode() == self::PRICE_MODE_MANUAL;
    }

    /**
     * @return bool
     */
    public function isMaxPriceModeRegularValue()
    {
        return $this->getMaxPriceMode() == self::MAX_PRICE_MODE_REGULAR_VALUE;
    }

    /**
     * @return bool
     */
    public function isMaxPriceModeRegularPercent()
    {
        return $this->getMaxPriceMode() == self::MAX_PRICE_MODE_REGULAR_PERCENT;
    }

    /**
     * @return bool
     */
    public function isMaxPriceModeAttribute()
    {
        return $this->getMaxPriceMode() == self::PRICE_MODE_ATTRIBUTE;
    }

    public function getMaxPriceCoefficient()
    {
        return $this->getData('max_price_coefficient');
    }

    /**
     * @return array
     */
    public function getMaxPriceSource()
    {
        return array(
            'mode'            => $this->getMaxPriceMode(),
            'coefficient'     => $this->getMaxPriceCoefficient(),
            'attribute'       => $this->getData('max_price_attribute'),
            'regular_value'   => $this->getData('max_price_value'),
            'regular_percent' => $this->getData('max_price_percent'),
        );
    }

    /**
     * @return array
     */
    public function getMaxPriceAttributes()
    {
        $attributes = array();
        $src = $this->getMaxPriceSource();

        if ($src['mode'] == self::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getMaxPriceVariationMode()
    {
        return (int)$this->getData('max_price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isMaxPriceVariationModeParent()
    {
        return $this->getMaxPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isMaxPriceVariationModeChildren()
    {
        return $this->getMaxPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    /**
     * @return string|null
     */
    public function getLastCheckedListingProductDate()
    {
        return $this->getData('last_checked_listing_product_update_date');
    }

    //########################################

    /**
     * @return int
     */
    public function getDisableMode()
    {
        return (int)$this->getData('disable_mode');
    }

    /**
     * @return bool
     */
    public function isDisableModeManual()
    {
        return $this->getDisableMode() == self::DISABLE_MODE_MANUAL;
    }

    /**
     * @return bool
     */
    public function isDisableModeProductStatus()
    {
        return $this->getDisableMode() == self::DISABLE_MODE_PRODUCT_STATUS;
    }

    /**
     * @return bool
     */
    public function isDisableModeAttribute()
    {
        return $this->getDisableMode() == self::DISABLE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getDisableSource()
    {
        return array(
            'mode'        => $this->getDisableMode(),
            'attribute'   => $this->getData('disable_mode_attribute')
        );
    }

    /**
     * @return array
     */
    public function getDisableAttributes()
    {
        $attributes = array();
        $src = $this->getDisableSource();

        if ($src['mode'] == self::DISABLE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');
        return parent::delete();
    }

    //########################################
}
