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
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categorySecondaryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategorySecondaryTemplateModel = null;

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

        $this->_categoryTemplateModel               = null;
        $this->_categorySecondaryTemplateModel      = null;
        $this->_storeCategoryTemplateModel          = null;
        $this->_storeCategorySecondaryTemplateModel = null;
        $this->_magentoProductModel                 = null;

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

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategorySecondaryTemplate()
    {
        if ($this->_categorySecondaryTemplateModel === null) {
            try {
                $this->_categorySecondaryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category',
                    (int)$this->getAddingTemplateCategorySecondaryId(),
                    null,
                    array('template')
                );
            } catch (Exception $exception) {
                return $this->_categorySecondaryTemplateModel;
            }

            if ($this->getMagentoProduct() !== null) {
                $this->_categorySecondaryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->_categorySecondaryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategorySecondaryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->_categorySecondaryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    public function getStoreCategoryTemplate()
    {
        if ($this->_storeCategoryTemplateModel === null) {
            try {
                $this->_storeCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_StoreCategory', (int)$this->getAddingTemplateStoreCategoryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_storeCategoryTemplateModel;
            }

            if ($this->getMagentoProduct() !== null) {
                $this->_storeCategoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->_storeCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance
     */
    public function setStoreCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance)
    {
        $this->_storeCategoryTemplateModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    public function getStoreCategorySecondaryTemplate()
    {
        if ($this->_storeCategorySecondaryTemplateModel === null) {
            try {
                $this->_storeCategorySecondaryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_StoreCategory', (int)$this->getAddingTemplateStoreCategorySecondaryId(),
                    null, array('template')
                );
            } catch (Exception $exception) {
                return $this->_storeCategorySecondaryTemplateModel;
            }

            if ($this->getMagentoProduct() !== null) {
                $this->_storeCategorySecondaryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->_storeCategorySecondaryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance
     */
    public function setStoreCategorySecondaryTemplate(Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance)
    {
        $this->_storeCategorySecondaryTemplateModel = $instance;
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

    public function getAddingTemplateCategorySecondaryId()
    {
        return $this->getData('adding_template_category_secondary_id');
    }

    public function getAddingTemplateStoreCategoryId()
    {
        return $this->getData('adding_template_store_category_id');
    }

    public function getAddingTemplateStoreCategorySecondaryId()
    {
        return $this->getData('adding_template_store_category_secondary_id');
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
