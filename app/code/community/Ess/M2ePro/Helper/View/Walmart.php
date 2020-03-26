<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Walmart extends Mage_Core_Helper_Abstract
{
    const NICK  = 'walmart';

    const WIZARD_INSTALLATION_NICK = 'installationWalmart';
    const MENU_ROOT_NODE_NICK = 'm2epro/walmart';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Walmart');
    }

    //########################################

    public function getMenuRootNodeLabel()
    {
        return $this->getTitle();
    }

    //########################################

    public function getPageNavigationPath($pathNick, $tabName = null, $additionalEnd = null)
    {
        return Mage::helper('M2ePro/View')->getPageNavigationPath(
            self::NICK .'/'. $pathNick, $tabName, $additionalEnd
        );
    }

    //########################################

    public function getWizardInstallationNick()
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    public function isInstallationWizardFinished()
    {
        return Mage::helper('M2ePro/Module_Wizard')->isFinished($this->getWizardInstallationNick());
    }

    //########################################

    public function is3rdPartyShouldBeShown()
    {
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Runtime');

        if ($sessionCache->getValue('is_3rd_party_should_be_shown') !== null) {
            return $sessionCache->getValue('is_3rd_party_should_be_shown');
        }

        $accountCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        $accountCollection->addFieldToFilter('other_listings_synchronization', 1);

        if ((bool)$accountCollection->getSize()) {
            $result = true;
        } else {
            $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
            $result = $collection->getSize();
        }

        $sessionCache->setValue('is_3rd_party_should_be_shown', $result);

        return $result;
    }

    //########################################

    /**
     * @param string $key
     * @param int $id
     * @param bool $isVariation
     *
     * @return bool
     */
    public function isResetFilterShouldBeShown($key, $id)
    {
        $sessionKey = "is_reset_filter_should_be_shown_{$key}_" . (int)$id;

        $sessionCache = Mage::helper('M2ePro/Data_Cache_Runtime');
        if ($sessionCache->getValue($sessionKey) !== null) {
            return $sessionCache->getValue($sessionKey);
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter($key, $id)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
            ->addFieldToFilter('is_online_price_invalid', 0);

        return $sessionCache->setValue($sessionKey, (bool)$collection->getSize());
    }

    //########################################
}
