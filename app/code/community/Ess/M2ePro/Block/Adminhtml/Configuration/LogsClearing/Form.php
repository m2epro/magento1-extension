<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Block_Adminhtml_Configuration_LogsClearing_Form
 *
 * @method Ess_M2ePro_Helper_Order_Notification getOrderNotificationHelper()
 */
class Ess_M2ePro_Block_Adminhtml_Configuration_LogsClearing_Form
    extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('configurationLogsClearingForm');
        $this->setTemplate('M2ePro/configuration/logsClearing.phtml');
        $this->setPageHelpLink("global-settings");
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'config_edit_form',
                'action'  => $this->getUrl('M2ePro/adminhtml_configuration_logsClearing/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/LogClearing.js');
    }

    protected function _beforeToHtml()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $tasks = array(
            Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS,
            Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS,
            Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS,
        );

        $modes = array();
        $days = array();

        foreach ($tasks as $task) {
            $modes[$task] = $config->getGroupValue('/logs/clearing/' . $task . '/', 'mode');
            $days[$task] = $config->getGroupValue('/logs/clearing/' . $task . '/', 'days');
        }

        $this->modes = $modes;
        $this->days = $days;
        // ---------------------------------------

        foreach ($tasks as $task) {
            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'LogClearingObj.runNowLog(\'' . $task . '\')',
                'class'   => 'run_now_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('run_now_' . $task, $buttonBlock);
            // ---------------------------------------

            if ($task == Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS) {
                continue;
            }

            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                'onclick' => 'LogClearingObj.clearAllLog(\'' . $task . '\')',
                'class'   => 'clear_all_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('clear_all_' . $task, $buttonBlock);
            // ---------------------------------------
        }

        $this->setData('order_notification_helper', Mage::helper('M2ePro/Order_Notification'));

        return parent::_beforeToHtml();
    }

    //########################################
}
