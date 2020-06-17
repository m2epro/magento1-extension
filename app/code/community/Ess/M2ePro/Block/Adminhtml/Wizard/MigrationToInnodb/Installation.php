<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToInnodb_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_AbstractWizard
{
    protected function _beforeToHtml()
    {
        $this->setId('wizard' . $this->getNick() . $this->getStep());
        $this->_headerText = Mage::helper('M2ePro')->__($this->getHeaderTextHtml());

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton(
            'continue', array(
                'id'      => 'update_all_marketplaces',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'MigrationToInnodbObj.continueStep();',
                'class'   => 'primary forward',
            )
        );

        return parent::_beforeToHtml();
    }

    protected function getHeaderTextHtml()
    {
        return Mage::helper('M2ePro')->__('Marketplace Synchronization');
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions(
            "adminhtml_wizard_{$this->getNick()}"
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            array(
                'ebay_marketplace/synchGetExecutingInfo' =>
                    $this->getUrl('*/adminhtml_ebay_marketplace/synchGetExecutingInfo'),
                'amazon_marketplace/synchGetExecutingInfo' =>
                    $this->getUrl('*/adminhtml_amazon_marketplace/synchGetExecutingInfo'),
                'walmart_marketplace/synchGetExecutingInfo' =>
                    $this->getUrl('*/adminhtml_walmart_marketplace/synchGetExecutingInfo')
            )
        );

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
        window.MarketplaceSynchProgressObj = new WizardMigrationToInnodbMarketplaceSynchProgress(
            new ProgressBar('marketplaces_progress_bar'),
            new AreaWrapper('marketplaces_content_container')
        );
    
         window.MigrationToInnodbObj = new WizardMigrationToInnodb();  
JS
        );

        return parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock', 'wizard.help.block', array(
                'no_collapse' => true,
                'no_hide'     => true
            )
        );

        $contentBlock = Mage::helper('M2ePro/Module_Wizard')->createBlock(
            "installation_{$this->getStep()}_content",
            $this->getNick()
        );

        return
            '<div id="marketplaces_progress_bar"></div>' .
            '<div id="marketplaces_content_container">' .
             parent::_toHtml() .
             $helpBlock->toHtml() .
             $contentBlock->toHtml() .
            '</div>';
    }

    //########################################
}
