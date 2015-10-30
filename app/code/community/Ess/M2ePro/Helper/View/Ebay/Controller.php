<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_View_Ebay_Controller extends Mage_Core_Helper_Abstract
{
    //########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        if (Mage::helper('M2ePro/Module')->isReadyToWork() &&
            Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1,true) &&
            !Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            $this->addCronNotificationMessage($controller);
        }

        if (Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished()) {

            $feedbacksNotificationMode = Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/view/ebay/feedbacks/notification/', 'mode');

            !$feedbacksNotificationMode ||
            !$this->haveNewNegativeFeedbacks() ||
            $this->addFeedbackNotificationMessage($controller);

            $this->addTokenExpirationDateNotificationMessage($controller);
            $this->addMarketplacesNotUpdatedNotificationMessage($controller);
        }
    }

    //########################################

    private function addCronNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $url = 'http://support.m2epro.com/knowledgebase/articles/42054-how-to-set-up-cron-job-for-m2e-pro';

        // M2ePro_TRANSLATIONS
        // Attention! Last eBay AUTOMATIC Synchronization was performed by Cron more than 1 hour ago. You should set up Cron Job, otherwise no Automatic Synchronization will be performed.<br/>You can check this <a href="%url%" target="_blank">Article</a> to get how to set Cron Job.
        $message = 'Attention! Last eBay AUTOMATIC Synchronization was performed by Cron ';
        $message .= 'more than 1 hour ago. You should set up Cron Job, otherwise no Automatic Synchronization ';
        $message .= 'will be performed.<br/>You can check this <a href="%url%" target="_blank">Article</a> '.
                    'to get how to set Cron Job.';

        $controller->getSession()->addNotice(Mage::helper('M2ePro')->__($message, $url));
    }

    private function addFeedbackNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $url = $controller->getUrl('*/adminhtml_ebay_feedback/index');

        // M2ePro_TRANSLATIONS
        // New Buyer negative Feedback was received. Go to the <a href="%url%" target="blank">feedback Page</a>.
        $message = 'New Buyer negative Feedback was received. '
            .'Go to the <a href="%url%" target="blank">Feedback Page</a>.';
        $message = Mage::helper('M2ePro')->__($message, $url);

        $controller->getSession()->addNotice($message);
    }

    //########################################

    private function addTokenExpirationDateNotificationMessage(
                            Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $tokenExpirationMessages = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(
            'ebay_accounts_token_expiration_messages'
        );

        if ($tokenExpirationMessages === false) {

            $tokenExpirationMessages = array();

            /* @var $tempCollection Mage_Core_Model_Mysql4_Collection_Abstract */
            $tempCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

            $tempCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $tempCollection->getSelect()->columns(array('id','title'));
            $tempCollection->getSelect()->columns('token_expired_date','second_table');

            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

            foreach ($tempCollection->getData() as $accountData) {

                $tokenExpirationTimeStamp = strtotime($accountData['token_expired_date']);
// M2ePro_TRANSLATIONS
/*
The token for "%account_title%" eBay Account has been expired.<br/>
Please, go to %menu_label% > Configuration > eBay Account >
<a href="%url%" target="_blank">General TAB</a>, click on the Get Token button.
(You will be redirected to the eBay website.) Sign-in and press I Agree on eBay Page.
Do not forget to press Save button after returning back to Magento
 */
                $textToTranslate =
                    'The token for "%account_title%" eBay Account has been expired.<br/>'.
                    'Please, go to %menu_label% > Configuration > eBay Account >'.
                    '<a href="%url%" target="_blank">General TAB</a>, click on the Get Token Button.'.
                    '(You will be redirected to the eBay website.) Sign-in and press I Agree on eBay Page.'.
                    'Do not forget to press Save Button after returning back to Magento';

                if ($tokenExpirationTimeStamp < $currentTimeStamp) {
                    $tempMessage = Mage::helper('M2ePro')->__(
                        trim($textToTranslate),
                        Mage::helper('M2ePro')->escapeHtml($accountData['title']),
                        Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                        $controller->getUrl('*/adminhtml_ebay_account/edit', array('id' => $accountData['id']))
                    );
                    $tokenExpirationMessages[] = array(
                        'type' => 'error',
                        'message' => $tempMessage
                    );

                    continue;
                }
// M2ePro_TRANSLATIONS
/*
Attention! The token for "%account_title%" eBay Account will be expired soon ( %date% ).
<br/>Please, go to %menu_label% > Configuration > eBay Account >
<a href="%url%" target="_blank">General TAB</a>, click on the Get Token Button.
(You will be redirected to the eBay website.) Sign-in and press I Agree on eBay Page.
Do not forget to press Save Button after returning back to Magento
 */
                $textToTranslate =
                   'Attention! The token for "%account_title%" eBay Account will be expired soon ( %date% ).'.
                    '<br/>Please, go to %menu_label% > Configuration > eBay Account >'.
                    '<a href="%url%" target="_blank">General TAB</a>, click on the Get Token Button.'.
                    '(You will be redirected to the eBay website.) Sign-in and press I Agree on eBay Page.'.
                    'Do not forget to press Save Button after returning back to Magento';

                if (($currentTimeStamp + 60*60*24*10) >= $tokenExpirationTimeStamp) {

                    $tempMessage = Mage::helper('M2ePro')->__(
                        trim($textToTranslate),
                        Mage::helper('M2ePro')->escapeHtml($accountData['title']),
                        Mage::app()->getLocale()->date(strtotime($accountData['token_expired_date']))
                                                ->toString($format),
                        Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                            $controller->getUrl('*/adminhtml_ebay_account/edit', array('id' => $accountData['id']))
                        );

                    $tokenExpirationMessages[] = array(
                        'type' => 'notice',
                        'message' => $tempMessage
                    );

                    continue;
                }
            }

            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('ebay_accounts_token_expiration_messages',
                                                         $tokenExpirationMessages,
                                                         array('account','ebay'),
                                                         60*60*24);
        }

        foreach ($tokenExpirationMessages as $messageData) {
            $method = 'add' . ucfirst($messageData['type']);
            $controller->getSession()->$method($messageData['message']);
        }
    }

    private function addMarketplacesNotUpdatedNotificationMessage(
                            Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $outdatedMarketplaces = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue('ebay_outdated_marketplaces');

        if ($outdatedMarketplaces === false) {
            $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $dictionaryTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

            $rows = $readConn->select()->from($dictionaryTable,'marketplace_id')
                ->where('client_details_last_update_date IS NOT NULL')
                ->where('server_details_last_update_date IS NOT NULL')
                ->where('client_details_last_update_date < server_details_last_update_date')
                ->query();

            $ids = array();
            foreach ($rows as $row) {
                $ids[] = $row['marketplace_id'];
            }

            $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
                ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                ->addFieldToFilter('id',array('in' => $ids))
                ->setOrder('sorder','ASC');

            $outdatedMarketplaces = array();
            /* @var $marketplace Ess_M2ePro_Model_Marketplace */
            foreach ($marketplacesCollection as $marketplace) {
                $outdatedMarketplaces[] = $marketplace->getTitle();
            }

            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('ebay_outdated_marketplaces',
                                                                  $outdatedMarketplaces,
                                                                  array('ebay','marketplace'),
                                                                  60*60*24);
        }

        if (count($outdatedMarketplaces) <= 0) {
            return;
        }

// M2ePro_TRANSLATIONS
// %marketplace_title% data was changed on eBay. You need to synchronize it the Extension works properly. Please, go to %menu_label% > Configuration > <a href="%url%" target="_blank">eBay Sites</a> and click the Update All Now Button.

        $message = '%marketplace_title% data was changed on eBay. You need to synchronize it'.
                   ' the Extension works properly. Please, go to %menu_path% > '.
                   '<a href="%url%" target="_blank">eBay Sites</a> and click the Update All Now Button.';

        $controller->getSession()->addNotice(Mage::helper('M2ePro')->__(
            $message,
            implode(', ',$outdatedMarketplaces),
            Mage::helper('M2ePro/View_Ebay')->getPageNavigationPath('configuration'),
            $controller->getUrl(
                '*/adminhtml_ebay_marketplace',
                array('tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_MARKETPLACE)
            )
        ));
    }

    //########################################

    private function haveNewNegativeFeedbacks()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $configGroup = '/view/ebay/feedbacks/notification/';

        $lastCheckDate = $config->getGroupValue($configGroup, 'last_check');

        if (is_null($lastCheckDate)) {
            $config->setGroupValue($configGroup, 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $collection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
                            ->addFieldToFilter('buyer_feedback_date', array('gt' => $lastCheckDate))
                            ->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Ebay_Feedback::TYPE_NEGATIVE);

        if ($collection->getSize() > 0) {
            $config->setGroupValue($configGroup, 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return true;
        }

        return false;
    }

    //########################################
}