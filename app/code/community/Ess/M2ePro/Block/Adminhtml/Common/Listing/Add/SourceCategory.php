<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_SourceCategory
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct($attributes)
    {
        parent::__construct($attributes);

        $this->setData($attributes);
        $component = $this->getData('component');

        // Initialization block
        // ---------------------------------------
        $this->setId($component.'ListingAddStepFourCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_product_category';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__("Select Products");
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        if (is_null($this->getRequest()->getParam('back'))) {
            $url = $this->getUrl('*/adminhtml_common_listing_productAdd/index', array(
                'id' => $this->getRequest()->getParam('id'),
                'component' => $component
            ));
        } else {
            $tab = constant('Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_'.strtoupper($component));
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_common_listing/index',
                array(
                    'tab' => $tab
                )
            );
        }
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('auto_action', array(
            'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
            'onclick'   => 'ListingAutoActionHandlerObj.loadAutoActionHtml();'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'add_category_products()',
            'class'     => 'scalable next'
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_common_listing_autoAction',
            array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => $this->getData('component')
            )
        );
        $urls = json_encode($urls);

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $translations = json_encode(array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
        ));

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    ListingAutoActionHandlerObj = new ListingAutoActionHandler();

</script>
HTML;

        return $viewHeaderBlock->toHtml() . parent::getGridHtml() . $js;
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>'.
            '<div id="add_products_container">'.
            parent::_toHtml() .
            '</div>';
    }

    //########################################
}