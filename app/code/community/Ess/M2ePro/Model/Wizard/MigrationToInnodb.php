<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_MigrationToInnodb extends Ess_M2ePro_Model_Wizard
{
    protected $_steps = array(
        'marketplacesSynchronization'
    );

    //########################################

    /**
     * @return string
     */
    public function getNick()
    {
        return 'migrationToInnodb';
    }

    //########################################

    public function isActive($view)
    {
        if ($view === null) {
            return true;
        }

        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        $collection->addFieldToFilter('component_mode', $view);

        foreach ($collection->getItems() as $marketplace) {
            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            if (!$marketplace->getResource()->isDictionaryExist($marketplace)) {
                return true;
            }
        }

        return false;
    }

    //########################################

    public function rememberRefererUrl($url)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/wizard/migration_to_innodb/referer_url/');
        $registry->setValue($url);
        $registry->save();
    }

    public function getRefererUrl()
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/wizard/migration_to_innodb/referer_url/');
        return $registry->getValue();
    }

    public function clearRefererUrl()
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/wizard/migration_to_innodb/referer_url/');
        if ($registry->getId()) {
            $registry->delete();
        }
    }

    //########################################
}
