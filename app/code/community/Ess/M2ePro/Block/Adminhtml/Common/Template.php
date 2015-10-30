<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Template extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $nick;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('commonTemplate');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_template';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Policy'),
            'onclick'   => '',
            'class'     => 'add add-button-drop-down'
        ));
        // ---------------------------------------
    }

    //########################################

    protected function getAddButtonJavascript()
    {
        $data = array(
            'target_css_class' => 'add-button-drop-down',
            'items'            => array(
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_common_template/new',
                        array(
                            'channel' => $this->nick,
                            'type' => Ess_M2ePro_Block_Adminhtml_Common_Template_Grid::TEMPLATE_SELLING_FORMAT,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Selling Format')
                ),
                array(
                    'url'   => $this->getUrl(
                        '*/adminhtml_common_template/new',
                        array(
                            'channel' => $this->nick,
                            'type' => Ess_M2ePro_Block_Adminhtml_Common_Template_Grid::TEMPLATE_SYNCHRONIZATION,
                        )
                    ),
                    'label' => Mage::helper('M2ePro')->__('Synchronization')
                )
            )
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
    });

</script>

JAVASCIRPT;

        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_template_tabs');
        $hideChannels = '';

        $tabsIds = $tabsBlock->getTabsIds();

        if (count($tabsIds) <= 1) {
            $hideChannels = ' style="visibility: hidden"';
        }

        return $javascript .
            $this->getAddButtonJavascript() .
            parent::_toHtml() . <<<HTML
<div class="content-header">
    <table cellspacing="0">
        <tr>
            <td{$hideChannels}>{$tabsBlock->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
<div id="template_tabs_container"></div>
HTML;

    }

    //########################################
}