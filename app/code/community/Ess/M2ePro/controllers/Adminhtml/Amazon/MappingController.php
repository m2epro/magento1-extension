<?php

class Ess_M2ePro_Adminhtml_Amazon_MappingController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    private function initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Mapping'));

        $this->_initPopUp();

        return $this;
    }

    public function indexAction()
    {
        $this->initAction();
        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_configuration',
                '',
                array(
                    'active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_MAPPING
                )
            )
        );

        $this->renderLayout();
    }

    public function saveAction()
    {
        $attributes = $this->getRequest()->getParam('attributes');
        if (!empty($attributes)) {
            $this->mapAttributes($attributes);
            $this->_getSession()->addSuccess(
                $this->__('Settings saved')
            );
        }

        return $this->_redirect('*/*/index');
    }

    /**
     * @param list<int, string> $attributesList
     * @return void
     */
    private function mapAttributes($attributesList)
    {
        /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingService $mappingService */
        $mappingService = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingService');
        foreach ($attributesList as $attributeMappingId => $magentoCode) {
            $mappingService->updateMagentoAttributeCode($attributeMappingId, $magentoCode);
        }
    }
}
