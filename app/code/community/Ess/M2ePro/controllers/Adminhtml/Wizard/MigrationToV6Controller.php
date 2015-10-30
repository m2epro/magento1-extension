<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_MigrationToV6Controller
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //########################################

    protected function getNick()
    {
        return 'migrationToV6';
    }

    protected function getMenuRootNodeNick()
    {
        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0) {
            return Ess_M2ePro_Helper_View_Ebay::NICK;
        }

        return Ess_M2ePro_Helper_View_Common::NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0) {
            return Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel();
        }

        return Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel();
    }

    //########################################

    protected function getCustomViewNick()
    {
        return count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0 ?
                Ess_M2ePro_Helper_View_Ebay::NICK : Ess_M2ePro_Helper_View_Common::NICK;
    }

    protected function _isAllowed()
    {
        $menuNickTemp = count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0 ?
                            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK :
                            Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK;
        return Mage::getSingleton('admin/session')->isAllowed($menuNickTemp);
    }

    //########################################

    public function indexAction()
    {
        if ($this->isNotStarted()) {
            return $this->_redirect('*/*/welcome');
        }

        if ($this->isActive()) {
            return $this->_redirect('*/*/installation');
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        if ($this->getCustomViewNick() == Ess_M2ePro_Helper_View_Ebay::NICK) {
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        return $this->_redirect('*/adminhtml_common_listing/index');
    }

    public function installationAction()
    {
        Mage::helper('M2ePro/Module_Wizard')->getWizard('migrationToV6')->removeEmptySteps();

        if ($this->isFinished() || $this->isNotStarted()) {
            return $this->_redirect('*/*/index');
        }

        if (!$this->getCurrentStep()) {
            $this->setStep($this->getFirstStep());
        }

        $this->_forward($this->getCurrentStep());
    }

    public function saveSellingFormatCurrenciesAction()
    {
        $postParam = $this->getRequest()->getPost('form_data');

        $response = array(
            'success' => true,
            'next_step' => $this->getNextStep()
        );
        $this->getResponse()->setBody(json_encode($response));

        if (is_null($postParam)) {
            return;
        }

        parse_str($postParam, $data);

        !empty($data['ebay']) && $this->saveEbaySellingFormatData($data['ebay']);
        !empty($data['amazon']) && $this->saveAmazonSellingFormatData($data['amazon']);
        !empty($data['buy']) && $this->saveBuySellingFormatData($data['buy']);
    }

    //########################################

    private function renderSimpleStep()
    {
        return $this->_initAction()
                    ->_addContent($this->getWizardHelper()->createBlock(
                        'installation_'.$this->getCurrentStep(),
                        $this->getNick())
                    )
                    ->renderLayout();
    }

    //########################################

    public function introAction()
    {
        $this->renderSimpleStep();
    }

    public function sellingFormatCurrenciesAction()
    {
        $this->renderSimpleStep();
    }

    public function notificationsAction()
    {
        $this->renderSimpleStep();
    }

    //########################################

    protected function saveEbaySellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'start_price_coefficient',
            'reserve_price_coefficient',
            'buyitnow_price_coefficient'
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_ebay_template_selling_format');
    }

    protected function saveAmazonSellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'price_coefficient',
            'sale_price_coefficient',
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_amazon_template_selling_format');
    }

    protected function saveBuySellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'price_coefficient',
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_buy_template_selling_format');
    }

    // ---------------------------------------

    protected function saveSellingFormatData($data, $coefficientIds, $tableName)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        foreach ($data as $coefficient => $value) {
            foreach ($coefficientIds as $coefficientId) {
                if (strpos($coefficient, $coefficientId) !== 0) {
                    continue;
                }

                $templateId = (int)str_replace($coefficientId.'_', '', $coefficient);

                $connWrite->update(
                    $tableName,
                    array($coefficientId => $value),
                    array('template_selling_format_id = ?' => $templateId)
                );
            }
        }
    }

    //########################################
}