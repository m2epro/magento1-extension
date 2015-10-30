<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_OtherCategory getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_OtherCategory extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source[]
     */
    private $otherCategorySourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_OtherCategory');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->marketplaceModel = NULL;
        $this->accountModel = NULL;
        $this->otherCategorySourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $this->getAccountId()
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->otherCategorySourceModels[$productId])) {
            return $this->otherCategorySourceModels[$productId];
        }

        $this->otherCategorySourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Source');
        $this->otherCategorySourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->otherCategorySourceModels[$productId]->setOtherCategoryTemplate($this);

        return $this->otherCategorySourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return array
     */
    public function getCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('category_secondary_mode'),
            'value'     => $this->getData('category_secondary_id'),
            'path'      => $this->getData('category_secondary_path'),
            'attribute' => $this->getData('category_secondary_attribute')
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getStoreCategoryMainSource()
    {
        return array(
            'mode'      => $this->getData('store_category_main_mode'),
            'value'     => $this->getData('store_category_main_id'),
            'path'      => $this->getData('store_category_main_path'),
            'attribute' => $this->getData('store_category_main_attribute')
        );
    }

    /**
     * @return array
     */
    public function getStoreCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('store_category_secondary_mode'),
            'value'     => $this->getData('store_category_secondary_id'),
            'path'      => $this->getData('store_category_secondary_path'),
            'attribute' => $this->getData('store_category_secondary_attribute')
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(

            'category_secondary_id'        => 0,
            'category_secondary_path'      => '',
            'category_secondary_mode'      => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'category_secondary_attribute' => '',

            'store_category_main_id'        => 0,
            'store_category_main_path'      => '',
            'store_category_main_mode'      => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'store_category_main_attribute' => '',

            'store_category_secondary_id'        => 0,
            'store_category_secondary_path'      => '',
            'store_category_secondary_mode'      => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'store_category_secondary_attribute' => ''
        );
    }

    //########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('template_other_category_id', $this->getId());

        if (is_array($columns) && !empty($columns)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$collection->getData() : (array)$collection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_othercategory');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_othercategory');
        return parent::delete();
    }

    //########################################
}