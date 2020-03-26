<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_DropEbayTranslations extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_listing_product')
            ->dropColumn('translation_status', true, false)
            ->dropColumn('translation_service', true, false)
            ->dropColumn('translated_date', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_marketplace')
            ->dropColumn('translation_service_mode', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_account')
            ->dropColumn('translation_hash', true, false)
            ->dropColumn('translation_info', true, false)
            ->commit();

        $this->_installer->getMainConfigModifier()->delete('/ebay/translation_services/gold/');
        $this->_installer->getMainConfigModifier()->delete('/ebay/translation_services/silver/');
        $this->_installer->getMainConfigModifier()->delete('/ebay/translation_services/platinum/');
    }

    //########################################
}
