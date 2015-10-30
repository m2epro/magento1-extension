<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_LogsClearing_Form
    extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationLogsClearingForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/logsClearing.phtml');

        // ---------------------------------------

        $this->setPageHelpLink('Global+Settings#GlobalSettings-LogsClearing');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_logsClearing/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/LogClearingHandler.js');
    }

    protected function _beforeToHtml()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $tasks = array(
            Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS,
            Ess_M2ePro_Model_Log_Clearing::LOG_OTHER_LISTINGS,
            Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS,
            Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS
        );

        // ---------------------------------------
        $modes = array();
        $days  = array();

        foreach ($tasks as $task) {
            $modes[$task] = $config->getGroupValue('/logs/clearing/'.$task.'/','mode');
            $days[$task] = $config->getGroupValue('/logs/clearing/'.$task.'/','days');
        }

        $this->modes = $modes;
        $this->days = $days;
        // ---------------------------------------

        foreach ($tasks as $task) {
            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'LogClearingHandlerObj.runNowLog(\'' . $task . '\')',
                'class'   => 'run_now_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('run_now_'.$task, $buttonBlock);
            // ---------------------------------------

            if ($task == Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS) {
                continue;
            }

            // ---------------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                'onclick' => 'LogClearingHandlerObj.clearAllLog(\'' . $task . '\')',
                'class'   => 'clear_all_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('clear_all_'.$task, $buttonBlock);
            // ---------------------------------------
        }

        return parent::_beforeToHtml();
    }

    //########################################
}