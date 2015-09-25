<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility extends Mage_Core_Helper_Abstract
{
    const TYPE_SPECIFIC   = 'specific';
    const TYPE_KTYPE      = 'ktype';

    const PRODUCT_TYPE_VEHICLE    = 0;
    const PRODUCT_TYPE_MOTORCYCLE = 1;
    const PRODUCT_TYPE_ATV        = 2;

    // ##########################################################

    public function getSpecificSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS,
        );
    }

    public function isMarketplaceSupportsSpecific($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getSpecificSupportedMarketplaces());
    }

    // ----------------------------------------------------------

    public function getKtypeSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_AU,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE,
        );
    }

    public function isMarketplaceSupportsKtype($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getKtypeSupportedMarketplaces());
    }

    // ##########################################################

    public function getAttribute($type)
    {
        switch ($type) {
            case self::TYPE_SPECIFIC:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motor/','motors_specifics_attribute'
                );

            case self::TYPE_KTYPE:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motor/','motors_ktypes_attribute'
                );
        }

        return '';
    }

    public function parseAttributeValue($value)
    {
        if (empty($value)) {
            return array();
        }

        $value = trim($value, ',') . ',';

        preg_match_all('/("?(\d+?)"?\|"(.+?)",)|("?(\d+)"?,)/', $value, $matches);

        $identifiersWithNotes = array_combine($matches[2], $matches[3]);
        $identifiersWithoutNotes = $matches[5];

        $parsedData = array();

        foreach ($identifiersWithNotes as $identifier => $note) {
            if (empty($identifier) || empty($note)) {
                continue;
            }

            $parsedData[$identifier] = array(
                'id'   => $identifier,
                'note' => $note,
            );
        }

        foreach ($identifiersWithoutNotes as $identifier) {
            if (empty($identifier) || isset($parsedData[$identifier])) {
                continue;
            }

            $parsedData[$identifier] = array(
                'id'   => $identifier,
                'note' => '',
            );
        }

        return $parsedData;
    }

    public function buildAttributeValue(array $data)
    {
        if (empty($data)) {
            return '';
        }

        $value = '';
        foreach ($data as $item) {
            if (empty($item) || empty($item['id'])) {
                continue;
            }

            $value .= '"' . $item['id'] . '"';

            $note = trim($item['note']);

            if (!empty($note)) {
                $value .= '|"' . $note . '"';
            }

            $value .= ',';
        }

        return $value;
    }

    // ##########################################################

    public function getDictionaryTable($type)
    {
        switch ($type) {
            case self::TYPE_SPECIFIC:
                return Mage::getSingleton('core/resource')->getTableName(
                    'm2epro_ebay_dictionary_motor_specific'
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
            case self::TYPE_SPECIFIC:
                return 'epid';

            case self::TYPE_KTYPE:
                return 'ktype';
        }

        return '';
    }

    // ##########################################################
}