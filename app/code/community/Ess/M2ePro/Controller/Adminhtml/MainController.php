<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    protected function _preDispatch()
    {
        parent::_preDispatch();

        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            // check rewrite menu
            if (count($this->getCustomViewComponentHelper()->getActiveComponents()) < 1) {
                return $this->_redirect(Mage::helper('M2ePro/Module_Support')->getPageRoute());
            }

            // update client data
            try {
                Mage::helper('M2ePro/Client')->updateBackupConnectionData(false);
            } catch (Exception $exception) {}

            // run servicing code
            try {

                $dispatcher = Mage::getModel('M2ePro/Servicing_Dispatcher');
                $dispatcher->process(Ess_M2ePro_Model_Servicing_Dispatcher::DEFAULT_INTERVAL,
                                     $dispatcher->getFastTasks());

            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        return $this;
    }

    // ---------------------------------------

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $this->addNotificationMessages();
        return parent::loadLayout($ids, $generateBlocks, $generateXml);
    }

    // ---------------------------------------

    protected function addLeft(Mage_Core_Block_Abstract $block)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            if ($this->isContentLocked()) {
                return $this;
            }
        }

        return parent::addLeft($block);
    }

    protected function addContent(Mage_Core_Block_Abstract $block)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            if ($this->isContentLocked()) {
                return $this;
            }
        }

        return parent::addContent($block);
    }

    // ---------------------------------------

    protected function beforeAddContentEvent()
    {
        $this->addRequirementsErrorMessage();
        $this->addWizardUpgradeNotification();
    }

    //########################################

    protected function getCustomViewHelper()
    {
        return Mage::helper('M2ePro/View')->getHelper($this->getCustomViewNick());
    }

    protected function getCustomViewComponentHelper()
    {
        return Mage::helper('M2ePro/View')->getComponentHelper($this->getCustomViewNick());
    }

    protected function getCustomViewControllerHelper()
    {
        return Mage::helper('M2ePro/View')->getControllerHelper($this->getCustomViewNick());
    }

    // ---------------------------------------

    abstract protected function getCustomViewNick();

    //########################################

    protected function addNotificationMessages()
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            $muteMessages = $this->addBrowserNotifications();

            if (!$muteMessages) {
                $this->addLicenseNotifications();
            }

            $this->addServerNotifications();
            $this->addServerMaintenanceInfo();

            if (!$muteMessages) {
                $this->getCustomViewControllerHelper()->addMessages();
                $this->addCronErrorMessage();
            }
        }
    }

    // ---------------------------------------

    private function addBrowserNotifications()
    {
// M2ePro_TRANSLATIONS
// We are sorry, Internet Explorer browser is not supported. Please, use another browser (Mozilla Firefox, Google Chrome, etc.).
        if (Mage::helper('M2ePro/Client')->isBrowserIE()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'We are sorry, Internet Explorer browser is not supported. Please, use'.
                ' another browser (Mozilla Firefox, Google Chrome, etc.).'
            ));
            return true;
        }
        return false;
    }

    // ---------------------------------------

    private function addLicenseNotifications()
    {
        $added = false;
        if (!$added && $this->getCustomViewHelper()->isInstallationWizardFinished()) {
            $added = $this->addLicenseActivationNotifications();
        }

        if (!$added && Mage::helper('M2ePro/Module_License')->getKey()) {
            $added = $this->addLicenseValidationFailNotifications();
        }

        if (!$added && Mage::helper('M2ePro/Module_License')->getKey()) {
            $added = $this->addLicenseStatusNotifications();
        }
    }

    private function addServerNotifications()
    {
        $messages = Mage::helper('M2ePro/Module')->getServerMessages();

        foreach ($messages as $message) {

            if (isset($message['text']) && isset($message['type']) && $message['text'] != '') {

                switch ($message['type']) {
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_ERROR:
                        $this->_getSession()->addError(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_WARNING:
                        $this->_getSession()->addWarning(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_SUCCESS:
                        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_NOTICE:
                    default:
                        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__($message['text']));
                        break;
                }
            }
        }
    }

    private function addServerMaintenanceInfo()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            // M2ePro_TRANSLATIONS
            // M2E Pro server is currently under the planned maintenance. The process is scheduled to last %from% to %to%. Please do not apply any actions during this time frame.
            $message = 'M2E Pro server is currently under the planned maintenance. The process is scheduled to last';
            $message .= ' %from% to %to%. Please do not apply any actions during this time frame.';

            $this->_getSession()->addNotice(Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Server_Maintenance')->getDateEnabledFrom()->format('Y-m-d H:i:s'),
                Mage::helper('M2ePro/Server_Maintenance')->getDateEnabledTo()->format('Y-m-d H:i:s')
            ));
        } else if (Mage::helper('M2ePro/Server_Maintenance')->isScheduled()) {
            // M2ePro_TRANSLATIONS
            // The preventive server maintenance has been scheduled. The Service will be unavailable %from% to %to%. All product updates will processed after the technical works are finished.
            $message = 'The preventive server maintenance has been scheduled. The Service will be unavailable';
            $message .= ' %from% to %to%. All product updates will processed after the technical works are finished.';

            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Server_Maintenance')->getDateEnabledFrom()->format('Y-m-d H:i:s'),
                Mage::helper('M2ePro/Server_Maintenance')->getDateEnabledTo()->format('Y-m-d H:i:s')
            ));
        }
    }

    private function addCronErrorMessage()
    {
        // M2ePro_TRANSLATIONS
        // Attention! AUTOMATIC Synchronization is not running at the moment. It does not allow M2E Pro to work correctly.<br/>Please check this <a href="%url%" target="_blank">article</a> for the details on how to resolve the problem.

        if (Mage::helper('M2ePro/Module')->isReadyToWork() &&
            Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1, true) &&
            !Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {

            $url = Mage::helper('M2ePro/Module_Support')->getKnowledgebaseUrl('cron-running');

            $message  = 'Attention! AUTOMATIC Synchronization is not running at the moment. ';
            $message .= 'It does not allow M2E Pro to work correctly.<br/>';
            $message .= 'Please check this <a href="%url%" target="_blank">article</a> ';
            $message .= 'for the details on how to resolve the problem.';
            $message = Mage::helper('M2ePro')->__($message, $url);

            $this->_getSession()->addError($message);
        }
    }

    //########################################

    private function addLicenseActivationNotifications()
    {
        if (!Mage::helper('M2ePro/Module_License')->getKey() ||
            !Mage::helper('M2ePro/Module_License')->getDomain() ||
            !Mage::helper('M2ePro/Module_License')->getIp()) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            $message = Mage::helper('M2ePro')->__(
                'M2E Pro Module requires activation. Go to the <a href="%url%" target ="_blank">License Page</a>.',
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    private function addLicenseValidationFailNotifications()
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if (!$licenseHelper->isValidDomain()) {

// M2ePro_TRANSLATIONS
// To start working with M2E Pro, you need to associate your current Domain with the Extension Key. Please find more details under the <a href="%url%" target="_blank">Billing Info</a>.
            $message = Mage::helper('M2ePro')->__(<<<HTML
To start working with M2E Pro, you need to associate your current Domain with the Extension Key.
 Please find more details under the <a href="%url%" target="_blank">Billing Info</a>.
HTML
                , Mage::helper('M2ePro/View_Configuration')->getLicenseUrl()
            );

            $this->_getSession()->addError($message);
            return true;
        }

        if (!$licenseHelper->isValidIp()) {

// M2ePro_TRANSLATIONS
// To start working with M2E Pro, you need to associate your current IP with the Extension Key. Please find more details under the <a href="%url%" target="_blank">Billing Info</a>.

            $message = Mage::helper('M2ePro')->__(<<<HTML
To start working with M2E Pro, you need to associate your current Domain with the Extension Key.
 Please find more details under the <a href="%url%" target="_blank">Billing Info</a>.
HTML
                , Mage::helper('M2ePro/View_Configuration')->getLicenseUrl()
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    private function addLicenseStatusNotifications()
    {
        if (!Mage::helper('M2ePro/Module_License')->getStatus()) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            $message = Mage::helper('M2ePro')->__(
                'Your M2E Pro Instance suspended.
                The details can be found in <a href="%url%" target ="_blank">Billing Info</a>.',
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    //########################################

    private function addWizardUpgradeNotification()
    {
        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeWizard = $wizardHelper->getActiveWizard($this->getCustomViewNick());

        if (!$activeWizard) {
            return;
        }

        $activeWizardNick = $wizardHelper->getNick($activeWizard);

        if ((bool)$this->getRequest()->getParam('wizard',false) ||
            $this->getRequest()->getControllerName() == 'adminhtml_wizard_'.$activeWizardNick) {
            return;
        }

        $wizardHelper->addWizardHandlerJs();

        // Video tutorial
        // ---------------------------------------
        $this->_initPopUp();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorialHandler.js');
        // ---------------------------------------

        $this->getLayout()->getBlock('content')->append(
            $wizardHelper->createBlock('notification',$activeWizardNick)
        );
    }

    //########################################

    protected function addRequirementsErrorMessage()
    {
        if (Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue('/view/requirements/popup/', 'closed')) {
            return;
        };

        /** @var Ess_M2ePro_Model_Requirements_Manager $manager */
        $manager = Mage::getModel('M2ePro/Requirements_Manager');
        if ($manager->isMeet()) {
            return;
        }

        $this->_initPopUp();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('M2ePro/adminhtml_requirementsPopup')
        );
    }

    //########################################

    private function isContentLocked()
    {
        return Mage::helper('M2ePro/Module')->isDisabled() ||
               $this->isContentLockedByWizard() ||
               Mage::helper('M2ePro/Client')->isBrowserIE();
    }

    private function isContentLockedByWizard()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!($activeWizard = $wizardHelper->getActiveBlockerWizard($this->getCustomViewNick()))) {
            return false;
        }

        $activeWizardNick = $wizardHelper->getNick($activeWizard);

        if ((bool)$this->getRequest()->getParam('wizard',false) ||
            $this->getRequest()->getControllerName() == 'adminhtml_wizard_'.$activeWizardNick) {
            return false;
        }

        return true;
    }

    //########################################
}