<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Auto_Category_Group getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    protected $_otherCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Auto_Category_Group');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_categoryTemplateModel      = null;
        $this->_otherCategoryTemplateModel = null;
        $this->_magentoProductModel        = null;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if ($this->_categoryTemplateModel === null) {
            try {
                $this->_categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAddingTemplateCategoryId(), null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_categoryTemplateModel;
            }

            if ($this->getMagentoProduct() !== null) {
                $this->_categoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->_categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->_categoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getOtherCategoryTemplate()
    {
        if ($this->_otherCategoryTemplateModel === null) {
            try {
                $this->_otherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAddingTemplateOtherCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_otherCategoryTemplateModel;
            }

            if ($this->getMagentoProduct() !== null) {
                $this->_otherCategoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->_otherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
        $this->_otherCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->_magentoProductModel = $instance;
    }

    //########################################

    public function getAddingTemplateCategoryId()
    {
        return $this->getData('adding_template_category_id');
    }

    public function getAddingTemplateOtherCategoryId()
    {
        return $this->getData('adding_template_other_category_id');
    }

    //########################################

    /**
     * @return bool
     */
    public function isAddingModeAddAndAssignCategory()
    {
        return $this->getParentObject()->getAddingMode() ==
               Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    //########################################
}
