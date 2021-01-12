<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Manager
{
    const MODE_PARENT   = 0;
    const MODE_CUSTOM   = 1;
    const MODE_TEMPLATE = 2;

    const COLUMN_PREFIX = 'template';

    const OWNER_LISTING = 'listing';
    const OWNER_LISTING_PRODUCT = 'listing_product';

    const TEMPLATE_RETURN_POLICY   = 'return_policy';
    const TEMPLATE_PAYMENT         = 'payment';
    const TEMPLATE_SHIPPING        = 'shipping';
    const TEMPLATE_DESCRIPTION     = 'description';
    const TEMPLATE_SELLING_FORMAT  = 'selling_format';
    const TEMPLATE_SYNCHRONIZATION = 'synchronization';

    protected $_ownerObject  = null;
    protected $_templateNick = null;
    protected $_resultObject = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing|Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getOwnerObject()
    {
        return $this->_ownerObject;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing|Ess_M2ePro_Model_Ebay_Listing_Product $object
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setOwnerObject($object)
    {
        if (!($object instanceof Ess_M2ePro_Model_Ebay_Listing) &&
            !($object instanceof Ess_M2ePro_Model_Ebay_Listing_Product)) {
            throw new Ess_M2ePro_Model_Exception('Owner object is out of knowledge range.');
        }

        $this->_ownerObject = $object;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function isListingOwner()
    {
        return $this->getOwnerObject() instanceof Ess_M2ePro_Model_Ebay_Listing;
    }

    /**
     * @return bool
     */
    public function isListingProductOwner()
    {
        return $this->getOwnerObject() instanceof Ess_M2ePro_Model_Ebay_Listing_Product;
    }

    //########################################

    /**
     * @return null|string
     */
    public function getTemplate()
    {
        return $this->_templateNick;
    }

    /**
     * @param string $nick
     * @return $this
     * @throws Ess_M2ePro_Model_Exception
     */
    public function setTemplate($nick)
    {
        if (!in_array(strtolower($nick), $this->getAllTemplates())) {
            throw new Ess_M2ePro_Model_Exception('Policy nick is out of knowledge range.');
        }

        $this->_templateNick = strtolower($nick);
        return $this;
    }

    //########################################

    /**
     * @return array
     */
    public function getAllTemplates()
    {
        return array(
            self::TEMPLATE_RETURN_POLICY,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_PAYMENT,
            self::TEMPLATE_DESCRIPTION,
            self::TEMPLATE_SELLING_FORMAT,
            self::TEMPLATE_SYNCHRONIZATION
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isFlatTemplate()
    {
        return in_array($this->getTemplate(), $this->getFlatTemplates());
    }

    /**
     * @return array
     */
    public function getFlatTemplates()
    {
        return array(
            self::TEMPLATE_RETURN_POLICY,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_PAYMENT,
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isHorizontalTemplate()
    {
        return in_array($this->getTemplate(), $this->getHorizontalTemplates());
    }

    /**
     * @return array
     */
    public function getHorizontalTemplates()
    {
        return array(
            self::TEMPLATE_SELLING_FORMAT,
            self::TEMPLATE_SYNCHRONIZATION,
            self::TEMPLATE_DESCRIPTION
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMarketplaceDependentTemplate()
    {
        return in_array($this->getTemplate(), $this->getMarketplaceDependentTemplates());
    }

    /**
     * @return array
     */
    public function getMarketplaceDependentTemplates()
    {
        return array(
            self::TEMPLATE_PAYMENT,
            self::TEMPLATE_SHIPPING,
            self::TEMPLATE_RETURN_POLICY,
        );
    }

    public function getNotMarketplaceDependentTemplates()
    {
        return array_diff($this->getAllTemplates(), $this->getMarketplaceDependentTemplates());
    }

    //########################################

    /**
     * @return string
     */
    public function getModeColumnName()
    {
        return self::COLUMN_PREFIX.'_'.$this->getTemplate().'_mode';
    }

    /**
     * @return string
     */
    public function getTemplateIdColumnName()
    {
        return self::COLUMN_PREFIX.'_'.$this->getTemplate().'_id';
    }

    //########################################

    public function getIdColumnValue()
    {
        if ($this->isModeParent()) {
            return null;
        }

        return $this->getOwnerObject()->getData($this->getTemplateIdColumnName());
    }

    //########################################

    public function getModeValue()
    {
        return $this->getOwnerObject()->getData($this->getModeColumnName());
    }

    public function getTemplateIdValue()
    {
        return $this->getOwnerObject()->getData($this->getTemplateIdColumnName());
    }

    //########################################

    public function getParentResultObject()
    {
        if ($this->isListingOwner()) {
            return null;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
        $manager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $manager->setTemplate($this->getTemplate());
        $manager->setOwnerObject($this->getOwnerObject()->getEbayListing());

        return $manager->getResultObject();
    }

    public function getTemplateResultObject()
    {
        $id = $this->getTemplateIdValue();

        if ($id === null) {
            return null;
        }

        return $this->makeResultObject($id);
    }

    // ---------------------------------------

    protected function makeResultObject($id)
    {
        $modelName = $this->getTemplateModelName();

        if ($this->isHorizontalTemplate()) {
            $object = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                $modelName, $id, null, array('template')
            );
        } else {
            $object = Mage::helper('M2ePro')->getCachedObject(
                $modelName, $id, null, array('template')
            );
        }

        return $object;
    }

    //########################################

    /**
     * @return bool
     */
    public function isModeParent()
    {
        return $this->getModeValue() == self::MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isModeCustom()
    {
        return $this->getModeValue() == self::MODE_CUSTOM;
    }

    /**
     * @return bool
     */
    public function isModeTemplate()
    {
        return $this->getModeValue() == self::MODE_TEMPLATE;
    }

    //########################################

    public function getResultObject()
    {
        if ($this->_resultObject !== null) {
            return $this->_resultObject;
        }

        if ($this->isListingProductOwner() && $this->isModeParent()) {
            $this->_resultObject = $this->getParentResultObject();
        } else {
            $this->_resultObject = $this->getTemplateResultObject();
        }

        if ($this->_resultObject === null) {
            throw new Ess_M2ePro_Model_Exception('Unable to get result object.');
        }

        return $this->_resultObject;
    }

    //########################################

    /**
     * @return null|string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTemplateModelName()
    {
        $name = null;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
                $name = 'Ebay_Template_Payment';
                break;
            case self::TEMPLATE_SHIPPING:
                $name = 'Ebay_Template_Shipping';
                break;
            case self::TEMPLATE_RETURN_POLICY:
                $name = 'Ebay_Template_ReturnPolicy';
                break;
            case self::TEMPLATE_SELLING_FORMAT:
                $name = 'Template_SellingFormat';
                break;
            case self::TEMPLATE_DESCRIPTION:
                $name = 'Template_Description';
                break;
            case self::TEMPLATE_SYNCHRONIZATION:
                $name = 'Template_Synchronization';
                break;
        }

        if ($name === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $name;
    }

    public function getTemplateModel($returnChildModel = false)
    {
        $model = null;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
            case self::TEMPLATE_SHIPPING:
            case self::TEMPLATE_RETURN_POLICY:
                $model = Mage::getModel('M2ePro/'.$this->getTemplateModelName());
                break;

            case self::TEMPLATE_SELLING_FORMAT:
            case self::TEMPLATE_SYNCHRONIZATION:
            case self::TEMPLATE_DESCRIPTION:
                if ($returnChildModel) {
                    $modelPath = ucfirst(Ess_M2ePro_Helper_Component_Ebay::NICK).'_'.$this->getTemplateModelName();
                    $model = Mage::getModel('M2ePro/'.$modelPath);
                } else {
                    $model = Mage::helper('M2ePro/Component')->getComponentModel(
                        Ess_M2ePro_Helper_Component_Ebay::NICK,
                        $this->getTemplateModelName()
                    );
                }
                break;
        }

        if ($model === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $model;
    }

    public function getTemplateCollection()
    {
        $collection = null;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
            case self::TEMPLATE_SHIPPING:
            case self::TEMPLATE_RETURN_POLICY:
                $collection = $this->getTemplateModel()->getCollection();
                break;

            case self::TEMPLATE_SELLING_FORMAT:
            case self::TEMPLATE_SYNCHRONIZATION:
            case self::TEMPLATE_DESCRIPTION:
                $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    $this->getTemplateModelName()
                );
                break;
        }

        if ($collection === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $collection;
    }

    public function getTemplateBuilder()
    {
        $model = null;

        switch ($this->getTemplate()) {
            case self::TEMPLATE_PAYMENT:
                $model = Mage::getModel('M2ePro/Ebay_Template_Payment_Builder');
                break;
            case self::TEMPLATE_SHIPPING:
                $model = Mage::getModel('M2ePro/Ebay_Template_Shipping_Builder');
                break;
            case self::TEMPLATE_RETURN_POLICY:
                $model = Mage::getModel('M2ePro/Ebay_Template_ReturnPolicy_Builder');
                break;
            case self::TEMPLATE_SELLING_FORMAT:
                $model = Mage::getModel('M2ePro/Ebay_Template_SellingFormat_Builder');
                break;
            case self::TEMPLATE_DESCRIPTION:
                $model = Mage::getModel('M2ePro/Ebay_Template_Description_Builder');
                break;
            case self::TEMPLATE_SYNCHRONIZATION:
                $model = Mage::getModel('M2ePro/Ebay_Template_Synchronization_Builder');
                break;
        }

        if ($model === null) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf('Template nick "%s" is unknown.', $this->getTemplate()));
        }

        return $model;
    }

    //########################################

    /**
     * @param string $ownerObjectModel
     * @param int $templateId
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedOwnerObjects($ownerObjectModel, $templateId, $asArrays = true, $columns = '*')
    {
        /* @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection($ownerObjectModel);
        if ($ownerObjectModel === self::OWNER_LISTING) {
            $collection->getSelect()->where(
                "{$this->getTemplateIdColumnName()} = (?)",
                array($templateId)
            );
        } else {
            $collection->getSelect()->where(
                "{$this->getModeColumnName()} IN (?) AND {$this->getTemplateIdColumnName()} = {$templateId}",
                array(self::MODE_CUSTOM, self::MODE_TEMPLATE)
            );
        }

        if (is_array($columns) && !empty($columns)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$collection->getData() : (array)$collection->getItems();
    }

    public function getTemplatesFromData($data)
    {
        $resultTemplates = array();

        foreach ($this->getAllTemplates() as $template) {
            $this->setTemplate($template);

            $templateMode = $data[$this->getModeColumnName()];

            if ($templateMode == self::MODE_PARENT) {
                $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $data['listing_id']);
                $templateId   = $listing->getData($this->getTemplateIdColumnName());
            } else {
                $templateId = $data[$this->getTemplateIdColumnName()];
            }

            $templateModelName = $this->getTemplateModelName();

            if ($this->isHorizontalTemplate()) {
                $templateModel = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject($templateModelName, $templateId, null, array('template'))
                    ->getChildObject();
            } else {
                $templateModel = Mage::helper('M2ePro')->getCachedObject(
                    $templateModelName, $templateId, null, array('template')
                );
            }

            $resultTemplates[$template] = $templateModel;
        }

        return $resultTemplates;
    }

    //########################################
}
