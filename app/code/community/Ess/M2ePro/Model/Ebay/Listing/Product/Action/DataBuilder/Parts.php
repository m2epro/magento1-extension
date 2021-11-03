<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Parts
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if ($this->getEbayListing()->isPartsCompatibilityModeEpids()) {
            $motorsType = Mage::helper('M2ePro/Component_Ebay_Motors')->getEpidsTypeByMarketplace(
                $this->getMarketplace()->getId()
            );
            $tempData = $this->getMotorsData($motorsType);
            $tempData !== false && $data['motors_epids'] = $tempData;
        }

        if ($this->getEbayListing()->isPartsCompatibilityModeKtypes()) {
            $motorsType = Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE;
            $tempData = $this->getMotorsData($motorsType);
            $tempData !== false && $data['motors_ktypes'] = $tempData;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        $attributeValue = '';

        if ($this->getEbayListing()->isPartsCompatibilityModeEpids()) {
            $motorsType = Mage::helper('M2ePro/Component_Ebay_Motors')->getEpidsTypeByMarketplace(
                $this->getMarketplace()->getId()
            );

            $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getMotorsAttribute($motorsType));
        } else if ($this->getEbayListing()->isPartsCompatibilityModeKtypes()) {
            $motorsType = Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE;

            $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getMotorsAttribute($motorsType));
        }

        return $attributeValue ? md5($attributeValue) : null; // @codingStandardsIgnoreLine
    }

    public function getMotorsData($type)
    {
        $attribute = $this->getMotorsAttribute($type);

        if (empty($attribute)) {
            return false;
        }

        $this->searchNotFoundAttributes();

        $rawData = $this->getRawMotorsData($type);

        $attributes = $this->getMagentoProduct()->getNotFoundAttributes();
        if (!empty($attributes)) {
            return array();
        }

        if ($this->getMotorsHelper()->isTypeBasedOnEpids($type)) {
            return $this->getPreparedMotorsEpidsData($rawData);
        }

        if ($this->getMotorsHelper()->isTypeBasedOnKtypes($type)) {
            return $this->getPreparedMotorsKtypesData($rawData);
        }

        return null;
    }

    //########################################

    protected function getRawMotorsData($type)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($this->getMotorsAttribute($type));

        if (empty($attributeValue)) {
            return array();
        }

        $motorsData = $this->getMotorsHelper()->parseAttributeValue($attributeValue);

        $motorsData = array_merge(
            $this->prepareRawMotorsItems($motorsData['items'], $type),
            $this->prepareRawMotorsFilters($motorsData['filters'], $type),
            $this->prepareRawMotorsGroups($motorsData['groups'], $type)
        );

        return $this->filterDuplicatedData($motorsData, $type);
    }

    protected function filterDuplicatedData($motorsData, $type)
    {
        $uniqueItems = array();
        $uniqueFilters = array();
        $uniqueFiltersInfo = array();

        $itemType = $this->getMotorsHelper()->getIdentifierKey($type);

        foreach ($motorsData as $item) {
            if ($item['type'] === $itemType) {
                $uniqueItems[$item['id']] = $item;
                continue;
            }

            if (!in_array($item['info'], $uniqueFiltersInfo)) {
                $uniqueFilters[] = $item;
                $uniqueFiltersInfo[] = $item['info'];
            }
        }

        return array_merge(
            $uniqueItems,
            $uniqueFilters
        );
    }

    // ---------------------------------------

    protected function prepareRawMotorsItems($data, $type)
    {
        if (empty($data)) {
            return array();
        }

        $typeIdentifier = $this->getMotorsHelper()->getIdentifierKey($type);
        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($this->getMotorsHelper()->getDictionaryTable($type))
            ->where(
                '`' . $typeIdentifier . '` IN (?)',
                array_keys($data)
            );

        if ($this->getMotorsHelper()->isTypeBasedOnEpids($type)) {
            $select->where('scope = ?', $this->getMotorsHelper()->getEpidsScopeByType($type));
        }

        $queryStmt = $select->query();

        $existedItems = array();
        while ($row = $queryStmt->fetch()) {
            $existedItems[$row[$typeIdentifier]] = $row;
        }

        foreach ($data as $typeId => $dataItem) {
            $data[$typeId]['type'] = $typeIdentifier;
            $data[$typeId]['info'] = isset($existedItems[$typeId]) ? $existedItems[$typeId] : array();
        }

        return $data;
    }

    protected function prepareRawMotorsFilters($filterIds, $type)
    {
        if (empty($filterIds)) {
            return array();
        }

        $result = array();
        $typeIdentifier = $this->getMotorsHelper()->getIdentifierKey($type);

        $motorFilterCollection = Mage::getModel('M2ePro/Ebay_Motor_Filter')->getCollection();
        $motorFilterCollection->addFieldToFilter('id', array('in' => $filterIds));

        /** @var Ess_M2ePro_Model_Ebay_Motor_Filter $filter */
        foreach ($motorFilterCollection->getItems() as $filter) {
            if ($filter->getType() != $type) {
                continue;
            }

            $conditions = $filter->getConditions();

            $select = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from($this->getMotorsHelper()->getDictionaryTable($type));

            if ($this->getMotorsHelper()->isTypeBasedOnEpids($type)) {
                $select->where('scope = ?', $this->getMotorsHelper()->getEpidsScopeByType($type));
            }

            foreach ($conditions as $key => $value) {
                if ($key != 'year') {
                    $select->where('`' . $key . '` LIKE ?', '%' . $value . '%');
                    continue;
                }

                if ($this->getMotorsHelper()->isTypeBasedOnEpids($type)) {
                    if (!empty($value['from'])) {
                        $select->where('`year` >= ?', $value['from']);
                    }

                    if (!empty($value['to'])) {
                        $select->where('`year` <= ?', $value['to']);
                    }
                } else {
                    $select->where('from_year <= ?', $value);
                    $select->where('to_year >= ?', $value);
                }
            }

            $filterData = $select->query()->fetchAll();

            if (empty($filterData)) {
                $result[] = array(
                    'id'   => $filter->getId(),
                    'type' => 'filter',
                    'note' => $filter->getNote(),
                    'info' => array()
                );
                continue;
            }

            if ($this->getMotorsHelper()->isTypeBasedOnEpids($type)) {
                if ($type == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID_MOTOR) {
                    $filterData = $this->groupEbayMotorsEpidsData($filterData, $conditions);
                }

                foreach ($filterData as $group) {
                    $result[] = array(
                        'id'   => $filter->getId(),
                        'type' => 'filter',
                        'note' => $filter->getNote(),
                        'info' => $group
                    );
                }

                continue;
            }

            foreach ($filterData as $item) {
                if (isset($item[$typeIdentifier])) {
                    $result[] = array(
                        'id'   => $item[$typeIdentifier],
                        'type' => $typeIdentifier,
                        'note' => $filter->getNote(),
                        'info' => $item
                    );
                }
            }
        }

        return $result;
    }

    protected function prepareRawMotorsGroups($groupIds, $type)
    {
        if (empty($groupIds)) {
            return array();
        }

        $result = array();

        $motorGroupCollection = Mage::getModel('M2ePro/Ebay_Motor_Group')->getCollection();
        $motorGroupCollection->addFieldToFilter('id', array('in' => $groupIds));

        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $group */
        foreach ($motorGroupCollection->getItems() as $group) {
            if ($group->getType() != $type) {
                continue;
            }

            if ($group->isModeItem()) {
                $items = $this->prepareRawMotorsItems($group->getItems(), $type);
            } else {
                $items = $this->prepareRawMotorsFilters($group->getFiltersIds(), $type);
            }

            $result = array_merge($result, $items);
        }

        return $result;
    }

    //########################################

    protected function getPreparedMotorsEpidsData($data)
    {
        $ebayAttributes = $this->getEbayMotorsEpidsAttributes();

        $preparedData = array();
        $emptySavedItems = array();

        foreach ($data as $item) {
            if (empty($item['info'])) {
                $emptySavedItems[$item['type']][] = $item;
                continue;
            }

            $motorsList = array();
            $motorsData = $this->buildEpidData($item['info']);

            foreach ($motorsData as $key => $value) {
                if ($value == '--') {
                    unset($motorsData[$key]);
                    continue;
                }

                $name = $key;

                foreach ($ebayAttributes as $ebayAttribute) {
                    if ($ebayAttribute['title'] == $key) {
                        $name = $ebayAttribute['ebay_id'];
                        break;
                    }
                }

                $motorsList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $preparedData[] = array(
                'epid' => isset($item['info']['epid']) ? $item['info']['epid'] : null,
                'list' => $motorsList,
                'note' => $item['note'],
            );
        }

        if (!empty($emptySavedItems['epid'])) {
            $tempItems = array();
            foreach ($emptySavedItems['epid'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some ePID(s) which were saved in Parts Compatibility Magento Attribute
                have been removed. Their Values were ignored and not sent on eBay',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['filter'])) {
            $tempItems = array();
            foreach ($emptySavedItems['filter'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some ePID(s) Grid Filter(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['group'])) {
            $tempItems = array();
            foreach ($emptySavedItems['group'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some ePID(s) Group(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    protected function getPreparedMotorsKtypesData($data)
    {
        $preparedData = array();
        $emptySavedItems = array();

        foreach ($data as $item) {
            if (empty($item['info'])) {
                $emptySavedItems[$item['type']][] = $item;
                continue;
            }

            $preparedData[] = array(
                'ktype' => $item['id'],
                'note'  => $item['note'],
            );
        }

        if (!empty($emptySavedItems['ktype'])) {
            $tempItems = array();
            foreach ($emptySavedItems['ktype'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some kTypes(s) which were saved in Parts Compatibility Magento Attribute
                have been removed. Their Values were ignored and not sent on eBay',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['filter'])) {
            $tempItems = array();
            foreach ($emptySavedItems['filter'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some kTypes(s) Grid Filter(s) was removed, that is why its Settings
                were ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        if (!empty($emptySavedItems['group'])) {
            $tempItems = array();
            foreach ($emptySavedItems['group'] as $tempItem) {
                $tempItems[] = $tempItem['id'];
            }

            $msg = Mage::helper('M2ePro')->__(
                '
                Some kTypes(s) Group(s) was removed, that is why its Settings were
                ignored and can not be applied',
                implode(', ', $tempItems)
            );
            $this->addWarningMessage($msg);
        }

        return $preparedData;
    }

    // ---------------------------------------

    protected function groupEbayMotorsEpidsData($data, $condition)
    {
        $groupingFields = array_unique(
            array_merge(
                array('year', 'make', 'model'),
                array_keys($condition)
            )
        );

        $groups = array();
        foreach ($data as $item) {
            if (empty($groups)) {
                $group = array();
                foreach ($groupingFields as $groupingField) {
                    $group[$groupingField] = $item[$groupingField];
                }

                ksort($group);

                $groups[] = $group;
                continue;
            }

            $newGroup = array();
            foreach ($groupingFields as $groupingField) {
                $newGroup[$groupingField] = $item[$groupingField];
            }

            ksort($newGroup);

            if (!in_array($newGroup, $groups)) {
                $groups[] = $newGroup;
            }
        }

        return $groups;
    }

    protected function buildEpidData($resource)
    {
        $motorsData = array();

        if (isset($resource['make'])) {
            $motorsData['Make'] = $resource['make'];
        }

        if (isset($resource['model'])) {
            $motorsData['Model'] = $resource['model'];
        }

        if (isset($resource['year'])) {
            $motorsData['Year'] = $resource['year'];
        }

        if (isset($resource['submodel'])) {
            $motorsData['Submodel'] = $resource['submodel'];
        }

        if (isset($resource['trim'])) {
            $motorsData['Trim'] = $resource['trim'];
        }

        if (isset($resource['engine'])) {
            $motorsData['Engine'] = $resource['engine'];
        }

        if (isset($resource['street_name'])) {
            $motorsData['StreetName'] = $resource['street_name'];
        }

        return $motorsData;
    }

    protected function getEbayMotorsEpidsAttributes()
    {
        $categoryId = $this->getCategorySource()->getCategoryId();
        $categoryData = $this->getEbayMarketplace()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ?
            (array)Mage::helper('M2ePro')->jsonDecode($categoryData['features']) : array();

        $attributes = !empty($features['parts_compatibility_attributes']) ?
            $features['parts_compatibility_attributes'] : array();

        return $attributes;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motors
     */
    protected function getMotorsHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors');
    }

    protected function getMotorsAttribute($type)
    {
        return $this->getMotorsHelper()->getAttribute($type);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    protected function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }
}
