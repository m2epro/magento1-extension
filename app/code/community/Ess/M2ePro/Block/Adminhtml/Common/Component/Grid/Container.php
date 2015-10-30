<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Component_Grid_Container
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    abstract protected function getAmazonNewUrl();

    abstract protected function getBuyNewUrl();

    //########################################

    protected function getAddButtonOnClickAction()
    {
        $components = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();
        $action = '';

        if (count($components) == 1) {
            $component = reset($components);
            $action = 'setLocation(\''.$this->getNewUrl($component).'\');';
        }

        return $action;
    }

    //########################################

    protected function _toHtml()
    {
        return $this->getAddButtonJavascript() . parent::_toHtml();
    }

    // ---------------------------------------

    protected function getAddButtonJavascript()
    {
        if (count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) < 2) {
            return '';
        }

        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => $this->getAddButtonDropDownItems()
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    protected function getAddButtonDropDownItems()
    {
        $items = array();

        $activeComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponentsTitles();

        // ---------------------------------------
        foreach ($activeComponents as $component => $title) {
            $items[] = array(
                'url' => $this->getNewUrl($component),
                'label' => $title
            );
        }
        // ---------------------------------------

        return $items;
    }

    //########################################

    protected function getNewUrl($component)
    {
        $component = ucfirst(strtolower($component));
        $method = "get{$component}NewUrl";

        if (!method_exists($this, $method)) {
            throw new Ess_M2ePro_Model_Exception('Method of adding a new entity is not defined.');
        }

        return $this->$method();
    }

    //########################################
}