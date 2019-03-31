<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_AccountPickupStoreController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('My Stores'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Ebay/PickupStoreHandler.js');

        $this->_initPopUp();
        $this->setPageHelpLink();

        return $this;
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_configuration', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_ACCOUNT_PICKUP_STORE)
            ))->renderLayout();
    }

    public function pickupStoreGridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id', 0);
        $model = Mage::getSingleton('M2ePro/Ebay_Account_PickupStore')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Store does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_accountPickupStore/index');
        }

        $formData = Mage::helper('M2ePro/Data_Session')->getValue('pickupStore_form_data', true);

        if (!empty($formData) && is_array($formData)) {
            $model->addData($formData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);
        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = (int)$this->getRequest()->getParam('id', 0);

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: general
        // ---------------------------------------
        $keys = array(
            'name',
            'location_id',
            'account_id',
            'marketplace_id',
            'phone',
            'url',
            'pickup_instruction'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: location
        // ---------------------------------------
        $keys = array(
            'country',
            'region',
            'city',
            'postal_code',
            'address_1',
            'address_2',
            'latitude',
            'longitude',
            'utc_offset'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        // tab: businessHours
        // ---------------------------------------
        $data['business_hours'] = Mage::helper('M2ePro')->jsonEncode($post['business_hours']);
        $data['special_hours'] = '';

        $tmpSpecialHours = $post['special_hours'];
        if (!empty($tmpSpecialHours['date_settings']) && is_array($tmpSpecialHours['date_settings'])) {
            foreach ($tmpSpecialHours['date_settings'] as $date => $time) {
                if (empty($date) || $date == '0000-00-00') {
                    unset($tmpSpecialHours['date_settings'][$date]);
                }
            }

            !empty($tmpSpecialHours['date_settings']) &&
                $data['special_hours'] = Mage::helper('M2ePro')->jsonEncode($tmpSpecialHours);
        }
        // ---------------------------------------

        // tab: stockSettings
        // ---------------------------------------
        $keys = array(
            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($post['default_mode']) && $post['default_mode'] == 0) {
            $data['qty_mode'] = Ess_M2ePro_Model_Ebay_Account_PickupStore::QTY_MODE_SELLING_FORMAT_TEMPLATE;
        }
        // ---------------------------------------

        // creating of pickup store
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component_Ebay_PickupStore')->validateRequiredFields($data)) {

            Mage::helper('M2ePro/Data_Session')->setValue('pickupStore_form_data', $data);

            $this->getSession()->addError(Mage::helper('M2ePro')->__(
                'Validation error. You must fill all required fields.'
            ));

            return $id ? $this->_redirect('*/*/edit', array('id' => $id))
                       : $this->_redirect('*/*/new');
        }

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'store','add','entity',
                Mage::helper('M2ePro/Component_Ebay_PickupStore')->prepareRequestData($data),
                NULL, NULL, $data['account_id']
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            Mage::helper('M2ePro/Data_Session')->setValue('pickupStore_form_data', $data);

            $this->getSession()->addError(Mage::helper('M2ePro')->__(
                'The New Store has not been created. <br/>Reason: %error_message%', $exception->getMessage()
            ));

            return $id ? $this->_redirect('*/*/edit', array('id' => $id))
                       : $this->_redirect('*/*/new');
        }
        // ---------------------------------------

        $model = Mage::getModel('M2ePro/Ebay_Account_PickupStore');
        if ($id) {
            $model->load($id);
            $model->addData($data);
        } else {
            $model->setData($data);
        }
        $model->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Store was successfully saved.'));
        $this->_redirectUrl(
            Mage::helper('M2ePro')->getBackUrl(
                'list', array(), array('edit' => array('id' => $model->getId()))
            )
        );
    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id', 0);

        if (!$id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Store does not exist.'));
            return $this->_redirect('*/*/index');
        }

        /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore $model */
        $model = Mage::getModel('M2ePro/Ebay_Account_PickupStore')->load($id);

        if (!$model->getId()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Store does not exists.')
            );
            return $this->_redirect('*/adminhtml_ebay_accountPickupStore/index');
        }

        if ($model->isLocked()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Store used in Listing.')
            );
            return $this->_redirect('*/adminhtml_ebay_accountPickupStore/index');
        }

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'store', 'delete', 'entity',
                array(
                    'location_id' => $model->getLocationId()
                ),
                NULL, NULL, $model->getAccountId()
            );

            $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'The Store has not been deleted. <br/>Reason: %error_message%', $exception->getMessage()
            ));
            $this->_redirect('*/adminhtml_ebay_accountPickupStore/index');
        }

        $model->deleteInstance();

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Store was successfully deleted.')
        );

        return $this->_redirect('*/adminhtml_ebay_accountPickupStore/index');
    }

    //#############################################

    public function getRegionsAction()
    {
        $countryCode = $this->getRequest()->getParam('country_code');

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            Mage::helper('M2ePro/Magento')->getRegionsByCountryCode($countryCode)
        ));
    }

    //#############################################

    public function validateLocationAction()
    {
        $locationData = array(
            'country',
            'region',
            'city',
            'address_1',
            'address_2',
            'postal_code',
            'latitude',
            'longitude',
            'utc_offset',
        );

        $pickupStoreCollection = Mage::getModel('M2ePro/Ebay_Account_PickupStore')->getCollection();

        $idValue = (int)$this->getRequest()->getParam('id', 0);
        if (!empty($idValue)) {
            $pickupStoreCollection->addFieldToFilter('id', array('nin'=>array($idValue)));
        }

        foreach ($locationData as $locationItem) {
            $tempField = $this->getRequest()->getParam($locationItem, '');
            if (!empty($tempField)) {
                if ($locationItem == 'latitude' || $locationItem == 'longitude') {
                    $pickupStoreCollection->addFieldToFilter($locationItem, array('like' => $tempField));
                    continue;
                }

                $pickupStoreCollection->addFieldToFilter($locationItem, $tempField);
            }
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            array('result'=>!(bool)$pickupStoreCollection->getSize())
        ));
    }

    //#############################################
}