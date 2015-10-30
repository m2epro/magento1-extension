<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
        var_dump('start ...');

        try {

            /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion630 $migrationInstance */
            $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion630');
            $migrationInstance->setInstaller(new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup'));
            $migrationInstance->setForceAllSteps(true);
            $migrationInstance->migrate();

        } catch (Exception $e) {

            var_dump($e);
            die;
        }

        var_dump('success.');
    }

    /**
     * @title "Fix Ebay Description Templates 6.3.0 [temporary]"
     * @description "Fix Ebay Description Templates 6.3.0 [temporary]"
     */
    public function runFixEbayDescriptionTemplates630Action()
    {
        var_dump('start ...');

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');

        $descriptionTable     = $resource->getTableName('m2epro_template_description');
        $ebayDescriptionTable = $resource->getTableName('m2epro_ebay_template_description');
        $backupTable          = $resource->getTableName('m2epro_bv630_ebay_template_description');

        $resource->getConnection('core_write')->query(<<<SQL

INSERT INTO `{$descriptionTable}`
SELECT
    `id`,
    `title`,
    'ebay',
    `create_date`,
    `update_date`
FROM {$backupTable};

INSERT INTO `{$ebayDescriptionTable}`
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
    `use_supersize_images`,
    `watermark_mode`,
    `watermark_image`,
    `watermark_settings`
FROM {$backupTable};

SQL
        );

        var_dump('success.');
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

    public function enableMaintenanceModeAction()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            Mage::helper('M2ePro/Module_Maintenance')->enable();
        }

        $this->_getSession()->addSuccess('Maintenance was activated.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    public function disableMaintenanceModeAction()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            Mage::helper('M2ePro/Module_Maintenance')->disable();
            Mage::helper('M2ePro/Data_Session')->getValue('warning_message', true);
        }

        $this->_getSession()->addSuccess('Maintenance was deactivated.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDebugTabUrl());
    }

    // ---------------------------------------

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