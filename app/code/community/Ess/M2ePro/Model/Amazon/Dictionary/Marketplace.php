<?php

class Ess_M2ePro_Model_Amazon_Dictionary_Marketplace extends Ess_M2ePro_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_Dictionary_Marketplace');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)parent::getId();
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_MARKETPLACE_ID);
    }

    /**
     * @return array
     */
    public function getProductTypes()
    {
        return (array)json_decode(
            (string)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_PRODUCT_TYPES),
            true
        );
    }
}
