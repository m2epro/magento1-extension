<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    // #################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMigrationToV6Breadcrumb');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/breadcrumb.phtml');
    }

    // #################################################
}