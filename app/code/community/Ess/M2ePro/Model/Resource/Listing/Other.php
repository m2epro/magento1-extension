<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Listing_Other
    extends Ess_M2ePro_Model_Resource_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Other', 'id');
    }

    //########################################

    public function getItemsByProductId($productId, array $filters = array())
    {
        $cacheKey   = __METHOD__.$productId.sha1(Mage::helper('M2ePro')->jsonEncode($filters));
        $cacheValue = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if ($cacheValue !== null) {
            return $cacheValue;
        }

        $select = $this->_getReadAdapter()
            ->select()
            ->from(
                $this->getMainTable(),
                array('id','component_mode')
            )
            ->where("`product_id` IS NOT NULL AND `product_id` = ?", (int)$productId);

        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                $select->where('`'.$column.'` = ?', $value);
            }
        }

        $result = array();

        foreach ($select->query()->fetchAll() as $item) {
            $result[] = Mage::helper('M2ePro/Component')->getComponentObject(
                $item['component_mode'], 'Listing_Other', (int)$item['id']
            );
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $result);

        return $result;
    }

    //########################################
}
