<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_Notifications_Content
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationNotifications');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/installation/notifications.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        $breadcrumbBlockHtml = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_wizard_migrationToV6_breadcrumb')
            ->toHtml();

        $registry = Mage::getModel('M2ePro/Registry');
        $html = $registry->load('/wizard/migrationToV6_notes_html/', 'key')->getData('value');

        if (empty($html)) {
            $this->setData('save_migration_notes', true);
            $html = parent::_toHtml();
        }

        if ($this->getData('save_migration_notes')) {
            $registry->setData('key', '/wizard/migrationToV6_notes_html/')
                     ->setData('value', $html);
            $registry->save();
        }

        return $breadcrumbBlockHtml . $html;
    }

    //########################################
}