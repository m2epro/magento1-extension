<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace as ResourceModel;

class Ess_M2ePro_Model_Walmart_Dictionary_Marketplace extends Ess_M2ePro_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Walmart_Dictionary_Marketplace');
    }

    /**
     * @param int $marketplaceId
     * @return void
     */
    public function init(
        $marketplaceId,
        \DateTime $clientDetailsLastUpdateDate,
        \DateTime $serverDetailsLastUpdateDate
    ) {
        $this->setData(ResourceModel::COLUMN_MARKETPLACE_ID, $marketplaceId);
        $this->setData(
            ResourceModel::COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE,
            $clientDetailsLastUpdateDate->format('Y-m-d H:i:s')
        );
        $this->setData(
            ResourceModel::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE,
            $serverDetailsLastUpdateDate->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param array $productTypes
     * @return $this
     */
    public function setProductTypes(array $productTypes)
    {
        $this->setData(
            ResourceModel::COLUMN_PRODUCT_TYPES,
            json_encode($productTypes)
        );

        return $this;
    }

    /**
     * @return list<array{nick: string, title: string}>
     */
    public function getProductTypes()
    {
        $productTypes = $this->getData(ResourceModel::COLUMN_PRODUCT_TYPES);
        if (empty($productTypes)) {
            return array();
        }

        return json_decode($productTypes, true);
    }
}
