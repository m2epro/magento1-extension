<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Setup extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Setup', 'id');
    }

    //########################################

    public function initCurrentSetupObject($versionFrom, $versionTo)
    {
        if (!Mage::helper('M2ePro/Module_Database_Structure')->isTableExists('m2epro_setup')) {
            $this->initTable();
        }

        /** @var Ess_M2ePro_Model_Resource_Setup_Collection $collection */
        $collection = Mage::getModel('M2ePro/Setup')->getCollection();

        empty($versionFrom) ? $collection->addFieldToFilter('version_from', array('null' => true))
                            : $collection->addFieldToFilter('version_from', $versionFrom);

        $collection->addFieldToFilter('version_to', $versionTo);
        $collection->getSelect()->limit(1);

        /** @var Ess_M2ePro_Model_Setup $setupObject */
        $setupObject = $collection->getFirstItem();

        if (!$setupObject->getId()) {
            $setupObject->setData(
                array(
                'version_from' => empty($versionFrom) ? null : $versionFrom,
                'version_to'   => $versionTo,
                'is_backuped'  => 0,
                'is_completed' => 0,
                )
            );
            $setupObject->save();
        }

        return $setupObject;
    }

    protected function initTable()
    {
        $setupTableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_setup');

        $this->_getWriteAdapter()->query(
            <<<SQL
CREATE TABLE IF NOT EXISTS `{$setupTableName}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version_from` VARCHAR(32) DEFAULT NULL,
  `version_to` VARCHAR(32) DEFAULT NULL,
  `is_backuped` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_completed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `profiler_data` LONGTEXT DEFAULT  NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `version_from` (`version_from`),
  INDEX `version_to` (`version_to`),
  INDEX `is_backuped` (`is_backuped`),
  INDEX `is_completed` (`is_completed`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Setup[]
     */
    public function getNotCompletedUpgrades()
    {
        if (!Mage::helper('M2ePro/Module_Database_Structure')->isTableExists('m2epro_setup')) {
            return array();
        }

        $collection = Mage::getModel('M2ePro/Setup')->getCollection();
        $collection->addFieldToFilter('version_from', array('notnull' => true));
        $collection->addFieldToFilter('version_to', array('notnull' => true));
        $collection->addFieldToFilter('is_backuped', 1);
        $collection->addFieldToFilter('is_completed', 0);

        return $collection->getItems();
    }

    public function getMaxCompletedItem()
    {
        /** @var Ess_M2ePro_Model_Resource_Setup_Collection $collection */
        $collection = Mage::getModel('M2ePro/Setup')->getCollection();
        $collection->addFieldToFilter('is_completed', 1);

        /** @var Ess_M2ePro_Model_Setup[] $completedItems */
        $completedItems = $collection->getItems();
        $maxCompletedItem = null;

        foreach ($completedItems as $completedItem) {
            if ($maxCompletedItem === null) {
                $maxCompletedItem = $completedItem;
                continue;
            }

            if (version_compare($maxCompletedItem->getVersionTo(), $completedItem->getVersionTo(), '>')) {
                continue;
            }

            $maxCompletedItem = $completedItem;
        }

        return $maxCompletedItem;
    }

    //########################################
}
