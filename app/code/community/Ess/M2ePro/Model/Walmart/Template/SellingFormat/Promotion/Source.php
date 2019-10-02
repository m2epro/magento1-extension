<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_sellingFormatPromotionModel Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion
     */
    protected $_sellingFormatPromotionModel = null;

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
     * @param Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion $instance
     * @return $this
     */
    public function setSellingFormatPromotion(Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion $instance)
    {
        $this->_sellingFormatPromotionModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion
     */
    public function getSellingFormatPromotion()
    {
        return $this->_sellingFormatPromotionModel;
    }

    //########################################

    public function getStartDate()
    {
        $result = NULL;

        switch ($this->getSellingFormatPromotion()->getStartDateMode()) {
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion::START_DATE_MODE_VALUE:
                $result = $this->getSellingFormatPromotion()->getStartDateValue();
                break;

            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion::START_DATE_MODE_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getSellingFormatPromotion()->getStartDateAttribute()
                );
                break;
        }

        return $result;
    }

    public function getEndDate()
    {
        $result = NULL;

        switch ($this->getSellingFormatPromotion()->getEndDateMode()) {
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion::END_DATE_MODE_VALUE:
                $result = $this->getSellingFormatPromotion()->getEndDateValue();
                break;

            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion::END_DATE_MODE_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getSellingFormatPromotion()->getEndDateAttribute()
                );
                break;
        }

        return $result;
    }

    //########################################
}