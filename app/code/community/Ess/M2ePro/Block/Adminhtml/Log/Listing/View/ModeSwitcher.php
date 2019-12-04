<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Log_Listing_View_ModeSwitcher extends Mage_Adminhtml_Block_Widget
{
    const VIEW_MODE_SEPARATED = 'separated';
    const VIEW_MODE_GROUPED   = 'grouped';
    const DEFAULT_VIEW_MODE   = self::VIEW_MODE_SEPARATED;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setId('logViewModeSwitcher');
    }

    protected function _toHtml()
    {
        $data = array(
            'current_view_mode' => $this->getCurrentViewMode(),
            'route' => '*/*/' . $this->getRoute(),
            'items' => $this->getMenuItems()
        );

        $modeChangeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_grid_modeSwitcher_log');
        $modeChangeBlock->setData($data);
        $modeChangeLabel = Mage::helper('M2ePro')->__('View Mode');

        return <<<HTML
<div class="view-mode-switcher"><b>{$modeChangeLabel}: </b>{$modeChangeBlock->toHtml()}</div>
HTML;
    }

    protected function getMenuItems()
    {
        return array(
            array(
                'value' => 'separated',
                'label' => Mage::helper('M2ePro')->__('Separated')
            ),
            array(
                'value' => 'grouped',
                'label' => Mage::helper('M2ePro')->__('Grouped')
            )
        );
    }

    protected function getCurrentViewMode()
    {
        if (!isset($this->_data['current_view_mode'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('View Mode is not set.');
        }

        return $this->_data['current_view_mode'];
    }

    protected function getRoute()
    {
        if (!isset($this->_data['route'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Route is not set.');
        }

        return $this->_data['route'];
    }

    //########################################
}
