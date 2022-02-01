<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    /** @var Ess_M2ePro_Helper_Module_Wizard|null */
    protected $_wizardHelper = null;

    //########################################

    abstract protected function getNick();

    abstract protected function getMenuRootNodeNick();

    abstract protected function getMenuRootNodeLabel();

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();
        $this->_initPopUp();

        Mage::helper('M2ePro/Module_Wizard')->addWizardJs();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed($this->getMenuRootNodeNick());
    }

    //########################################

    public function indexAction()
    {
        if ($this->isNotStarted() || $this->isActive()) {
            return $this->_redirect('*/*/installation');
        }

        $this->_redirect('*/*/congratulation', array('wizard' => true));
    }

    // ---------------------------------------

    public function installationAction()
    {
        if ($this->isFinished()) {
            return $this->_redirect('*/*/congratulation', array('wizard' => true));
        }

        if ($this->isNotStarted()) {
            $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE);
        }

        if (!$this->getCurrentStep() || !in_array($this->getCurrentStep(), $this->getSteps())) {
            $this->setStep($this->getFirstStep());
        }

        $this->_forward($this->getCurrentStep());
    }

    public function congratulationAction()
    {
        if (!$this->isFinished()) {
            $this->_redirect('*/*/index');

            return;
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_initAction();
        $this->_addContent(Mage::getSingleton('core/layout')->createBlock('M2ePro/adminhtml_wizard_congratulation'));
        $this->renderLayout();
    }

    public function registrationAction()
    {
        $isExistInfo = Mage::helper('M2ePro/Module')->getRegistration()->isExistInfo();
        $key = Mage::helper('M2ePro/Module_License')->getKey();

        if ($isExistInfo && !empty($key)) {
            $this->setStep($this->getNextStep());
        }

        return $this->renderSimpleStep();
    }

    public function accountAction()
    {
        return $this->renderSimpleStep();
    }

    public function listingTutorialAction()
    {
        return $this->renderSimpleStep();
    }

    //########################################

    public function createLicenseAction()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            $message = 'The action is temporarily unavailable. M2E Pro Server is under maintenance.';
            $message .= ' Please try again later.';

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status'  => false,
                        'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
                    )
                )
            );
        }

        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
            'phone',
            'country',
            'city',
            'postal_code',
        );

        $licenseData = array();
        foreach ($requiredKeys as $key) {
            if ($tempValue = $this->getRequest()->getParam($key)) {
                $licenseData[$key] = Mage::helper('M2ePro')->escapeJs(
                    Mage::helper('M2ePro')->escapeHtml($tempValue)
                );
                continue;
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status'  => false,
                        'message' => Mage::helper('M2ePro')->__('You should fill all required fields.')
                    )
                )
            );
        }

        $info = Mage::getSingleton('M2ePro/Registration_Info_Factory')->createInfoInstance(
            $licenseData['email'],
            $licenseData['firstname'],
            $licenseData['lastname'],
            $licenseData['phone'],
            $licenseData['country'],
            $licenseData['city'],
            $licenseData['postal_code']
        );

        Mage::helper('M2ePro/Module')->getRegistration()->saveInfo($info);

        if(Mage::helper('M2ePro/Module_License')->getKey()){
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('status'  => true))
            );
        }

        $message = null;

        try {
            $licenseResult = Mage::helper('M2ePro/Module_License')->obtainRecord($info);
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $licenseResult = false;
            $message = Mage::helper('M2ePro')->__($e->getMessage());
        }

        if (!$licenseResult) {
            if (!$message) {
                $message = $this->__('License Creation is failed. Please contact M2E Pro Support for resolution.');
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'status'  => $licenseResult,
                        'message' => $message
                    )
                )
            );
        }

        try {
            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
            );
        }
        catch (Exception $e) {}

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'status'  => $licenseResult,
                    'message' => $message
                )
            )
        );
    }

    //########################################

    public function skipAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED);

        $component = $this->getCustomViewNick();
        $this->_redirect("M2ePro/adminhtml_{$component}_listing/index");
    }

    public function completeAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED);

        $this->_redirect('*/*/index');
    }

    //########################################

    protected function getWizardHelper()
    {
        if ($this->_wizardHelper === null) {
            $this->_wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        }

        return $this->_wizardHelper;
    }

    // ---------------------------------------

    protected function setStatus($status)
    {
        $this->getWizardHelper()->setStatus($this->getNick(), $status);

        return $this;
    }

    protected function getStatus()
    {
        return $this->getWizardHelper()->getStatus($this->getNick());
    }

    // ---------------------------------------

    protected function setStep($step)
    {
        $this->getWizardHelper()->setStep($this->getNick(), $step);

        return $this;
    }

    protected function getSteps()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getSteps();
    }

    protected function getFirstStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getFirstStep();
    }

    protected function getPrevStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getPrevStep();
    }

    protected function getCurrentStep()
    {
        return $this->getWizardHelper()->getStep($this->getNick());
    }

    protected function getNextStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getNextStep();
    }

    // ---------------------------------------

    protected function isNotStarted()
    {
        return $this->getWizardHelper()->isNotStarted($this->getNick());
    }

    protected function isActive()
    {
        return $this->getWizardHelper()->isActive($this->getNick());
    }

    public function isCompleted()
    {
        return $this->getWizardHelper()->isCompleted($this->getNick());
    }

    public function isSkipped()
    {
        return $this->getWizardHelper()->isSkipped($this->getNick());
    }

    protected function isFinished()
    {
        return $this->getWizardHelper()->isFinished($this->getNick());
    }

    //########################################

    public function setStepAction()
    {
        $step = $this->getRequest()->getParam('step');

        if ($step === null) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'type'    => 'error',
                        'message' => Mage::helper('M2ePro')->__('Step is invalid')
                    )
                )
            );
        }

        $this->setStep($step);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'type' => 'success'
                )
            )
        );
    }

    public function setStatusAction()
    {
        $status = $this->getRequest()->getParam('status');

        if ($status === null) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'type'    => 'error',
                        'message' => Mage::helper('M2ePro')->__('Status is invalid')
                    )
                )
            );
        }

        $this->setStatus($status);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'type' => 'success'
                )
            )
        );
    }

    //########################################

    public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu($this->getMenuRootNodeNick());
        $tempResult->_title($this->getMenuRootNodeLabel());

        return $tempResult;
    }

    //########################################

    protected function renderSimpleStep()
    {
        return $this->_initAction()
            ->_addContent(
                $this->getWizardHelper()->createBlock(
                    'installation_' . $this->getCurrentStep(),
                    $this->getNick()
                )
            )
            ->renderLayout();
    }

    //########################################
}
