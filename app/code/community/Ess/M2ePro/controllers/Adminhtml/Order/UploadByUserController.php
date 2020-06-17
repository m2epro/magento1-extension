<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser_Manager as AmazonManager;
use Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser_Manager as EbayManager;
use Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser_Manager as WalmartManager;

class Ess_M2ePro_Adminhtml_Order_UploadByUserController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function getPopupHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Order_UploadByUser_Popup $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_order_uploadByUser_popup');
        $block->setComponent($this->getRequest()->getParam('component'));

        $this->_addAjaxContent($block->toHtml());
    }

    public function getPopupGridAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Order_UploadByUser_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_order_uploadByUser_grid');
        $block->setComponent($this->getRequest()->getParam('component'));

        $this->_addAjaxContent($block->toHtml());
    }

    // ---------------------------------------

    public function resetAction()
    {
        $component = $this->getRequest()->getParam('component');
        $accountId = $this->getRequest()->getParam('account_id');
        if (empty($component) || empty($accountId)) {
            return $this->_addJsonContent(
                array(
                    'result'   => false,
                    'messages' => array(
                        array(
                            'type' => 'error',
                            'text' => Mage::helper('M2ePro')->__('Account must be specified.')
                        )
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component')->getCachedComponentObject($component, 'Account', $accountId);
        $manager = $this->getManager($account);

        $manager->clear();

        return $this->_addJsonContent(array('result' => true));
    }

    public function configureAction()
    {
        $component = $this->getRequest()->getParam('component');
        $accountId = $this->getRequest()->getParam('account_id');
        if (empty($component) || empty($accountId)) {
            return $this->_addJsonContent(
                array(
                    'result'   => false,
                    'messages' => array(
                        array(
                            'type' => 'error',
                            'text' => Mage::helper('M2ePro')->__('Account must be specified.')
                        )
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component')->getCachedComponentObject($component, 'Account', $accountId);
        $manager = $this->getManager($account);

        $fromDate = $this->getRequest()->getParam('from_date');
        $toDate   = $this->getRequest()->getParam('to_date');

        try {
            $manager->setFromToDates($fromDate, $toDate);
        } catch (Exception $e) {
            return $this->_addJsonContent(
                array(
                    'result'   => false,
                    'messages' => array(
                        array(
                            'type' => 'error',
                            'text' => $e->getMessage()
                        )
                    )
                )
            );
        }

        return $this->_addJsonContent(array('result' => true));
    }

    //########################################

    protected function getManager(Ess_M2ePro_Model_Account $account)
    {
        switch ($account->getComponentMode()) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Amazon_Order_UploadByUser_Manager');
                break;

            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Ebay_Order_UploadByUser_Manager');
                break;

            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $manager = Mage::getModel('M2ePro/Cron_Task_Walmart_Order_UploadByUser_Manager');
                break;
        }

        /** @var AmazonManager|EbayManager|WalmartManager $manager */
        $manager->setIdentifierByAccount($account);
        return $manager;
    }

    //########################################
}
