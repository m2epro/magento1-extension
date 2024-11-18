<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Analytics extends Ess_M2ePro_Model_Servicing_Task
{
    /** In bytes. It is equal 1 Mb */
    const REQUEST_SIZE_MAX = 1048576;

    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'analytics';
    }

    //########################################

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry')->isPlannedNow();
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        try {
            return $this->collectAnalytics();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return array('analytics' => array());
        }
    }

    public function processResponseData(array $data)
    {
        return null;
    }

    //########################################

    protected function collectAnalytics()
    {
        /** @var Ess_M2ePro_Model_Servicing_Task_Analytics_Registry $registry */
        $registry   = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_Registry');
        $serializer = Mage::getSingleton('M2ePro/Servicing_Task_Analytics_EntityManager_Serializer');

        if (!$registry->getStartedAt()) {
            $registry->markStarted();
        }

        $progress = array();
        $entities = array();

        foreach ($this->getEntitiesTypes() as $component => $entitiesTypes) {
            foreach ($entitiesTypes as $entityType) {

                /** @var Ess_M2ePro_Model_Servicing_Task_Analytics_EntityManager $manager */
                $manager = Mage::getModel(
                    'M2ePro/Servicing_Task_Analytics_EntityManager', array(
                    'component'  => $component,
                    'entityType' => $entityType
                    )
                );

                $progress[$manager->getEntityKey()] = false;

                if ($manager->isCompleted()) {
                    $progress[$manager->getEntityKey()] = true;
                    continue;
                }

                $iteration = 0;
                foreach ($manager->getEntities() as $item) {
                    /** @var Ess_M2ePro_Model_Abstract $item */

                    if ($iteration && $iteration % 10 === 0 && $this->isEntitiesPackFull($entities)) {
                        break 3;
                    }

                    $entities[] = $serializer->serialize($item, $manager);
                    $manager->setLastProcessedId($item->getId());
                    $iteration++;
                }
            }
        }

        if (!in_array(false, $progress)) {
            $registry->markFinished();
        }

        return array(
            'analytics' => array(
                'entities'     => $entities,
                'planned_at'   => $registry->getPlannedAt(),
                'started_at'   => $registry->getStartedAt(),
                'finished_at'  => $registry->getFinishedAt(),
            )
        );
    }

    //########################################

    protected function getEntitiesTypes()
    {
        return array(
            Ess_M2ePro_Helper_Component_Amazon::NICK => array(
                'Account',
                'Listing',
                'Template_Synchronization',
                'Template_SellingFormat',
                'Amazon_Template_ProductTaxCode',
                'Amazon_Template_Shipping',
            ),
            Ess_M2ePro_Helper_Component_Ebay::NICK => array(
                'Account',
                'Listing',
                'Template_Synchronization',
                'Template_Description',
                'Template_SellingFormat',
                'Ebay_Template_ReturnPolicy',
                'Ebay_Template_Payment',
                'Ebay_Template_Shipping',
                'Ebay_Template_Category',
            ),
            Ess_M2ePro_Helper_Component_Walmart::NICK => array(
                'Account',
                'Listing',
                'Template_Synchronization',
                'Template_Description',
                'Template_SellingFormat',
            )
        );
    }

    protected function isEntitiesPackFull(&$entities)
    {
        $dataSize = strlen(Mage::helper('M2ePro')->jsonEncode($entities));
        return $dataSize > self::REQUEST_SIZE_MAX;
    }

    //########################################
}
