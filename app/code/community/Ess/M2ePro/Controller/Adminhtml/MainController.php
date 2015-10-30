<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            // check rewrite menu
            if (count($this->getCustomViewComponentHelper()->getActiveComponents()) < 1) {
                throw new Ess_M2ePro_Model_Exception('At least 1 channel of current View should be enabled.');
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

            } catch (Exception $exception) {}
        }

        $maintenanceHelper = Mage::helper('M2ePro/Module_Maintenance');

        if ($maintenanceHelper->isEnabled()) {

            if ($maintenanceHelper->isOwner()) {
                $maintenanceHelper->prolongRestoreDate();
            } elseif ($maintenanceHelper->isExpired()) {
                $maintenanceHelper->disable();
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

            $lockNotification = $this->addLockNotifications();
            $browserNotification = $this->addBrowserNotifications();
            $maintenanceNotification = $this->addMaintenanceNotifications();

            $muteMessages = $lockNotification || $browserNotification || $maintenanceNotification;

            if (!$muteMessages && $this->getCustomViewHelper()->isInstallationWizardFinished()) {
                $this->addLicenseNotifications();
            }

            $this->addServerNotifications();

            if (!$muteMessages) {
                $this->getCustomViewControllerHelper()->addMessages($this);
            }
        }
    }

    // ---------------------------------------

    private function addLockNotifications()
    {
        if (Mage::helper('M2ePro/Module')->isLockedByServer()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('M2E Pro Module is locked because of security reason. Please contact us.')
            );
            return true;
        }
        return false;
    }

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

    private function addMaintenanceNotifications()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Maintenance')->isOwner()) {

            $this->_getSession()->addNotice(Mage::helper('M2ePro')->__(
                'Maintenance is Active.'
            ));

            return false;
        }

        $this->_getSession()->addError(Mage::helper('M2ePro')->__(
            'M2E Pro is working in Maintenance Mode at the moment. Developers are investigating your issue.'
        ).'<br/>'.Mage::helper('M2ePro')->__(
            'You will be able to see a content of this Page soon. Please wait and then refresh a browser Page later.'
        ));

        return true;
    }

    // ---------------------------------------

    private function addLicenseNotifications()
    {
        $licenseMainErrorStatus = $this->addLicenseActivationNotifications() ||
                                  $this->addLicenseValidationFailNotifications();

        if ($licenseMainErrorStatus) {
            return;
        }

        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if ($this->addLicenseStatusesNotifications($component)) {
                continue;
            }

            if ($licenseHelper->isNoneMode($component)) {
                continue;
            }

            if ($this->addLicenseExpirationDatesNotifications($component)) {
                continue;
            }

            $this->addLicenseTrialNotifications($component);
            $this->addLicensePreExpirationDateNotifications($component);
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

    //########################################

    private function addLicenseActivationNotifications()
    {
        if (!Mage::helper('M2ePro/Module_License')->getKey() ||
            !Mage::helper('M2ePro/Module_License')->getDomain() ||
            !Mage::helper('M2ePro/Module_License')->getIp() ||
            !Mage::helper('M2ePro/Module_License')->getDirectory()) {

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

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

// M2ePro_TRANSLATIONS
// M2E Pro License Key Validation is failed for this Domain. Go to the <a href="%url%" target="_blank">License Page</a>.
            $message = 'M2E Pro License Key Validation is failed for this Domain. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>.';
            $message = Mage::helper('M2ePro')->__($message,$url);

            $this->_getSession()->addError($message);
            return true;
        }

        if (!$licenseHelper->isValidIp()) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

// M2ePro_TRANSLATIONS
// M2E Pro License Key Validation is failed for this IP. Go to the <a href="%url%" target="_blank">License Page</a>.
            $message = 'M2E Pro License Key Validation is failed for this IP. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>.';
            $message = Mage::helper('M2ePro')->__($message, $url);

            $this->_getSession()->addError($message);
            return true;
        }

        if (!$licenseHelper->isValidDirectory()) {
            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            // M2ePro_TRANSLATIONS
            // M2E Pro License Key Validation is failed for this Base Directory. Go to the <a href="%url%" target="_blank">License Page</a>
            $message = 'M2E Pro License Key Validation is failed for this Base Directory. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>';
            $message = Mage::helper('M2ePro')->__($message, $url);

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function addLicenseStatusesNotifications($component)
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if ($licenseHelper->isSuspendedStatus($component)) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            // M2ePro_TRANSLATIONS
            // M2E Pro Module License suspended for "%component_name%" Component. Go to the <a href="%url%" target="_blank">License Page</a>.
            $message = 'M2E Pro Module License suspended for "%component_name%" Component. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>.';
            $message = Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        if ($licenseHelper->isClosedStatus($component)) {
            // M2ePro_TRANSLATIONS
            // M2E Pro Module License closed for "%component_name%" Component. Go to the <a href="%url%" target="_blank">License Page</a>
            $message = 'M2E Pro Module License closed for "%component_name%" Component. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>';

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            $message = Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        if ($licenseHelper->isCanceledStatus($component)) {

            $message = 'M2E Pro Module License canceled for "%component_name%" Component. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>';

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            $message = Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    private function addLicenseExpirationDatesNotifications($component)
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if ($licenseHelper->isExpirationDate($component)) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();
            // M2ePro_TRANSLATIONS
            // M2E Pro Module License has expired for "%component_name%" Component. Go to the <a href="%url%" target="_blank">License Page</a>
            $message = 'M2E Pro Module License has expired for "%component_name%" Component. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>';
            $message = Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $url
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function addLicenseTrialNotifications($component)
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if ($licenseHelper->isTrialMode($component)) {

            $expirationDate = $licenseHelper->getTextExpirationDate($component);

            // M2ePro_TRANSLATIONS
            // M2E Pro Module is running under Trial License for "%component_name%" Component, that will expire on %date%.
            $message = 'M2E Pro Module is running under Trial License for "%component_name%" Component, ';
            $message .= 'that will expire on %date%.';
            $message = Mage::helper('M2ePro')->__(
                $message,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $expirationDate
            );

            $this->_getSession()->addWarning($message);
            return true;
        }

        return false;
    }

    private function addLicensePreExpirationDateNotifications($component)
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        if ($licenseHelper->getIntervalBeforeExpirationDate($component) > 0 &&
            $licenseHelper->getIntervalBeforeExpirationDate($component) <= 60*60*24*3) {

            $url = Mage::helper('M2ePro/View_Configuration')->getLicenseUrl();

            $expirationDate = $licenseHelper->getTextExpirationDate($component);

            // M2ePro_TRANSLATIONS
            // M2E Pro Module License will expire on %date% for "%component_name%" Component. Go to the <a href="%url%" target="_blank">License Page</a>
            $message = 'M2E Pro Module License will expire on %date% for "%component_name%" Component. ';
            $message .= 'Go to the <a href="%url%" target="_blank">License Page</a>';
            $message = Mage::helper('M2ePro')->__(
                $message,
                $expirationDate,
                Mage::helper('M2ePro/Component_'.ucfirst($component))->getTitle(),
                $url
            );

            $this->_getSession()->addWarning($message);
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
        if (Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/requirements/popup/', 'closed')) {
            return;
        };

        $isMeetRequirements = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue('is_meet_requirements');

        if ($isMeetRequirements === false) {
            $isMeetRequirements = true;
            foreach (Mage::helper('M2ePro/Module')->getRequirementsInfo() as $requirement) {
                if (!$requirement['current']['status']) {
                    $isMeetRequirements = false;
                    break;
                }
            }
            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
                'is_meet_requirements',(int)$isMeetRequirements, array(), 60*60
            );
        }

        if ($isMeetRequirements) {
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
        return $this->isContentLockedByWizard() ||
               Mage::helper('M2ePro/Client')->isBrowserIE() ||
               Mage::helper('M2ePro/Module')->isLockedByServer() ||
               (
                   Mage::helper('M2ePro/Module_Maintenance')->isEnabled() &&
                   !Mage::helper('M2ePro/Module_Maintenance')->isOwner()
               );
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