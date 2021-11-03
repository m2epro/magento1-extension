<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Motors extends Mage_Core_Helper_Abstract
{
    const TYPE_EPID_MOTOR = 1;
    const TYPE_KTYPE      = 2;
    const TYPE_EPID_UK    = 3;
    const TYPE_EPID_DE    = 4;
    const TYPE_EPID_AU    = 5;

    const EPID_SCOPE_MOTORS = 1;
    const EPID_SCOPE_UK     = 2;
    const EPID_SCOPE_DE     = 3;
    const EPID_SCOPE_AU     = 4;

    const PRODUCT_TYPE_VEHICLE    = 0;
    const PRODUCT_TYPE_MOTORCYCLE = 1;
    const PRODUCT_TYPE_ATV        = 2;

    const MAX_ITEMS_COUNT_FOR_ATTRIBUTE = 3000;

    //########################################

    public function getAttribute($type)
    {
        switch ($type) {
            case self::TYPE_EPID_MOTOR:
                return Mage::helper('M2ePro/Component_Ebay_Configuration')->getMotorsEpidsAttribute();

            case self::TYPE_KTYPE:
                return Mage::helper('M2ePro/Component_Ebay_Configuration')->getKTypesAttribute();

            case self::TYPE_EPID_UK:
                return Mage::helper('M2ePro/Component_Ebay_Configuration')->getUkEpidsAttribute();

            case self::TYPE_EPID_DE:
                return Mage::helper('M2ePro/Component_Ebay_Configuration')->getDeEpidsAttribute();

            case self::TYPE_EPID_AU:
                return Mage::helper('M2ePro/Component_Ebay_Configuration')->getAuEpidsAttribute();
        }

        return '';
    }

    //########################################

    public function parseAttributeValue($value)
    {
        $parsedData = array(
            'items' => array(),
            'filters' => array(),
            'groups' => array()
        );

        if (empty($value)) {
            return $parsedData;
        }

        $value = trim($value, ',') . ',';

        preg_match_all(
            '/("?(\d+)"?,)|' .
             '("?(\d+?)"?\|"(.+?)",)|' .
             '("?(ITEM)"?\|"(\d+?)"?\|"(.+?)",)|' .
             '("?(FILTER)"?\|"?(\d+?)"?,)|' .
             '("?(GROUP)"?\|"?(\d+?)"?,)/',
            $value,
            $matches
        );

        $items = array();
        foreach ($matches[0] as $item) {
            $item = explode('|', $item);

            $item[0] = trim(trim($item[0], ','), '"');
            $item[1] = (empty($item[1])) ? '' : trim(trim($item[1], ','), '"');
            $item[2] = (empty($item[2])) ? '' : trim(trim($item[2], ','), '"');

            $items[] = array($item[0],$item[1],$item[2]);
        }

        foreach ($items as $item) {
            if (empty($item[0])) {
                continue;
            }

            if ($item[0] == 'FILTER') {
                if ((empty($item[1]))) {
                    continue;
                }

                if (in_array($item[1], $parsedData['filters'])) {
                    continue;
                }

                $parsedData['filters'][] = $item[1];
            } else if ($item[0] == 'GROUP') {
                if ((empty($item[1]))) {
                    continue;
                }

                if (in_array($item[1], $parsedData['groups'])) {
                    continue;
                }

                $parsedData['groups'][] = $item[1];
            } else {
                if ($item[0] === 'ITEM') {
                    $itemId = $item[1];
                    $itemNote = $item[2];
                } else {
                    $itemId = $item[0];
                    $itemNote = $item[1];
                }

                $parsedData['items'][$itemId]['id'] = $itemId;
                $parsedData['items'][$itemId]['note'] = $itemNote;
            }
        }

        return $parsedData;
    }

    public function buildAttributeValue(array $data)
    {
        $strs = array();

        if (!empty($data['items'])) {
            $strs[] = $this->buildItemsAttributeValue($data['items']);
        }

        if (!empty($data['filters'])) {
            $strs[] = $this->buildFilterAttributeValue($data['filters']);
        }

        if (!empty($data['groups'])) {
            $strs[] = $this->buildGroupAttributeValue($data['groups']);
        }

        return implode(',', $strs);
    }

    // ---------------------------------------

    public function buildItemsAttributeValue(array $items)
    {
        if (empty($items)) {
            return '';
        }

        $values = array();
        foreach ($items as $item) {
            $value = '"ITEM"|"' . $item['id'] . '"';

            $note = trim($item['note']);

            if (!empty($note)) {
                $value .= '|"' . $note . '"';
            }

            $values[] = $value;
        }

        return implode(',', $values);
    }

    public function buildFilterAttributeValue(array $filters)
    {
        if (empty($filters)) {
            return '';
        }

        $values = array();
        foreach ($filters as $id) {
            $values[] = '"FILTER"|"' . $id . '"';
        }

        return implode(',', $values);
    }

    public function buildGroupAttributeValue(array $groups)
    {
        if (empty($groups)) {
            return '';
        }

        $values = array();
        foreach ($groups as $id) {
            $values[] = '"GROUP"|"' . $id . '"';
        }

        return implode(',', $values);
    }

    //########################################

    public function isTypeBasedOnEpids($type)
    {
        if (in_array($type, array(self::TYPE_EPID_MOTOR, self::TYPE_EPID_UK, self::TYPE_EPID_DE, self::TYPE_EPID_AU))) {
            return true;
        }

        return false;
    }

    public function isTypeBasedOnKtypes($type)
    {
        return $type == self::TYPE_KTYPE;
    }

    //########################################

    public function getDictionaryTable($type)
    {
        if ($this->isTypeBasedOnEpids($type)) {
            return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
                'm2epro_ebay_dictionary_motor_epid'
            );
        }

        if ($this->isTypeBasedOnKtypes($type)) {
            return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix(
                'm2epro_ebay_dictionary_motor_ktype'
            );
        }

        return '';
    }

    public function getIdentifierKey($type)
    {
        if ($this->isTypeBasedOnEpids($type)) {
            return 'epid';
        }

        if ($this->isTypeBasedOnKtypes($type)) {
            return 'ktype';
        }

        return '';
    }

    //########################################

    public function getEpidsScopeByType($type)
    {
        switch ($type) {
            case self::TYPE_EPID_MOTOR:
                return self::EPID_SCOPE_MOTORS;

            case self::TYPE_EPID_UK:
                return self::EPID_SCOPE_UK;

            case self::TYPE_EPID_DE:
                return self::EPID_SCOPE_DE;

            case self::TYPE_EPID_AU:
                return self::EPID_SCOPE_AU;

            default:
                return null;
        }
    }

    public function getEpidsTypeByMarketplace($marketplaceId)
    {
        switch ((int)$marketplaceId) {
            case Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS:
                return self::TYPE_EPID_MOTOR;

            case Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK:
                return self::TYPE_EPID_UK;

            case Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE:
                return self::TYPE_EPID_DE;

            case Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_AU:
                return self::TYPE_EPID_AU;

            default:
                return null;
        }
    }

    //########################################

    /**
     * @param int $type
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function getDictionaryRecordCount($type)
    {
        $postfix = Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE == $type ? 'ktype' : 'epid';
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $selectStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                $dbHelper->getTableNameWithPrefix("m2epro_ebay_dictionary_motor_{$postfix}"),
                array(
                    'count' => new Zend_Db_Expr('COUNT(*)'),
                    'is_custom'
                )
            )
            ->group('is_custom');

        if ($this->isTypeBasedOnEpids($type)) {
            $selectStmt->where('scope = ?', $this->getEpidsScopeByType($type));
        }

        $custom = $ebay = 0;
        $queryStmt = $selectStmt->query();
        while ($row = $queryStmt->fetch()) {
            $row['is_custom'] == 1 ? $custom = $row['count'] : $ebay = $row['count'];
        }

        return array((int)$ebay, (int)$custom);
    }

    /**
     * @return bool
     */
    public function isKTypeMarketplacesEnabled()
    {

        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplaceCollection */
        $marketplaceCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
        $marketplaceCollection->addFieldToFilter('is_ktype', 1);
        $marketplaceCollection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        return (bool)$marketplaceCollection->getSize();
    }

    //########################################

    public function getGroupsAssociatedWithFilter($filterId)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_filter_to_group');

        $select = $connRead->select();
        $select->from(array('emftg' => $table), array('group_id'))
            ->where('filter_id = ?', $filterId);

        return Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAssociatedProducts($objectId, $objectType)
    {
        if ($objectType !== 'GROUP' && $objectType !== 'FILTER') {
            throw new Ess_M2ePro_Model_Exception_Logic("Incorrect object type: $objectType");
        }

        $attributesIds = $this->getPartsCompatibilityAttributesIds();
        if (empty($attributesIds)) {
            return array();
        }

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $sqlTemplateLike = "%\"$objectType\"|\"$objectId\"%";

        $attributesIdsTemplate = implode(',', $attributesIds);
        $collection->getSelect()->joinInner(
            array(
                'pet' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('catalog_product_entity_text')
            ),
            '(`pet`.`entity_id` = `e`.`entity_id` AND pet.attribute_id IN(' . $attributesIdsTemplate . ')
                AND value LIKE \'' . $sqlTemplateLike . '\')',
            array('value' => 'pet.value')
        );
        $collection->getSelect()->where('value IS NOT NULL');

        $collection->getSelect()->joinInner(
            array(
                'lp' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('M2ePro/Listing_Product')
            ),
            '(`lp`.`product_id` = `e`.`entity_id` AND `lp`.`component_mode` = "' .
            Ess_M2ePro_Helper_Component_Ebay::NICK . '")',
            array('listing_product_id' => 'lp.id')
        );

        $data = $collection->getData();
        $listingProductIds = array();
        foreach ($data as $product) {
            $listingProductIds[] = $product['listing_product_id'];
        }

        return array_unique($listingProductIds);
    }

    public function resetOnlinePartsData($listingProductIds)
    {
        if (empty($listingProductIds)) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $ebayListingProductTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_listing_product');

        $connWrite->update(
            $ebayListingProductTable,
            array('online_parts_data' => ''),
            array('listing_product_id IN(?)' => $listingProductIds)
        );
    }

    public function getPartsCompatibilityAttributesIds()
    {
        $result = array();
        $keys = array(
            'motors_epids_attribute',
            'uk_epids_attribute',
            'de_epids_attribute',
            'au_epids_attribute',
            'ktypes_attribute'
        );

        /** @var Ess_M2ePro_Model_Config_Manager $config */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        foreach ($keys as $attributeConfigKey) {
            $motorsEpidAttribute = $config->getGroupValue('/ebay/configuration/', $attributeConfigKey);
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $motorsEpidAttribute);

            if ($attributeId = $attribute->getId()) {
                $result[] = $attributeId;
            }
        }

        return $result;
    }

    //########################################
}
