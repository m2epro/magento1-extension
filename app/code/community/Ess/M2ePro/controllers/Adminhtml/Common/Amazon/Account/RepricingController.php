<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Account_RepricingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_account/index');
    }

    //########################################

    public function linkOrRegisterAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var $model Ess_M2ePro_Model_Account */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $this->_redirectUrl($repricing->getLinkUrl());
    }

    public function linkAction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $token = $this->getRequest()->getParam('account_token');
        $email = $this->getRequest()->getParam('email');

        $status = $this->getRequest()->getParam('status');
        $messages = $this->getRequest()->getParam('messages', array());

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $this->parseRepricingMessages($messages);

        if ($status == '1') {
            $model->setData('repricing', json_encode(array(
                'email' => $email,
                'token' => $token,
                'info' => array(
                    'total_products' => 0
                )
            )));
            $model->save();

            /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
            $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);
            $repricing->synchronize();
        }

        return $this->_redirectUrl($this->getUrl('*/adminhtml_common_amazon_account/edit', array(
            'id' => $accountId
        )). '#repricing');
    }

    //----------------------------------------

    public function openUnlinkPageAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var $model Ess_M2ePro_Model_Account */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $this->_redirectUrl($repricing->getUnLinkUrl());
    }

    public function unlinkAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        $status = $this->getRequest()->getParam('status');
        $messages = $this->getRequest()->getParam('messages', array());

        /** @var $model Ess_M2ePro_Model_Account */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $this->parseRepricingMessages($messages);

        if ($status == '1') {
            $model->setData('repricing', NULL);
            $model->save();

            /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
            $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);
            $repricing->resetProductRepricingStatus();
        }

        return $this->_redirectUrl($this->getUrl('*/adminhtml_common_amazon_account/edit', array(
            'id' => $accountId
        )). '#repricing');
    }

    //########################################

    public function openManagementAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $this->_redirectUrl($repricing->getManagementUrl());
    }

    //########################################

    public function synchronizeAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);
        $result = $repricing->synchronize();

        if ($result !== true) {
            return $this->getResponse()->setBody(json_encode(array(
                'messages' => $result
            )));
        }

        $repricingInfo = $model->getChildObject()->getRepricingInfo();

        $this->getResponse()->setBody(json_encode(array(
            'repricing_total_products' => $repricingInfo['total_products'],
            'm2epro_repricing_total_products' => count($repricing->getRepricingListingProductsData()),
        )));
    }

    //########################################

    private function parseRepricingMessages($messages)
    {
        foreach ($messages as $message) {

            if ($message['type'] == 'notice') {
                $this->_getSession()->addNotice($message['text']);
            }

            if ($message['type'] == 'warning') {
                $this->_getSession()->addWarning($message['text']);
            }

            if ($message['type'] == 'error') {
                $this->_getSession()->addError($message['text']);
            }
        }
    }

    //########################################
}
