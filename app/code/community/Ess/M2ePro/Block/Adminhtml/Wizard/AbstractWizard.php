<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_AbstractWizard extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/wizard.phtml';

    //########################################

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()
            ->addClassConstants('Ess_M2ePro_Helper_Module_Wizard');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addUrls(
            array(
                'setStep'   => $this->getUrl('*/adminhtml_wizard_'.$this->getNick().'/setStep'),
                'setStatus' => $this->getUrl('*/adminhtml_wizard_'.$this->getNick().'/setStatus')
            )
        );

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'Step' => 'Step',
                'Completed' => 'Completed',
                'Note: If you close the Wizard, it never starts again.
                You will be required to set all Settings manually. Press Cancel to continue working with Wizard.' =>
                    'Note: If you close the Wizard, it never starts again.
                    You will be required to set all Settings manually. Press Cancel to continue working with Wizard.',
            )
        );

        $step = Mage::helper('M2ePro/Module_Wizard')->getStep($this->getNick());
        $steps = Mage::helper('M2ePro')->jsonEncode(
            Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getSteps()
        );
        $status = Mage::helper('M2ePro/Module_Wizard')->getStatus($this->getNick());

        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(
            <<<JS
        WizardObj = new Wizard('{$status}', '{$step}');
        WizardObj.steps.all = {$steps};
JS
        );

        return parent::_prepareLayout();
    }

    //########################################
}
