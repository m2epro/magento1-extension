<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    protected function getSaveConfirmationText($id = null)
    {
        $saveConfirmation = '';

        if (is_null($id)) {
            $id = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId();
        }

        if ($id) {
            $saveConfirmation = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('<br/>
<b>Note:</b> All changes you have made will be automatically applied to all M2E Pro Listings where this Policy is used.'
                )
            );
        }

        return $saveConfirmation;
    }

    //########################################

    protected function _toHtml()
    {
        $translations = Mage::helper('M2ePro')->jsonEncode(array(
            'Do not show any more' => Mage::helper('M2ePro')->__('Do not show any more')
        ));

        $confirmationBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_widget_dialog_confirm')
            ->toHtml();

        $html = <<<HTML
        <script>
            M2ePro.translator.add({$translations});
        </script>
        <div style="display: none;">
            {$confirmationBlock}
        </div>
HTML
;
        return parent::_toHtml() . $html;
    }

    //########################################
}