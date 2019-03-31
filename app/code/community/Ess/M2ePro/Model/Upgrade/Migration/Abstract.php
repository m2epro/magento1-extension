<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $installer;

    //########################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //########################################

    protected function getFullTableName($tableName)
    {
        return $this->installer->getTablesObject()->getFullName($tableName);
    }

    //########################################
}