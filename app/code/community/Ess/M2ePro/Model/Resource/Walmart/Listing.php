<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Walmart_Listing
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;
    protected $_statisticDataCount = null;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Listing', 'listing_id');
    }

    //########################################

    public function getStatisticTotalCount($listingId)
    {
        $statisticData = $this->getStatisticData();
        if (!isset($statisticData[$listingId]['total'])) {
            return 0;
        }

        return (int)$statisticData[$listingId]['total'];
    }

    //########################################

    public function getStatisticActiveCount($listingId)
    {
        $statisticData = $this->getStatisticData();
        if (!isset($statisticData[$listingId]['active'])) {
            return 0;
        }

        return (int)$statisticData[$listingId]['active'];
    }

    //########################################

    public function getStatisticInactiveCount($listingId)
    {
        $statisticData = $this->getStatisticData();
        if (!isset($statisticData[$listingId]['inactive'])) {
            return 0;
        }

        return (int)$statisticData[$listingId]['inactive'];
    }

    //########################################

    protected function getStatisticData()
    {
        if ($this->_statisticDataCount) {
            return $this->_statisticDataCount;
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $m2eproListing = $structureHelper->getTableNameWithPrefix('m2epro_listing');
        $m2eproWalmartListing = $structureHelper->getTableNameWithPrefix('m2epro_walmart_listing');
        $m2eproListingProduct = $structureHelper->getTableNameWithPrefix('m2epro_listing_product');

        $sql = "SELECT
                    l.id                                           AS listing_id,
                    COUNT(lp.id)                                   AS total,
                    COUNT(CASE WHEN lp.status = 2 THEN lp.id END)  AS active,
                    COUNT(CASE WHEN lp.status != 2 THEN lp.id END) AS inactive
                FROM `{$m2eproListing}` AS `l`
                    INNER JOIN `{$m2eproWalmartListing}` AS `wl` ON l.id = wl.listing_id
                    LEFT JOIN `{$m2eproListingProduct}` AS `lp` ON l.id = lp.listing_id
                GROUP BY listing_id;";

        $result = $connRead->query($sql)->fetchAll();

        $data = array();
        foreach($result as $value){
            $data[$value['listing_id']] = $value;
        }

        return $this->_statisticDataCount = $data;
    }

    //########################################
}
