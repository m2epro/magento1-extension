<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Magento
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionMagento');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/magento.phtml');
    }

    // ########################################

    protected function isShown()
    {
        $this->defaultMagentoTimeZone = Mage_Core_Model_Locale::DEFAULT_TIMEZONE;
        return $this->defaultMagentoTimeZone != 'UTC';
    }

    // ########################################
}