<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_NotCreatedFilter extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/not_created_filter.phtml');
    }

    public function getParamName()
    {
        return 'not_created_only';
    }

    public function getFilterUrl()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = array();
        } else {
            $params = $this->getRequest()->getParams();
        }

        $tabId = Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::getTabIdByComponent(
            $this->getData('component_mode')
        );

        if (!is_null($tabId)) {
            $params['tab'] = $tabId;
        }

        if ($this->isChecked()) {
            unset($params[$this->getParamName()]);
        } else {
            $params[$this->getParamName()] = true;
        }

        return $this->getUrl('*/'.$this->getData('controller').'/*', $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName());
    }

    //########################################
}