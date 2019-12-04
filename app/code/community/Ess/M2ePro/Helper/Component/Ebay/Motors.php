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
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/', 'epids_motor_attribute'
                );

            case self::TYPE_KTYPE:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/', 'ktypes_attribute'
                );

            case self::TYPE_EPID_UK:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/', 'epids_uk_attribute'
                );

            case self::TYPE_EPID_DE:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/', 'epids_de_attribute'
                );

            case self::TYPE_EPID_AU:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motors/', 'epids_au_attribute'
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
}
