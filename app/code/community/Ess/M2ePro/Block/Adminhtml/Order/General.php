<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_General extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    private $gridIds = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/general.phtml');
    }

    //########################################

    public function setGridIds(array $gridIds = array())
    {
        $this->gridIds = $gridIds;
        return $this;
    }

    public function getGridIds()
    {
        return $this->gridIds;
    }

    //########################################
}