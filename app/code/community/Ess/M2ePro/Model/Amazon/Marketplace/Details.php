<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Marketplace_Details
{
    protected $_marketplaceId = null;

    protected $_productData = array();

    //########################################

    /**
     * @param $marketplaceId
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setMarketplaceId($marketplaceId)
    {
        if ($this->_marketplaceId === $marketplaceId) {
            return $this;
        }

        $this->_marketplaceId = $marketplaceId;
        $this->load();

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getProductData()
    {
       return $this->_productData;
    }

    /**
     * @param $productDataNick
     * @return array
     */
    public function getVariationThemes($productDataNick)
    {
        if (!isset($this->_productData[$productDataNick])) {
            return array();
        }

        return (array)$this->_productData[$productDataNick]['variation_themes'];
    }

    /**
     * @param $productDataNick
     * @param $theme
     * @return array
     */
    public function getVariationThemeAttributes($productDataNick, $theme)
    {
        $themes = $this->getVariationThemes($productDataNick);
        return !empty($themes[$theme]['attributes']) ? $themes[$theme]['attributes'] : array();
    }

    //########################################

    protected function load()
    {
        if ($this->_marketplaceId === null) {
            throw new Ess_M2ePro_Model_Exception('Marketplace was not set.');
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table    = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');

        $data = $connRead->select()
            ->from($table)
            ->where('marketplace_id = ?', (int)$this->_marketplaceId)
            ->query()
            ->fetch();

        if ($data === false) {
            throw new Ess_M2ePro_Model_Exception('Marketplace not found or not synchronized');
        }

        $this->_productData = Mage::helper('M2ePro')->jsonDecode($data['product_data']);
    }

    //########################################
}
