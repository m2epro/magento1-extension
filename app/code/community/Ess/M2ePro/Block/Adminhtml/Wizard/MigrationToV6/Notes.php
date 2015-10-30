<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Notes extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayMigrationToV6Breadcrumb');
        // ---------------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__(
            'M2E Pro Migration to v. %version%', Mage::helper('M2ePro/Module')->getVersion()
        );

        $this->setTemplate('widget/form/container.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        $html = Mage::getModel('M2ePro/Registry')->load('/wizard/migrationToV6_notes_html/', 'key')
                                                 ->getData('value');

        return parent::_toHtml() . $html;
    }

    //########################################
}