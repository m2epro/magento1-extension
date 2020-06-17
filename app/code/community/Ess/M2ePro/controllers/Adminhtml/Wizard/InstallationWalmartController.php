<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Account as Account;

class Ess_M2ePro_Adminhtml_Wizard_InstallationWalmartController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addJs('M2ePro/SynchProgress.js')
             ->addJs('M2ePro/Marketplace.js')
             ->addJs('M2ePro/Wizard/InstallationWalmart.js')
             ->addJs('M2ePro/Walmart/Account.js')
             ->addJs('M2ePro/Walmart/Configuration/General.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    //########################################

    public function accountContinueAction()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        if (empty($params['marketplace_id'])) {
            $error = Mage::helper('M2ePro')->__('Please select Marketplace');
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('message' => $error)));
        }

        try {
            $accountData = array();

            $requiredFields = array(
                'marketplace_id',
                'consumer_id',
                'old_private_key',
                'client_id',
                'client_secret'
            );

            foreach ($requiredFields as $requiredField) {
                if (!empty($params[$requiredField])) {
                    $accountData[$requiredField] = $params[$requiredField];
                }
            }

            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            $marketplace = Mage::getModel('M2ePro/Marketplace')->loadInstance($accountData['marketplace_id']);

            if ($params['marketplace_id'] == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
                $params['consumer_id'] && $params['old_private_key']) {
                $requestData = array(
                    'marketplace_id' => $params['marketplace_id'],
                    'consumer_id'    => $params['consumer_id'],
                    'private_key'    => $params['old_private_key'],
                );
            } elseif ($params['marketplace_id'] != Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA &&
                $params['client_id'] && $params['client_secret']) {
                $requestData = array(
                    'marketplace_id' => $params['marketplace_id'],
                    'client_id'      => $params['client_id'],
                    'client_secret'  => $params['client_secret'],
                    'consumer_id'    => $params['consumer_id']
                );
            } else {
                $error = Mage::helper('M2ePro')->__('You should fill all required fields.');
                return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('message' => $error)));
            }

            $marketplace->setData('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $marketplace->save();

            $accountData = array_merge(
                $this->getAccountDefaultSettings(),
                array(
                    'title' => "Default - {$marketplace->getCode()}",
                ),
                $accountData
            );

            /** @var $model Ess_M2ePro_Model_Account */
            $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Account');
            Mage::getModel('M2ePro/Walmart_Account_Builder')->build($model, $accountData);

            $id = $model->save()->getId();

            /** @var $dispatcherObject Ess_M2ePro_Model_Walmart_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

            $connectorObj = $dispatcherObject->getConnector(
                'account',
                'add',
                'entityRequester',
                $requestData,
                $id
            );
            $dispatcherObject->process($connectorObj);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            if (!empty($model)) {
                $model->deleteInstance();
            }

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
            );

            $error = 'The Walmart access obtaining is currently unavailable.<br/>Reason: %error_message%';

            if (!Mage::helper('M2ePro/Module_License')->isValidDomain() ||
                !Mage::helper('M2ePro/Module_License')->isValidIp()) {
                $error .= '</br>Go to the <a href="%url%" target="_blank">License Page</a>.';
                $error = Mage::helper('M2ePro')->__(
                    $error,
                    $exception->getMessage(),
                    Mage::helper('M2ePro/View_Configuration')->getLicenseUrl(array('wizard' => 1))
                );
            } else {
                $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());
            }

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('message' => $error)));
        }

        $this->setStep($this->getNextStep());

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function listingTutorialContinueAction()
    {
        Mage::helper('M2ePro/Module_Wizard')->setStatus(
            Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK,
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        return $this->_redirect('*/adminhtml_walmart_listing_create');
    }

    public function settingsAction()
    {
        return $this->renderSimpleStep();
    }

    public function settingsContinueAction()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        Mage::helper('M2ePro/Component_Walmart_Configuration')->setConfigValues($params);

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    //########################################

    protected function getAccountDefaultSettings()
    {
        $data = Mage::getModel('M2ePro/Walmart_Account_Builder')->getDefaultData();

        $data['other_listings_synchronization'] = 0;
        $data['other_listings_mapping_mode'] = 0;

        $data['magento_orders_settings']['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')
            ->getDefaultStoreId();

        return $data;
    }

    //########################################
}
