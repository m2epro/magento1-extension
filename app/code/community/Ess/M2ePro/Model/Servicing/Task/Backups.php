<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Backups extends Ess_M2ePro_Model_Servicing_Task
{
    const MAX_ALLOWED_ITEMS_PER_REQUEST = 10000;

    /** @var Ess_M2ePro_Model_Servicing_Task_Backups_Manager */
    private $backup = null;

    //########################################

    public function __construct()
    {
        $this->backup = Mage::getSingleton('M2ePro/Servicing_Task_Backups_Manager');
    }

    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'backups';
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        $requestData = array('tables' => array());

        $totalItems = 0;

        foreach (Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables() as $tableName) {
            if (!$this->backup->canBackupTable($tableName) || !$this->backup->isTimeToBackupTable($tableName)) {
                continue;
            }

            $dump = $this->backup->getTableDump($tableName);
            $requestData['tables'][$tableName] = $dump;

            $this->backup->updateTableLastAccessDate($tableName);

            $totalItems += count($dump);

            if ($totalItems >= self::MAX_ALLOWED_ITEMS_PER_REQUEST) {
                break;
            }
        }

        return $requestData;
    }

    public function processResponseData(array $data)
    {
        $this->backup->deleteSettings();

        if (isset($data['settings']) && is_array($data['settings'])) {
            $this->backup->setSettings($data['settings']);
        }
    }

    //########################################
}