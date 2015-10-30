<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific extends Mage_Adminhtml_Block_Widget
{
    protected $_marketplaceId = null;
    protected $_categoryMode = null;
    protected $_categoryValue = null;

    protected $_internalData = array();
    protected $_uniqueId = '';
    protected $_isCompactMode = false;

    protected $_attributes = array();
    protected $_selectedSpecifics = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategorySpecific');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/specific.phtml');

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    protected function _beforeToHtml()
    {
        $uniqueId = $this->getUniqueId();

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => '',
                'onclick' => 'EbayListingCategorySpecificHandler'.$uniqueId.'Obj.removeSpecific(this);',
                'class' => 'scalable delete remove_custom_specific_button'
            ));
        $this->setChild('remove_custom_specific_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Add Custom Specific'),
                'onclick' => 'EbayListingCategorySpecificHandler'.$uniqueId.'Obj.addCustomSpecificRow();',
                'class' => 'add add_custom_specific_button'
            ));
        $this->setChild('add_custom_specific_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => '',
                'onclick' => 'EbayListingCategorySpecificHandler'.$uniqueId.'Obj.removeItemSpecificsCustomValue(this);',
                'class'   => 'scalable delete remove_item_specifics_custom_value_button',
                'style'   => 'padding-bottom:1px; padding-right:0px; padding-left:4px;'
            ));
        $this->setChild('remove_item_specifics_custom_value_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    public function getMarketplaceId()
    {
        return $this->_marketplaceId;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->_marketplaceId = $marketplaceId;
        return $this;
    }

    // ---------------------------------------

    public function getCategoryMode()
    {
        return $this->_categoryMode;
    }

    public function setCategoryMode($categoryMode)
    {
        $this->_categoryMode = $categoryMode;
        return $this;
    }

    // ---------------------------------------

    public function getCategoryValue()
    {
        return $this->_categoryValue;
    }

    public function setCategoryValue($categoryValue)
    {
        $this->_categoryValue = $categoryValue;
        return $this;
    }

    //########################################

    public function setInternalData(array $data)
    {
        $this->_internalData = $data;
        return $this;
    }

    public function getInternalData()
    {
        return $this->_internalData;
    }

    // ---------------------------------------

    public function setUniqueId($id)
    {
        $this->_uniqueId = $id;
        return $this;
    }

    public function getUniqueId()
    {
        return $this->_uniqueId;
    }

    // ---------------------------------------

    public function setCompactMode($isMode = true)
    {
        $this->_isCompactMode = $isMode;
        return $this;
    }

    public function isCompactMode()
    {
        return $this->_isCompactMode;
    }

    //########################################

    public function getAttributes()
    {
        $attributes = array();
        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getSelectedSpecifics()
    {
        return $this->_selectedSpecifics;
    }

    public function setSelectedSpecifics(array $specifics)
    {
        foreach ($specifics as $specific) {

            if ($specific['mode'] == Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS) {
                $specific['value_custom_value'] = json_decode($specific['value_custom_value'],true);
                $this->_selectedSpecifics[] = $specific;
                continue;
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = json_decode($specific['value_ebay_recommended'],true);
            }
            unset($specific['value_ebay_recommended']);

            if ($specific['value_mode'] == Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE) {
                $specific['value_data'] = json_decode($specific['value_custom_value'],true);
            }
            unset($specific['value_custom_value']);

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }
            unset($specific['value_custom_attribute']);

            unset($specific['id']);
            unset($specific['template_category_id']);
            unset($specific['update_date']);
            unset($specific['create_date']);

            $this->_selectedSpecifics[] = $specific;
        }

        return $this;
    }

    //########################################

    public function getDictionarySpecifics()
    {
        if ($this->getCategoryMode() == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return array();
        }

        $specifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSpecifics(
            $this->getCategoryValue(), $this->getMarketplaceId()
        );

        return is_null($specifics) ? array() : $specifics;
    }

    public function getEbaySelectedSpecifics()
    {
        return $this->filterSelectedSpecificsByMode(
            Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS
        );
    }

    public function getCustomSelectedSpecifics()
    {
        return $this->filterSelectedSpecificsByMode(
            Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS
        );
    }

    // ---------------------------------------

    public function getRequiredDetailsFields()
    {
        $features = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getFeatures(
            $this->getCategoryValue(), $this->getMarketplaceId()
        );

        if (empty($features)) {
            return array();
        }

        $statusRequired = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_REQUIRED;

        $requiredFields = array();
        foreach (array('ean','upc','isbn') as $identifier) {

            $key = $identifier.'_enabled';
            if (!isset($features[$key]) || $features[$key] != $statusRequired) {
                continue;
            }

            $requiredFields[] = strtoupper($identifier);
        }

        return $requiredFields;
    }

    //########################################

    protected function filterSelectedSpecificsByMode($mode)
    {
        if (count($this->getSelectedSpecifics()) == 0) {
            return array();
        }

        $customSpecifics = array();
        foreach ($this->getSelectedSpecifics() as $selectedSpecific) {
            if ($selectedSpecific['mode'] != $mode) {
                continue;
            }

            unset($selectedSpecific['mode']);
            $customSpecifics[] = $selectedSpecific;
        }

        return $customSpecifics;
    }

    //########################################
}