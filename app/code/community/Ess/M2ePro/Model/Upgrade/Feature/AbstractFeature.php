<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer;

    //########################################

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer = $installer;
    }

    //########################################

    public function getBackupTables()
    {
        return array();
    }

    abstract public function execute();

    //########################################
}
