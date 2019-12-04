<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Issue_Object as Issue;

class Ess_M2ePro_Model_Amazon_Marketplace_Issue_NotUpdated extends Ess_M2ePro_Model_Issue_Locator_Abstract
{
    const CACHE_KEY = __CLASS__;

    //########################################

    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        $outdatedMarketplaces = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(self::CACHE_KEY);
        if ($outdatedMarketplaces === false) {
            $tableName = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_amazon_dictionary_marketplace');

            $queryStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
                ->select()
                ->from($tableName, array('marketplace_id', 'server_details_last_update_date'))
                ->where('client_details_last_update_date IS NOT NULL')
                ->where('server_details_last_update_date IS NOT NULL')
                ->where('client_details_last_update_date < server_details_last_update_date')
                ->query();

            $dictionaryData = array();
            while ($row = $queryStmt->fetch()) {
                $dictionaryData[(int)$row['marketplace_id']] = $row['server_details_last_update_date'];
            }

            $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
                ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                ->addFieldToFilter('id', array('in' => array_keys($dictionaryData)))
                ->setOrder('sorder', 'ASC');

            $outdatedMarketplaces = array();
            foreach ($marketplacesCollection as $marketplace) {
                /** @var $marketplace Ess_M2ePro_Model_Marketplace */
                $outdatedMarketplaces[$marketplace->getTitle()] = $dictionaryData[$marketplace->getId()];
            }

            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
                self::CACHE_KEY, $outdatedMarketplaces, array('amazon','marketplace'), 60*60*24
            );
        }

        if (empty($outdatedMarketplaces)) {
            return array();
        }

        $tempTitle = Mage::helper('M2ePro')->__(
            'M2E Pro requires action: Amazon marketplace data needs to be synchronized.
            Please update Amazon marketplaces.'
        );
        $textToTranslate = <<<TEXT
%marketplace_title% data was changed on Amazon. You need to resynchronize the marketplace(s) to correctly
associate your products with Amazon catalog.<br>
Please go to %menu_path% > <a href="%url%" target="_blank">Marketplaces</a> and press <b>Update All Now</b>.
TEXT;

        $tempMessage = Mage::helper('M2ePro')->__(
            $textToTranslate,
            implode(', ', array_keys($outdatedMarketplaces)),
            Mage::helper('M2ePro/View_Amazon')->getPageNavigationPath('configuration'),
            Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_amazon_marketplace',
                array('tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_MARKETPLACE)
            )
        );

        $editHash = sha1(self::CACHE_KEY . Mage::helper('M2ePro')->jsonEncode($outdatedMarketplaces));
        $messageUrl = Mage::helper('adminhtml')->getUrl(
            'M2ePro/adminhtml_amazon_marketplace/index',
            array(
                'tab'    => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_MARKETPLACE,
                '_query' => array('hash' => $editHash)
            )
        );

        return array(
            Mage::getModel(
                'M2ePro/Issue_Object', array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                Issue::KEY_TITLE => $tempTitle,
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => $messageUrl
                )
            )
        );
    }

    //########################################

    public function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Amazon')->isInstallationWizardFinished() &&
               Mage::helper('M2ePro/Component_Amazon')->isEnabled();
    }

    //########################################
}
