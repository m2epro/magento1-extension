<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_SellingFormat as WalmartSellingFormat;

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_sellingFormatTemplateModel Ess_M2ePro_Model_Template_Sellingformat
     */
    protected $_sellingFormatTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     * @return $this
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
        $this->_sellingFormatTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getWalmartSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //########################################

    public function getLagTime()
    {
        $result = 0;
        $src = $this->getWalmartSellingFormatTemplate()->getLagTimeSource();

        if ($src['mode'] == WalmartSellingFormat::LAG_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::LAG_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 0 && $result = 0;

        return $result;
    }

    public function getItemWeight()
    {
        $result = 0;
        $src = $this->getWalmartSellingFormatTemplate()->getItemWeightSource();

        if ($src['mode'] == WalmartSellingFormat::WEIGHT_MODE_CUSTOM_VALUE) {
            $result = $src['custom_value'];
        }

        if ($src['mode'] == WalmartSellingFormat::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        $result < 0 && $result = 0;

        return $result;
    }

    public function getMustShipAlone()
    {
        $result = null;
        $src = $this->getWalmartSellingFormatTemplate()->getMustShipAloneSource();

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_YES) {
            $result = true;
        }

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_NO) {
            $result = false;
        }

        if ($src['mode'] == WalmartSellingFormat::MUST_SHIP_ALONE_MODE_CUSTOM_ATTRIBUTE) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    public function getShipsInOriginalPackaging()
    {
        $result = null;
        $src = $this->getWalmartSellingFormatTemplate()->getShipsInOriginalPackagingModeSource();

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_YES) {
            $result = true;
        }

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_NO) {
            $result = false;
        }

        if ($src['mode'] == WalmartSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_CUSTOM_ATTRIBUTE) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    public function getStartDate()
    {
        $result = null;
        $src = $this->getWalmartSellingFormatTemplate()->getSaleTimeStartDateSource();

        if ($src['mode'] == WalmartSellingFormat::DATE_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::DATE_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    public function getEndDate()
    {
        $result = null;
        $src = $this->getWalmartSellingFormatTemplate()->getSaleTimeEndDateSource();

        if ($src['mode'] == WalmartSellingFormat::DATE_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == WalmartSellingFormat::DATE_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        if ($this->getWalmartSellingFormatTemplate()->isAttributesModeNone()) {
            return array();
        }

        $result = array();
        $src = $this->getWalmartSellingFormatTemplate()->getAttributesSource();

        foreach ($src['template'] as $value) {
            if (empty($value)) {
                continue;
            }

            $result[$value['name']] = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $value['value'], $this->getMagentoProduct()
            );
        }

        return $result;
    }

    //########################################
}
