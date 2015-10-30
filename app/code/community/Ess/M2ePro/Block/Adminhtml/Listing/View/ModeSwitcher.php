<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_ModeSwitcher extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/listing/view/mode_switcher.phtml';

    //########################################

    public function getItems()
    {
        if (empty($this->_data['items']) || !is_array($this->_data['items'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Items are not set.');
        }

        return $this->_data['items'];
    }

    public function getCurrentViewMode()
    {
        if (empty($this->_data['current_view_mode'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Current View Mode is not set.');
        }

        return $this->_data['current_view_mode'];
    }

    public function getRoute()
    {
        if (empty($this->_data['route'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Route is not set.');
        }

        return $this->_data['route'];
    }

    public function getSwitchUrl()
    {
        $params = array();

        if ($id = $this->getRequest()->getParam('id')) {
            $params['id'] = $id;
        }

        $params['view_mode'] = '%view_mode%';

        return $this->getUrl($this->getRoute(), $params);
    }

    //########################################
}