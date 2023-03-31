<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountCreate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_account';
        $this->_mode = 'create';

        $this->_headerText = Mage::helper('M2ePro')->__("Add Account");

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        $this->_addButton(
            'continue',
            array(
                'label' => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'AmazonAccountCreateObj.continueClick();',
                'class' => 'close'
            )
        );
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'The specified Title is already used for other Account. Account Title must be unique.' =>
                    Mage::helper('M2ePro')->__(
                        'The specified Title is already used for other Account. Account Title must be unique.'
                    )
            )
        );

        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addConstants(
            Mage::helper('M2ePro')->getClassConstants('Ess_M2ePro_Helper_Component_Amazon'),
            'Ess_M2ePro_Helper_Component_Amazon'
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_amazon_account');

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    AmazonAccountCreateObj = new AmazonAccountCreate();
JS
        );

        return parent::_prepareLayout();
    }
}
