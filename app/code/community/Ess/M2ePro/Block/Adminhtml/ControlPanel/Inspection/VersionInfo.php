<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_VersionInfo
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspectionVersionInfo');
        $this->setTemplate('M2ePro/controlPanel/inspection/versionInfo.phtml');

        $this->prepareInfo();
    }

    //########################################

    protected function prepareInfo()
    {
        $this->currentVersion = Mage::helper('M2ePro/Module')->getPublicVersion();

        $this->latestPublicVersion = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            '/installation/public_last_version/'
        );

        $this->latestVersion = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            '/installation/build_last_version/'
        );
    }

    //########################################
}
