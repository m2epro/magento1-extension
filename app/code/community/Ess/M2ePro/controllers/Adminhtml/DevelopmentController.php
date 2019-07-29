<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_DevelopmentController
    extends Ess_M2ePro_Controller_Adminhtml_Development_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
                ->getLayout()
                ->getBlock('head')
                ->addJs('M2ePro/Plugin/DropDown.js')
                ->addCss('M2ePro/css/Plugin/DropDown.css');

        return $this;
    }

    /**
     * @title "First Test"
     * @description "Command for quick development"
     */
    public function firstTestAction()
    {

    }

    /**
     * @title "Second Test"
     * @description "Command for quick development"
     */
    public function secondTestAction()
    {

    }

    //########################################

    /**
     * @title "Force run migration 6.3.0 [temporary]"
     * @description "Force run migration 6.3.0"
     */
    public function runMigrationForce630Action()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630 $migrationInstance */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630');
        $migrationInstance->setInstaller(new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup'));
        $migrationInstance->setForceAllSteps(true);
        $migrationInstance->migrate();

        return $this->getResponse()->setBody('success');
    }

    /**
     * @title "Fix Ebay Description Templates 6.3.0 [temporary]"
     * @description "Fix Ebay Description Templates 6.3.0 [temporary]"
     */
    public function runFixEbayDescriptionTemplates630Action()
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $descriptionTable     = $dbHelper->getTableNameWithPrefix('m2epro_template_description');
        $ebayDescriptionTable = $dbHelper->getTableNameWithPrefix('m2epro_ebay_template_description');
        $backupTable          = $dbHelper->getTableNameWithPrefix('m2epro_bv630_ebay_template_description');

        $resource->getConnection('core_write')->query(<<<SQL

INSERT INTO `{$descriptionTable}` (
`id`,
`title`,
`component_mode`,
`create_date`,
`update_date`
)
SELECT
    `id`,
    `title`,
    'ebay',
    `create_date`,
    `update_date`
FROM {$backupTable};

SQL
        );

        $resource->getConnection('core_write')->query(<<<SQL

INSERT INTO `{$ebayDescriptionTable}` (
`template_description_id`,
`is_custom_template`,
`title_mode`,
`title_template`,
`subtitle_mode`,
`subtitle_template`,
`description_mode`,
`description_template`,
`condition_mode`,
`condition_value`,
`condition_attribute`,
`condition_note_mode`,
`condition_note_template`,
`product_details`,
`cut_long_titles`,
`hit_counter`,
`editor_type`,
`enhancement`,
`gallery_type`,
`image_main_mode`,
`image_main_attribute`,
`gallery_images_mode`,
`gallery_images_limit`,
`gallery_images_attribute`,
`default_image_url`,
`variation_configurable_images`,
`use_supersize_images`
)
SELECT
    `id`,
    `is_custom_template`,
    `title_mode`,
    `title_template`,
    `subtitle_mode`,
    `subtitle_template`,
    `description_mode`,
    `description_template`,
    `condition_mode`,
    `condition_value`,
    `condition_attribute`,
    `condition_note_mode`,
    `condition_note_template`,
    `product_details`,
    `cut_long_titles`,
    `hit_counter`,
    `editor_type`,
    `enhancement`,
    `gallery_type`,
    `image_main_mode`,
    `image_main_attribute`,
    `gallery_images_mode`,
    `gallery_images_limit`,
    `gallery_images_attribute`,
    `default_image_url`,
    `variation_configurable_images`,
    `use_supersize_images`
FROM {$backupTable};

SQL
        );

        return $this->getResponse()->setBody('success');
    }

    //########################################

    /**
     * @title "Fix getOptions() on non object"
     * @description "Amazon [temporary]"
     */
    public function runFixAmazonGetOptionsOnNonObjectAction()
    {
        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $alp = $dbHelper->getTableNameWithPrefix('m2epro_amazon_listing_product');
        $lpv = $dbHelper->getTableNameWithPrefix('m2epro_listing_product_variation');

        $resource->getConnection('core_write')->query(<<<SQL

UPDATE `{$alp}` `malp`
   LEFT JOIN `{$lpv}` `mlpv` ON `mlpv`.`listing_product_id` = `malp`.`listing_product_id`
SET `malp`.`is_variation_product_matched` = 0
WHERE `malp`.`is_variation_product` = 1 AND
      `malp`.`is_variation_product_matched` = 1 AND
      `mlpv`.`id` IS NULL;

SQL
        );

        return $this->getResponse()->setBody('success');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_development'))
                ->renderLayout();
    }

    //########################################

    public function summaryTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_summary')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function aboutTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_about')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function databaseTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    //########################################

    public function enableDevelopmentModeAction()
    {
        Mage::helper('M2ePro/Module')->setDevelopmentModeMode(true);

        $this->_getSession()->addSuccess('Development mode has been Enabled.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    public function disableDevelopmentModeAction()
    {
        Mage::helper('M2ePro/Module')->setDevelopmentModeMode(false);

        $this->_getSession()->addSuccess('Development mode has been Disabled.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    //########################################
}