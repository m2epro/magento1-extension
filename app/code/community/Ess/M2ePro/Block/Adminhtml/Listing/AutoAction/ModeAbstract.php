<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_ModeAbstract extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('listingAutoActionMode');
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $data = array(
            'id'      => 'continue_button',
            'class'   => 'next continue_button',
            'onclick' => 'ListingAutoActionObj.addingModeContinue();',
            'label'   => Mage::helper('M2ePro')->__('Continue')
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
    }

    public function isAdminStore()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('listing');
        return $listing->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    public function getWebsiteName()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('listing');
        return Mage::helper('M2ePro/Magento_Store')->getWebsiteName($listing->getStoreId());
    }

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');
        $parentHtml = parent::_toHtml();

        return <<<HTML
<div class="content-header" style="border: none;">
    <table cellspacing="0">
        <tr>
            <td><h3>{$this->getBlockTitle()}</h3></td>
        </tr>
    </table>
</div>

{$this->getHelpBlock()->toHtml()}

<div id="block-content-wrapper" style="margin: 15px 0 0 10px;">
    {$this->getBlockContent()}
</div>

{$parentHtml}

<div style="text-align: right; margin: 20px 0;">
    <a id="cancel_button"
       onclick="ListingAutoActionObj.addingModeCancel();"
       href="javascript: void(0);">{$helper->__('Cancel')}</a>
    &nbsp;&nbsp;&nbsp;
    {$this->getChildHtml('continue_button')}
</div>
HTML;
    }

    // ---------------------------------------

    protected function getBlockTitle()
    {
        return Mage::helper('M2ePro')->__(
            'Choose the level at which Products should be automatically added or deleted'
        );
    }

    protected function getHelpBlock()
    {
        $helpBlock = Mage::app()->getLayout()->createBlock('M2ePro/adminhtml_helpBlock')->setData(
            array(
                'id' => 'block_notice_listing_auto_action_mode',
                'title' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
                'content' => Mage::helper('M2ePro')->__(
                    <<<HTML
Choose the level at which Products should be automatically added or deleted.<br/><br/>

<b>Global</b> will check for Products being added or deleted in Magento Catalog.<br/>
<b>Website</b> will check for Products being added or deleted in Magento Website.<br/>
<b>Category</b> will check for Products being added or deleted in Magento Category.<br/><br/>

The detailed information can be found <a href="%url%" target="_blank">here</a>.
HTML
                    ,
                    $this->getHelpPageUrl()
                )
            )
        );

        return $helpBlock;
    }

    protected function getBlockContent()
    {
        $helper = Mage::helper('M2ePro');

        $form = new Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Form(
            array(
                'id'      => 'edit_form',
                'method'  => 'post',
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $form->addField(
            'global',
            'radio',
            array(
                'name' => 'auto_mode',
                'value' => Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL,
                'after_element_html' => <<<HTML
<span style="padding-left: 5px; font-weight: bold;">
    {$helper->__('Global (all Products)')}
</span>
HTML
            )
        );

        $form->addField(
            'note_global',
            'note',
            array(
                'text' => <<<HTML
<span style="display: inline-block; padding-bottom: 5px;">
    {$helper->__('Acts when a Product is added or deleted from Magento Catalog.')}
</span>
HTML
            )
        );

        if (!$this->isAdminStore()) {
            $form->addField(
                'website',
                'radio',
                array(
                    'name' => 'auto_mode',
                    'value' => Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE,
                    'after_element_html' => <<<HTML
<span style="padding-left: 5px; font-weight: bold;">
    {$helper->__('Website')}&nbsp;({$this->getWebsiteName()})
</span>
HTML
                )
            );

            $form->addField(
                'note_website',
                'note',
                array(
                    'text' => <<<HTML
<span style="display: inline-block; padding-bottom: 5px;">
    {$helper->__('Acts when a Product is added to or deleted from the Website with regard
                 to the Store View specified for the M2E Pro Listing.')}
</span>
HTML
                )
            );
        }

        $form->addField(
            'category',
            'radio',
            array(
                'name' => 'auto_mode',
                'value' => Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY,
                'after_element_html' => <<<HTML
<span style="padding-left: 5px; font-weight: bold;">
    {$helper->__('Category')}
</span>
HTML
            )
        );

        $form->addField(
            'note_category',
            'note',
            array(
                'text' => <<<HTML
<span style="display: inline-block; padding-bottom: 5px;">
    {$helper->__('Acts when the Product is added to or deleted from the selected Magento Category.')}
</span>
HTML
            )
        );

        $form->addField(
            'validation',
            'text',
            array(
                'class' => 'M2ePro-validate-mode',
                'style' => 'display: none;'
            )
        );

        return $form->toHtml();
    }

    //########################################
}
