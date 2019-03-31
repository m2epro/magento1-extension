<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Command_Group extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/development/tabs/command/group.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->commands = Mage::helper('M2ePro/View_Development_Command')->parseGeneralCommandsData(
            $this->getControllerName()
        );

        return parent::_beforeToHtml();
    }

    //########################################

    public function getCommandLauncherHtml(array $commandRow)
    {
        $target = '';
        $commandRow['new_window'] && $target = 'target="_blank"';

        $onClick = '';
        $commandRow['confirm'] && $onClick = "return confirm('{$commandRow['confirm']}');";
        if (!empty($commandRow['prompt']['text']) && !empty($commandRow['prompt']['var'])) {
            $onClick =  <<<JS
var result = prompt('{$commandRow['prompt']['text']}');
if (result) window.location.href = $(this).getAttribute('href') + '?{$commandRow['prompt']['var']}=' + result;
return false;
JS;
        }

        return <<<HTML
<a href="{$commandRow['url']}" {$target}
   onclick="{$onClick}"
   title="{$commandRow['description']}">
{$commandRow['title']}
</a>
HTML;
    }

    //########################################
}