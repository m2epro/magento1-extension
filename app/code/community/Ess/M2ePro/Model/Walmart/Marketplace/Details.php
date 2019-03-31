<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Marketplace_Details
{
    private $marketplaceId = null;

    private $productData = array();

    //########################################

    /**
     * @param $marketplaceId
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setMarketplaceId($marketplaceId)
    {
        if ($this->marketplaceId === $marketplaceId) {
            return $this;
        }

        $this->marketplaceId = $marketplaceId;
        $this->load();

        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getProductData()
    {
       return $this->productData;
    }

    /**
     * @param $productDataNick
     * @return array
     */
    public function getVariationAttributes($productDataNick)
    {
        if (!isset($this->productData[$productDataNick])) {
            return array();
        }

        return (array)$this->productData[$productDataNick]['variation_attributes'];
    }

    //########################################

    private function load()
    {
        if (is_null($this->marketplaceId)) {
            throw new Ess_M2ePro_Model_Exception('Marketplace was not set.');
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table    = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_walmart_dictionary_marketplace');

        $data = $connRead->select()
            ->from($table)
            ->where('marketplace_id = ?', (int)$this->marketplaceId)
            ->query()
            ->fetch();

        if ($data === false) {
            throw new Ess_M2ePro_Model_Exception('Marketplace not found or not synchronized');
        }

        $this->productData    = Mage::helper('M2ePro')->jsonDecode($data['product_data']);
    }

    //########################################
}