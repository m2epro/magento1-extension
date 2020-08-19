<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_EbayConfig extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $configModifier = $this->_installer->getMainConfigModifier();

        $configModifier->getEntity('/view/ebay/template/selling_format/', 'show_tax_category')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('view_template_selling_format_show_tax_category');

        $configModifier->getEntity('/view/ebay/feedbacks/notification/', 'mode')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('feedback_notification_mode');

        $configModifier->getEntity('/view/ebay/feedbacks/notification/', 'last_check')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('feedback_notification_last_check');

        $configModifier->getEntity('/ebay/description/', 'should_be_ulrs_secure')
            ->updateGroup('/general/configuration/')
            ->updateKey('secure_image_url_in_item_description_mode');

        $configModifier->getEntity('/ebay/description/', 'upload_images_mode')
            ->updateGroup('/ebay/configuration/');

        $configModifier->getEntity('/ebay/motors/', 'epids_motor_attribute')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('motors_epids_attribute');

        $configModifier->getEntity('/ebay/motors/', 'epids_uk_attribute')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('uk_epids_attribute');

        $configModifier->getEntity('/ebay/motors/', 'epids_de_attribute')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('de_epids_attribute');

        $configModifier->getEntity('/ebay/motors/', 'epids_au_attribute')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('au_epids_attribute');

        $configModifier->getEntity('/ebay/motors/', 'ktypes_attribute')
            ->updateGroup('/ebay/configuration/');

        $configModifier->getEntity('/ebay/sell_on_another_marketplace/', 'tutorial_shown')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('sell_on_another_marketplace_tutorial_shown');

        $configModifier->getEntity('/ebay/connector/listing/', 'check_the_same_product_already_listed')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('prevent_item_duplicates_mode');

        $configModifier->getEntity('/component/ebay/variation/', 'mpn_can_be_changed')
            ->updateGroup('/ebay/configuration/')
            ->updateKey('variation_mpn_can_be_changed');
    }

    //########################################
}
