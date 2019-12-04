<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Ebay extends Mage_Core_Helper_Abstract
{
    const NICK  = 'ebay';

    const WIZARD_INSTALLATION_NICK = 'installationEbay';
    const MENU_ROOT_NODE_NICK = 'm2epro/ebay';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('eBay');
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
        return Mage::helper('M2ePro/Module_Wizard')->isFinished(
            $this->getWizardInstallationNick()
        );
    }

    //########################################

    public function isFeedbacksShouldBeShown($accountId = null)
    {
        $accountCollection = Mage::getModel('M2ePro/Ebay_Account')->getCollection();
        $accountCollection->addFieldToFilter('feedbacks_receive', 1);

        $feedbackCollection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection();

        if ($accountId !== null) {
            $accountCollection->addFieldToFilter(
                'account_id', $accountId
            );
            $feedbackCollection->addFieldToFilter(
                'account_id', $accountId
            );
        }

        return $accountCollection->getSize() || $feedbackCollection->getSize();
    }

    public function is3rdPartyShouldBeShown()
    {
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Runtime');

        if ($sessionCache->getValue('is_3rd_party_should_be_shown') !== null) {
            return $sessionCache->getValue('is_3rd_party_should_be_shown');
        }

        $accountCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountCollection->addFieldToFilter('other_listings_synchronization', 1);

        if ((bool)$accountCollection->getSize()) {
            $result = true;
        } else {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
            $result = $collection->getSize();
        }

        $sessionCache->setValue('is_3rd_party_should_be_shown', $result);

        return $result;
    }

    //----------------------------------------

    public function isDuplicatesFilterShouldBeShown($listingId = null)
    {
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Runtime');

        if ($sessionCache->getValue('is_duplicates_filter_should_be_shown') !== null) {
            return $sessionCache->getValue('is_duplicates_filter_should_be_shown');
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('is_duplicate', 1);
        $listingId && $collection->addFieldToFilter('listing_id', (int)$listingId);

        $result = (bool)$collection->getSize();
        $sessionCache->setValue('is_duplicates_filter_should_be_shown', $result);

        return $result;
    }

    //########################################
}
