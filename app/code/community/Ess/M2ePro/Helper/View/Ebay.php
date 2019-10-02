<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Ebay extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Sell On eBay

    const NICK  = 'ebay';

    const WIZARD_INSTALLATION_NICK = 'installationEbay';
    const MENU_ROOT_NODE_NICK = 'm2epro/ebay';

    const MODE_SIMPLE = 'simple';
    const MODE_ADVANCED = 'advanced';

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

    public function getPageNavigationPath($pathNick, $tabName = NULL, $additionalEnd = NULL)
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

    public function getMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/ebay/', 'mode');
    }

    public function setMode($mode)
    {
        $mode = strtolower($mode);
        if (!in_array($mode, array(self::MODE_SIMPLE,self::MODE_ADVANCED))) {
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/view/ebay/', 'mode', $mode);
    }

    // ---------------------------------------

    public function isSimpleMode()
    {
        return $this->getMode() == self::MODE_SIMPLE;
    }

    public function isAdvancedMode()
    {
        return $this->getMode() == self::MODE_ADVANCED;
    }

    //########################################

    public function isFeedbacksShouldBeShown($accountId = NULL)
    {
        $accountCollection = Mage::getModel('M2ePro/Ebay_Account')->getCollection();
        $accountCollection->addFieldToFilter(
            'feedbacks_receive', Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_RECEIVE_YES
        );

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
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Session');

        if ($sessionCache->getValue('is_3rd_party_should_be_shown') !== null) {
            return $sessionCache->getValue('is_3rd_party_should_be_shown');
        }

        $accountCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountCollection->addFieldToFilter(
            'other_listings_synchronization', Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES
        );

        if ((bool)$accountCollection->getSize()) {
            $result = true;
        } else {
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');

            $logCollection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
            $logCollection->addFieldToFilter(
                'component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK
            );

            $result = $collection->getSize() || $logCollection->getSize();
        }

        $sessionCache->setValue('is_3rd_party_should_be_shown', $result);

        return $result;
    }

    //----------------------------------------

    public function isDuplicatesFilterShouldBeShown($listingId = null)
    {
        $sessionCache = Mage::helper('M2ePro/Data_Cache_Session');

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
