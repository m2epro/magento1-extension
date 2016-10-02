<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Motors extends Mage_Core_Helper_Abstract
{
    const TYPE_EPID = 1;
    const TYPE_KTYPE = 2;

    const PRODUCT_TYPE_VEHICLE    = 0;
    const PRODUCT_TYPE_MOTORCYCLE = 1;
    const PRODUCT_TYPE_ATV        = 2;

    //########################################

    public function getEpidSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS,
        );
    }

    public function isMarketplaceSupportsEpid($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getEpidSupportedMarketplaces());
    }

    // ---------------------------------------

    public function getKtypeSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_AU,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_IT,
        );
    }

    public function isMarketplaceSupportsKtype($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getKtypeSupportedMarketplaces());
    }

    //########################################

    public function getAttribute($type)
    {
        switch ($type) {
            case self::TYPE_EPID:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/','epids_attribute'
                );

            case self::TYPE_KTYPE:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/','ktypes_attribute'
                );
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

    public function getDictionaryTable($type)
    {
        switch ($type) {
            case self::TYPE_EPID:
                return Mage::getSingleton('core/resource')->getTableName(
                    'm2epro_ebay_dictionary_motor_epid'
                );

            case self::TYPE_KTYPE:
                return Mage::getSingleton('core/resource')->getTableName(
                    'm2epro_ebay_dictionary_motor_ktype'
                );
        }

        return '';
    }

    public function getIdentifierKey($type)
    {
        switch ($type) {
            case self::TYPE_EPID:
                return 'epid';

            case self::TYPE_KTYPE:
                return 'ktype';
        }

        return '';
    }

    //########################################
}