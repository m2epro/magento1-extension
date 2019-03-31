<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Walmart extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Sell On Walmart

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

    public function getPageNavigationPath($pathNick, $tabName = NULL, $additionalEnd = NULL)
    {
        return Mage::helper('M2ePro/View')->getPageNavigationPath(
            self::NICK .'/'. $pathNick, $tabName, $additionalEnd
        );
    }

    //########################################

    public function getAutocompleteMaxItems()
    {
        $temp = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/walmart/autocomplete/','max_records_quantity'
        );
        return $temp <= 0 ? 100 : $temp;
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
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Session');

        if (!is_null($sessionCache->getValue('is_3rd_party_should_be_shown'))) {
            return $sessionCache->getValue('is_3rd_party_should_be_shown');
        }

        $accountCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        $accountCollection->addFieldToFilter(
            'other_listings_synchronization', Ess_M2ePro_Model_Walmart_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES
        );

        if ((bool)$accountCollection->getSize()) {
            $result = true;
        } else {
            $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');

            $logCollection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
            $logCollection->addFieldToFilter(
                'component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK
            );

            $result = $collection->getSize() || $logCollection->getSize();
        }

        $sessionCache->setValue('is_3rd_party_should_be_shown', $result);

        return $result;
    }

    //########################################

    public function isResetFilterShouldBeShown($listingId, $isVariation = false)
    {
        $sessionKey = 'is_reset_filter_should_be_shown_' . (int)$listingId . '_' . (int)$isVariation;
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Session');

        if (is_null($sessionCache->getValue($sessionKey))) {

            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
            $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $collection->addFieldToFilter('is_online_price_invalid', 0)
                       ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
                       ->addFieldToFilter('listing_id', $listingId);

            if ($isVariation) {
                $collection->addFieldToFilter('is_variation_product', 1);
            }
            $sessionCache->setValue($sessionKey, (bool)$collection->getSize());
        }

        return $sessionCache->getValue($sessionKey);
    }

    //########################################
}