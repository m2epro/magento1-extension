<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m10_PartsCompatibilityImprovement extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    private $ebayMarketplacesCache = array();

    //########################################

    public function execute()
    {
        $isReviseUpdatePartsColumnExists = $this->_installer->getTableModifier('ebay_template_synchronization')
            ->isColumnExists('revise_update_parts');

        if ($isReviseUpdatePartsColumnExists) {
            return;
        }

        //----------------------------------------

        $this->clearUnnecessaryData();
        $this->modifyDBScheme();

        //----------------------------------------

        $motorsAttributesKeys = array(
            'motors_epids_attribute',
            'uk_epids_attribute',
            'de_epids_attribute',
            'au_epids_attribute',
            'ktypes_attribute'
        );

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('m2epro_config'))
            ->where('`group` = ?', '/ebay/configuration/')
            ->where('`key` IN (?)', $motorsAttributesKeys)
            ->query();

        $motorsAttributes = array();
        while ($row = $query->fetch()) {
            if ($row['value']) {
                $motorsAttributes[$row['key']] = $row['value'];
            }
        }

        //----------------------------------------

        $listingsStmt = $this->_installer->getConnection()
            ->select()
            ->from(
                array(
                    'l' => $this->_installer->getFullTableName('m2epro_listing')
                ),
                array('id', 'marketplace_id')
            )
            ->joinInner(
                array('el' => $this->_installer->getFullTableName('m2epro_ebay_listing')),
                'l.id = el.listing_id',
                array('parts_compatibility_mode')
            )
            ->query();

        $listingsInfo = array();
        while ($row = $listingsStmt->fetch()) {
            $marketplaceId = $row['marketplace_id'];
            if (!isset($listingsInfo[$marketplaceId])) {
                $listingsInfo[$marketplaceId] = array();
            }

            $partsCompatibilityMode = $row['parts_compatibility_mode'];
            if (!isset($listingsInfo[$marketplaceId][$partsCompatibilityMode])) {
                $listingsInfo[$marketplaceId][$partsCompatibilityMode] = array();
            }

            $listingsInfo[$marketplaceId][$partsCompatibilityMode][] = $row['id'];
        }

        foreach ($listingsInfo as $marketplaceId => $marketplaceInfo) {
            foreach ($marketplaceInfo as $partsCompatibilityMode => $listingsIds) {
                $attributeConfigKey = $this->getPartsCompatibilityAttributeConfigKey(
                    $marketplaceId,
                    $partsCompatibilityMode
                );
                if (!$attributeConfigKey) {
                    continue;
                }

                if (!isset($motorsAttributes[$attributeConfigKey]) || !$motorsAttributes[$attributeConfigKey]) {
                    continue;
                }

                $attributeCode = $motorsAttributes[$attributeConfigKey];
                $template = implode(',', $listingsIds);

                $this->_installer->run(
                    <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_listing_product')}` AS `main`
    INNER JOIN `{$this->_installer->getTable('m2epro_listing_product')}` AS `lp`
        ON lp.id = main.listing_product_id
    INNER JOIN `{$this->_installer->getTable('m2epro_listing')}` AS `l` ON lp.listing_id = l.id
        AND l.id IN ({$template})
    INNER JOIN `{$this->_installer->getTable('m2epro_ebay_listing')}` AS `al` ON al.listing_id = lp.listing_id
    INNER JOIN `{$this->_installer->getTable('eav_attribute')}` AS `ea`
        ON ea.attribute_code = '$attributeCode'
    LEFT JOIN `{$this->_installer->getTable('catalog_product_entity_text')}` AS `cpev_default`
        ON cpev_default.attribute_id = ea.attribute_id
            AND cpev_default.entity_id=lp.product_id AND cpev_default.store_id = 0
    LEFT JOIN `{$this->_installer->getTable('catalog_product_entity_text')}` AS `cpev`
        ON cpev.attribute_id = ea.attribute_id AND cpev.entity_id=lp.product_id AND cpev.store_id = l.store_id
SET main.online_parts_data = MD5(IFNULL(cpev.value, cpev_default.value))
WHERE IFNULL(cpev.value, cpev_default.value) IS NOT NULL;
SQL
                );
            }
        }
    }

    //----------------------------------------

    private function modifyDBScheme()
    {
        $this->_installer
            ->getTableModifier('ebay_listing_product')
            ->addColumn(
                'online_parts_data',
                'VARCHAR(32)',
                null,
                'online_categories_data',
                false,
                false
            )
            ->commit();

        $this->_installer
            ->getTableModifier('ebay_template_synchronization')
            ->addColumn(
                'revise_update_parts',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'revise_update_categories',
                false,
                false
            )
            ->commit();

        $this->_installer->run(
            <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_template_synchronization')}`
SET `revise_update_parts` = `revise_update_categories`;
SQL
        );
    }

    //----------------------------------------

    private function clearUnnecessaryData()
    {
        // clear unnecessary data from online_categories_data (motors_epids)
        $this->_installer->run(
            <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_listing_product')}`
SET online_categories_data = CASE
    WHEN INSTR(online_categories_data, 'motors_epids') > 0
    THEN CONCAT(SUBSTRING(online_categories_data, 1, INSTR(online_categories_data, 'motors_epids')-3), '}')
    ELSE online_categories_data
END
WHERE online_categories_data IS NOT NULL;
SQL
        );

        // clear unnecessary data from online_categories_data (motors_ktypes)
        $this->_installer->run(
            <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_listing_product')}`
SET online_categories_data = CASE
    WHEN INSTR(online_categories_data, 'motors_ktypes') > 0
    THEN CONCAT(SUBSTRING(online_categories_data, 1, INSTR(online_categories_data, 'motors_ktypes')-3), '}')
    ELSE online_categories_data
END
WHERE online_categories_data IS NOT NULL;
SQL
        );
    }

    //########################################

    private function getPartsCompatibilityAttributeConfigKey($marketplaceId, $partsCompatibilityMode)
    {
        // https://docs.m2epro.com/help/m1/ebay-integration/parts-compatibility
        // motors_epids_attribute, uk_epids_attribute, de_epids_attribute, au_epids_attribute, ktypes_attribute
        $this->initEbayMarketplacesCache();

        if ($marketplaceId === Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return 'motors_epids_attribute';
        } else if ($marketplaceId === Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK) {
            if ($partsCompatibilityMode == Ess_M2ePro_Model_Ebay_Listing::PARTS_COMPATIBILITY_MODE_EPIDS) {
                return 'uk_epids_attribute';
            } else {
                return 'ktypes_attribute';
            }
        } else {
            if (isset($this->ebayMarketplacesCache[$marketplaceId])) {
                if ($this->ebayMarketplacesCache[$marketplaceId]['is_epid'] &&
                    $this->ebayMarketplacesCache[$marketplaceId]['is_ktype']
                ) {
                    return $partsCompatibilityMode === Ess_M2ePro_Model_Ebay_Listing::PARTS_COMPATIBILITY_MODE_EPIDS ?
                        $this->ebayMarketplacesCache[$marketplaceId]['origin_country'] . '_epids_attribute'
                        : 'ktypes_attribute';
                } else {
                    return $this->ebayMarketplacesCache[$marketplaceId]['is_epid'] ?
                        $this->ebayMarketplacesCache[$marketplaceId]['origin_country'] . '_epids_attribute'
                        : 'ktypes_attribute';
                }
            } else {
                return null;
            }
        }
    }

    private function initEbayMarketplacesCache()
    {
        if (!empty($this->ebayMarketplacesCache)) {
            return;
        }

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('m2epro_ebay_marketplace'))
            ->where('`is_epid` = 1 OR `is_ktype` = 1')
            ->query();

        while ($row = $query->fetch()) {
            $this->ebayMarketplacesCache[$row['marketplace_id']] = array(
                'origin_country' => $row['origin_country'],
                'is_epid' => $row['is_epid'],
                'is_ktype' => $row['is_ktype'],
            );
        }
    }
}
