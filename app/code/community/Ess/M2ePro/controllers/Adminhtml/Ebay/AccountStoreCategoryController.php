<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_AccountStoreCategoryController extends
    Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    public function refreshAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $account = Mage::getModel('M2ePro/Ebay_Account')->loadInstance($accountId);

        Mage::getModel('M2ePro/Ebay_Account_Store_Category_Update')->process($account);
    }

    public function getTreeAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $categoriesTreeArray = Mage::getModel('M2ePro/Ebay_Account')
            ->loadInstance($accountId)
            ->buildEbayStoreCategoriesTree();

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($categoriesTreeArray));
    }
}
