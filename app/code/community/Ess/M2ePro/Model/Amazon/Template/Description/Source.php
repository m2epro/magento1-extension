<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_descriptionTemplateModel Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Template_Description $instance
     * @return $this
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->_descriptionTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->_descriptionTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return null|string
     */
    public function getWorldwideId()
    {
        $result = '';
        $src = $this->getAmazonDescriptionTemplate()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_NONE) {
            $result = null;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    //########################################
}