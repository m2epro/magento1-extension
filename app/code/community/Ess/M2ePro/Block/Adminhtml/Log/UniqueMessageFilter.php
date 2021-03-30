<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Log_UniqueMessageFilter extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/log/uniqueMessageFilter.phtml');
    }

    public function getParamName()
    {
        return 'only_unique_messages';
    }

    public function getFilterUrl()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $params = array();
        } else {
            $params = $this->getRequest()->getParams();
        }

        if ($this->isChecked()) {
            $params[$this->getParamName()] = 0;
        } else {
            $params[$this->getParamName()] = 1;
        }

        return $this->getUrl($this->getData('route'), $params);
    }

    public function isChecked()
    {
        return $this->getRequest()->getParam($this->getParamName(), true);
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    //########################################
}
