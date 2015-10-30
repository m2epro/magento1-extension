<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayMigrationToV6Breadcrumb');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/breadcrumb.phtml');
    }

    //########################################
}