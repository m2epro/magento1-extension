<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

// Recommended value specifics migration
//########################################

$listingTable = $installer->getTablesObject()->getFullName('listing');
$listings = $installer->getConnection()->query("
  SELECT * FROM {$listingTable} WHERE `additional_data` LIKE '%mode_same_category_data%';
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($listings as $listing) {

    $listingId = $listing['id'];
    $additionalData = (array)@json_decode($listing['additional_data'], true);
    $hasOldStructure = false;

    if (!empty($additionalData['mode_same_category_data']['specifics'])) {

        foreach ($additionalData['mode_same_category_data']['specifics'] as &$specific) {

            if (!empty($specific['value_ebay_recommended'])) {

                $recommendedValues = (array)@json_decode($specific['value_ebay_recommended'], true);

                if (empty($recommendedValues)) {
                    continue;
                }

                foreach ($recommendedValues as &$recommendedValue) {
                    if (!empty($recommendedValue['value'])) {
                        $recommendedValue = $recommendedValue['value'];
                        $hasOldStructure = true;
                    }
                }
                unset($recommendedValue);

                $specific['value_ebay_recommended'] = json_encode($recommendedValues);
            }
        }
        unset($specific);
    }

    if (!$hasOldStructure) {
        continue;
    }

    $connection->update(
        $listingTable,
        array('additional_data' => json_encode($additionalData)),
        array('id = ?' => $listingId)
    );
}

//########################################