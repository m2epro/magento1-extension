<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_sellingTemplateModel Ess_M2ePro_Model_Template_SellingFormat
     */
    protected $_sellingTemplateModel = null;

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
        $this->_sellingTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->_sellingTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return string
     */
    public function getTaxCategory()
    {
        $src = $this->getEbaySellingFormatTemplate()->getTaxCategorySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::TAX_CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        $src = $this->getEbaySellingFormatTemplate()->getDurationSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::DURATION_TYPE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    /**
     * @return string
     */
    public function getLotSize()
    {
        $src = $this->getEbaySellingFormatTemplate()->getLotSizeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LOT_SIZE_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    /**
     * @return int
     */
    public function getListingType()
    {
        $src = $this->getEbaySellingFormatTemplate()->getListingTypeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_ATTRIBUTE) {
            $ebayStringType = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            switch ($ebayStringType) {
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_FIXED:
                    return Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_AUCTION:
                    return Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
            }

            return Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
        }

        return $src['mode'];
    }

    //########################################
}
