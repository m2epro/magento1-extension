<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_referrer;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode      = 'support';

        $this->setId('supportContainer');
        $this->_headerText = Mage::helper('M2ePro')->__('Help Center');

        $this->_referrer = $this->getRequest()->getParam('referrer');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/9000117126');
        $this->_addButton(
            'knowledge_base',
            array(
                'label'   => Mage::helper('M2ePro')->__('Knowledge Base'),
                'onclick' => "window.open('{$url}', '_blank'); return false;",
                'class'   => 'button_link'
            )
        );

        if ($this->_referrer === null) {
            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, '');
        } else if ($this->_referrer == Ess_M2ePro_Helper_View_Ebay::NICK) {
            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Ebay::NICK);
        } else if ($this->_referrer == Ess_M2ePro_Helper_View_Amazon::NICK) {
            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Amazon::NICK);
        } else if ($this->_referrer == Ess_M2ePro_Helper_View_Walmart::NICK) {
            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Walmart::NICK);
        }

        $this->_addButton(
            'goto_docs',
            array(
                'label'   => Mage::helper('M2ePro')->__('Documentation'),
                'onclick' => "window.open('{$url}', '_blank'); return false;",
                'class'   => 'button_link'
            )
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_support');

        parent::_prepareLayout();
    }

    //########################################
}
