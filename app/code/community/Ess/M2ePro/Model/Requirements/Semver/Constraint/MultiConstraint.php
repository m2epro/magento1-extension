<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Defines a conjunctive or disjunctive set of constraints.
 */

use Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface as ConstraintInterface;

class Ess_M2ePro_Model_Requirements_Semver_Constraint_MultiConstraint implements ConstraintInterface
{
    /** @var ConstraintInterface[] */
    protected $_constraints;

    /** @var string */
    protected $_prettyString;

    /** @var bool */
    protected $_conjunctive;

    /**
     * @param ConstraintInterface[] $constraints A set of constraints
     * @param bool $conjunctive Whether the constraints should be treated as conjunctive or disjunctive
     */
    public function __construct(array $constraints, $conjunctive = true)
    {
        $this->_constraints = $constraints;
        $this->_conjunctive = $conjunctive;
    }

    /**
     * @return ConstraintInterface[]
     */
    public function getConstraints()
    {
        return $this->_constraints;
    }

    /**
     * @return bool
     */
    public function isConjunctive()
    {
        return $this->_conjunctive;
    }

    /**
     * @return bool
     */
    public function isDisjunctive()
    {
        return !$this->_conjunctive;
    }

    /**
     * @param ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches(ConstraintInterface $provider)
    {
        if (false === $this->_conjunctive) {
            foreach ($this->_constraints as $constraint) {
                if ($constraint->matches($provider)) {
                    return true;
                }
            }

            return false;
        }

        foreach ($this->_constraints as $constraint) {
            if (!$constraint->matches($provider)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $prettyString
     */
    public function setPrettyString($prettyString)
    {
        $this->_prettyString = $prettyString;
    }

    /**
     * @return string
     */
    public function getPrettyString()
    {
        if ($this->_prettyString) {
            return $this->_prettyString;
        }

        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $constraints = array();
        foreach ($this->_constraints as $constraint) {
            $constraints[] = (string) $constraint;
        }

        return '[' . implode($this->_conjunctive ? ' ' : ' || ', $constraints) . ']';
    }
}