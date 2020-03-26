<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method setPageHelpLink($link)
 * @method getPageHelpLink()
 */
class Ess_M2ePro_Block_Adminhtml_General extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsPhp */
    protected $_jsPhp;

    /** @var Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsTranslator */
    protected $_jsTranslator;

    /** @var Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsUrls */
    protected $_jsUrls;

    /** @var Ess_M2ePro_Block_Adminhtml_Magento_Renderer_Js */
    protected $_js;

    /** @var Ess_M2ePro_Block_Adminhtml_Magento_Renderer_Css */
    protected $_css;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('generalHtml');
        $this->setTemplate('M2ePro/general.phtml');

        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/', 'show_block_notices'
        );

        $this->_jsPhp        = Mage::getBlockSingleton('M2ePro/adminhtml_magento_renderer_jsPhp');
        $this->_jsTranslator = Mage::getBlockSingleton('M2ePro/adminhtml_magento_renderer_jsTranslator');
        $this->_jsUrls       = Mage::getBlockSingleton('M2ePro/adminhtml_magento_renderer_jsUrls');
        $this->_js           = Mage::getBlockSingleton('M2ePro/adminhtml_magento_renderer_js');
        $this->_css          = Mage::getBlockSingleton('M2ePro/adminhtml_magento_renderer_css');

        if (!$this->getRequest()->isAjax()) {
            $this->initRenderData();
        }
    }

    //########################################

    protected function initRenderData()
    {
        $this->_js->add(
            <<<JS
var M2ePro = {};

M2ePro.url        = new UrlHandler();
M2ePro.php        = new PhpHandler();
M2ePro.translator = new TranslatorHandler();

ControlPanelHandlerObj = new ControlPanelHandler();

// backward compatibility
M2ePro.text       = {};
M2ePro.formData   = {};
M2ePro.customData = {};
JS
        );

        $this->_jsUrls->addControllerActions('adminhtml_general');
        $this->_jsUrls->add($this->getSkinUrl('M2ePro'), 'm2epro_skin_url');
        $this->_jsUrls->add($this->getUrl('M2ePro/adminhtml_controlPanel/index'), 'm2epro_control_panel');

        $this->_jsTranslator->addTranslations(
            array(
                'Are you sure?',
                'Help',
                'Assign',
                'Attention',
                'Not Set',
                'Hide Block',
                'Show Tips',
                'Hide Tips',
                'Notice',
                'Error',
                'Close',
                'Success',
                'Warning',
                'None',
                'Cancel',
                'Confirm',
                'In Progress',
                'Product(s)',

                'Please select the Products you want to perform the Action on.',
                'Please select Items.',
                'Please select Action.',
                'View All Product Log',
                'This is a required field.',
                'Please enter valid UPC',
                'Please enter valid EAN',
                'Please enter valid ISBN',
                'Invalid input data. Decimal value required. Example 12.05',
                'Invalid input data. Integer value required. Example 12',
                'Invalid date time format string.',
                'Invalid date format string.',
                'Please enter a valid number value in a specified range.',

                'Create a New One...',
                'Creation of New Magento Attribute',
                'Unauthorized! Please login again.',

                'Settings have been saved.',
                'Preparing to start. Please wait ...',
                'Synchronization has successfully ended.',
                'Synchronization ended with warnings. <a target="_blank" href="%url%">View Log</a> for details.',
                'Synchronization ended with errors. <a target="_blank" href="%url%">View Log</a> for details.',
            )
        );
    }

    //########################################

    public function getJsPhp()
    {
        return $this->_jsPhp;
    }

    public function getJsTranslator()
    {
        return $this->_jsTranslator;
    }

    public function getJsUrls()
    {
        return $this->_jsUrls;
    }

    public function getJs()
    {
        return $this->_js;
    }

    public function getCss()
    {
        return $this->_css;
    }

    //########################################
}
