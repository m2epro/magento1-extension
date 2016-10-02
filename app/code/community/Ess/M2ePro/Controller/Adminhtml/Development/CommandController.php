<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Development_CommandController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/View_Development')->getPageRoute());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    //########################################

    protected function getStyleHtml()
    {
        $baseUrl = Mage::helper('M2ePro/Magento')->getBaseUrl();

        return <<<HTML
<script type="text/javascript" src="{$baseUrl}js/prototype/prototype.js"></script>

<style type="text/css">

    table.grid {
        border-color: black;
        border-style: solid;
        border-width: 1px 0 0 1px;
    }
    table.grid th {
        padding: 5px 20px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
        background-color: silver;
        color: white;
        font-weight: bold;
    }
    table.grid td {
        padding: 3px 10px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
    }

</style>
HTML;
    }

    //########################################
}