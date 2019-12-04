<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface as ConstraintInterface;
use Ess_M2ePro_Model_Requirements_Semver_Constraint_MultiConstraint as MultiConstraint;
use Ess_M2ePro_Model_Requirements_Semver_Constraint_Constraint as Constraint;

class Ess_M2ePro_Model_Requirements_Checks_PhpVersion extends Ess_M2ePro_Model_Requirements_Checks_Abstract
{
    const NICK = 'PhpVersion';

    //########################################

    public function isMeet()
    {
        try {
            return Ess_M2ePro_Model_Requirements_Semver_Semver::satisfies(
                $this->getReal(), $this->getCompatibilityPattern()
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    //########################################

    public function getMin()
    {
        /** @var Constraint[] $constraints */
        $constraints = $this->collectConstraints(
            $this->getVersionParser()->parseConstraints($this->getCompatibilityPattern())
        );

        $minVersion = null;
        foreach ($constraints as $constraint) {
            if ($minVersion === null || $constraint->versionCompare($constraint->getVersion(), $minVersion, '<')) {
                $minVersion = $constraint->getVersion();
            }
        }

        return $minVersion === null ? $this->getCompatibilityPattern() : $minVersion;
    }

    public function getReal()
    {
        return Mage::helper('M2ePro/Client')->getPhpVersion();
    }

    //########################################

    public function getCompatibilityPattern()
    {
        return $this->getReader()->gePhpVersionData();
    }

    //########################################

    protected function collectConstraints(ConstraintInterface $constraint)
    {
        if ($constraint instanceof Constraint) {
            return array($constraint);
        }

        $constraints = array();

        if ($constraint instanceof MultiConstraint) {
            foreach ($constraint->getConstraints() as $constraintChild) {
                $constraints = array_merge($constraints, $this->collectConstraints($constraintChild));
            }
        }

        return $constraints;
    }

    //########################################
}
