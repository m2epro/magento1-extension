<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support extends Mage_Adminhtml_Block_Widget_Form_Container
{
    private $referrer = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('supportContainer');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';

        $this->_mode = 'support';
        $this->referrer = $this->getRequest()->getParam('referrer');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $m2eProVersion = '<span style="color: #777; font-size: small; font-weight: normal">' .
                            '(M2E Pro ver. '.Mage::helper('M2ePro/Module')->getVersion().')' .
                         '</span>';
        $this->_headerText = Mage::helper('M2ePro')->__('Support') . " {$m2eProVersion}";
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = Mage::helper('M2ePro/View_Development')->getPageUrl();
        $this->_addButton('goto_development', array(
            'label'     => 'Control Panel',
            'onclick'   => 'window.location = \''.$url.'\'',
            'class'     => 'button_link development',
            'style'     => 'display: none;'
        ));
        // ---------------------------------------

        // ---------------------------------------
        if (is_null($this->referrer)) {

            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();

            $this->_addButton('goto_docs', array(
                'label' => Mage::helper('M2ePro')->__('Documentation'),
                'onclick' => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                'class' => 'button_link'
            ));

        } else {

            if ($this->referrer == Ess_M2ePro_Helper_View_Ebay::NICK) {

                $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Ebay::NICK);

                $this->_addButton('goto_docs', array(
                    'label'     => Mage::helper('M2ePro')->__('Documentation'),
                    'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                    'class'     => 'button_link'
                ));

            } else if ($this->referrer == Ess_M2ePro_Helper_View_Amazon::NICK) {

                $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Amazon::NICK);

                $this->_addButton('goto_docs', array(
                    'label'     => Mage::helper('M2ePro')->__('Documentation'),
                    'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                    'class'     => 'button_link'
                ));
            }
        }
        // ---------------------------------------
    }

    //########################################
}