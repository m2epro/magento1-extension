<?php

class Ess_M2ePro_Sql_Upgrade_v6_5_0_5__v6_5_0_6_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->installer->getTableModifier('amazon_marketplace')
            ->renameColumn('is_asin_available', 'is_new_asin_available', true);

        $this->installer->run(<<<SQL
    UPDATE `m2epro_amazon_marketplace`
    SET `is_new_asin_available` = 1
    WHERE `marketplace_id` = 24;

    UPDATE `m2epro_amazon_marketplace`
    SET `is_new_asin_available` = 0
    WHERE `marketplace_id` IN (27, 32);
SQL
        );

        $this->installer->run(<<<SQL
UPDATE `m2epro_amazon_listing_other`
SET `title` = '--'
WHERE `title` = ''
OR `title` = 'Unknown (can\'t be received)'
OR `title` IS NULL;
SQL
        );
    }

    //########################################
}