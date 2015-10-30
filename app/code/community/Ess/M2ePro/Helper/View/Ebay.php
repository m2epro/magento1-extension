<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Ebay extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Sell On eBay

    const NICK  = 'ebay';

    const WIZARD_INSTALLATION_NICK = 'installationEbay';
    const MENU_ROOT_NODE_NICK = 'm2epro_ebay';

    const MODE_SIMPLE = 'simple';
    const MODE_ADVANCED = 'advanced';

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__('Sell On eBay');
    }

    //########################################

    public function getMenuRootNodeLabel()
    {
        return $this->getTitle();
    }

    //########################################

    public function getPageNavigationPath($pathNick, $tabName = NULL, $additionalEnd = NULL)
    {
        $resultPath = array();

        $rootMenuNode = Mage::getConfig()->getNode('adminhtml/menu/m2epro_ebay');
        $menuLabel = Mage::helper('M2ePro/View')->getMenuPath($rootMenuNode, $pathNick, $this->getMenuRootNodeLabel());

        if (!$menuLabel) {
            return '';
        }

        $resultPath['menu'] = $menuLabel;

        if ($tabName) {
            $resultPath['tab'] = Mage::helper('M2ePro')->__($tabName) . ' ' . Mage::helper('M2ePro')->__('Tab');
        }

        if ($additionalEnd) {
            $resultPath['additional'] = Mage::helper('M2ePro')->__($additionalEnd);
        }

        return join($resultPath, ' > ');
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
        if (!in_array($mode,array(self::MODE_SIMPLE,self::MODE_ADVANCED))) {
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

    public function prepareMenu(array $menuArray)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed(self::MENU_ROOT_NODE_NICK)) {
            return $menuArray;
        }

        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) <= 0) {
            unset($menuArray[self::MENU_ROOT_NODE_NICK]);
            return $menuArray;
        }

        $tempTitle = $this->getMenuRootNodeLabel();
        !empty($tempTitle) && $menuArray[self::MENU_ROOT_NODE_NICK]['label'] = $tempTitle;

        // Add wizard menu item
        // ---------------------------------------
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeBlocker = $wizardHelper->getActiveBlockerWizard(Ess_M2ePro_Helper_View_Ebay::NICK);

        if (!$activeBlocker) {
            return $menuArray;
        }

        unset($menuArray[self::MENU_ROOT_NODE_NICK]['children']);
        unset($menuArray[self::MENU_ROOT_NODE_NICK]['click']);

        $menuArray[self::MENU_ROOT_NODE_NICK]['url'] = Mage::helper('adminhtml')->getUrl(
            'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
        );
        $menuArray[self::MENU_ROOT_NODE_NICK]['last'] = true;

        return $menuArray;
    }

    //########################################

    public function isFeedbacksShouldBeShown($accountId = NULL)
    {
        $accountCollection = Mage::getModel('M2ePro/Ebay_Account')->getCollection();
        $accountCollection->addFieldToFilter(
            'feedbacks_receive', Ess_M2ePro_Model_Ebay_Account::FEEDBACKS_RECEIVE_YES
        );

        $feedbackCollection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection();

        if (!is_null($accountId)) {
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

        if (!is_null($sessionCache->getValue('is_3rd_party_should_be_shown'))) {
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

    //########################################
}