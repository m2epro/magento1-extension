<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use Ess_M2ePro_Model_Requirements_Semver_Constraint_Constraint as Constraint;

/**
 * Defines a constraint.
 */
class Ess_M2ePro_Model_Requirements_Semver_Constraint_Constraint
    implements Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface
{
    /* operator integer values */
    const OP_EQ = 0;
    const OP_LT = 1;
    const OP_LE = 2;
    const OP_GT = 3;
    const OP_GE = 4;
    const OP_NE = 5;

    /**
     * Operator to integer translation table.
     *
     * @var array
     */
    private static $_transOpStr = array(
        '=' => self::OP_EQ,
        '==' => self::OP_EQ,
        '<' => self::OP_LT,
        '<=' => self::OP_LE,
        '>' => self::OP_GT,
        '>=' => self::OP_GE,
        '<>' => self::OP_NE,
        '!=' => self::OP_NE,
    );

    /**
     * Integer to operator translation table.
     *
     * @var array
     */
    private static $_transOpInt = array(
        self::OP_EQ => '==',
        self::OP_LT => '<',
        self::OP_LE => '<=',
        self::OP_GT => '>',
        self::OP_GE => '>=',
        self::OP_NE => '!=',
    );

    /** @var string */
    protected $_operator;

    /** @var string */
    protected $_version;

    /** @var string */
    protected $_prettyString;

    /**
     * @param Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface $provider
     *
     * @return bool
     */
    public function matches(Ess_M2ePro_Model_Requirements_Semver_Constraint_ConstraintInterface $provider)
    {
        if ($provider instanceof $this) {
            return $this->matchSpecific($provider);
        }

        // turn matching around to find a match
        return $provider->matches($this);
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
     * Get all supported comparison operators.
     *
     * @return array
     */
    public static function getSupportedOperators()
    {
        return array_keys(self::$_transOpStr);
    }

    /**
     * Sets operator and version to compare with.
     *
     * @param string $operator
     * @param string $version
     *
     * @throws \InvalidArgumentException if invalid operator is given.
     */
    public function __construct($operator, $version)
    {
        if (!isset(self::$_transOpStr[$operator])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid operator "%s" given, expected one of: %s',
                    $operator,
                    implode(', ', self::getSupportedOperators())
                )
            );
        }

        $this->_operator = self::$_transOpStr[$operator];
        $this->_version  = $version;
    }

    /**
     * @param string $a
     * @param string $b
     * @param string $operator
     * @param bool $compareBranches
     *
     * @throws \InvalidArgumentException if invalid operator is given.
     *
     * @return bool
     */
    public function versionCompare($a, $b, $operator, $compareBranches = false)
    {
        if (!isset(self::$_transOpStr[$operator])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid operator "%s" given, expected one of: %s',
                    $operator,
                    implode(', ', self::getSupportedOperators())
                )
            );
        }

        $aIsBranch = 'dev-' === substr($a, 0, 4);
        $bIsBranch = 'dev-' === substr($b, 0, 4);

        if ($aIsBranch && $bIsBranch) {
            return $operator === '==' && $a === $b;
        }

        // when branches are not comparable, we make sure dev branches never match anything
        if (!$compareBranches && ($aIsBranch || $bIsBranch)) {
            return false;
        }

        return version_compare($a, $b, $operator);
    }

    /**
     * @param Constraint $provider
     * @param bool $compareBranches
     *
     * @return bool
     */
    public function matchSpecific(Constraint $provider, $compareBranches = false)
    {
        $noEqualOp = str_replace('=', '', self::$_transOpInt[$this->_operator]);
        $providerNoEqualOp = str_replace('=', '', self::$_transOpInt[$provider->_operator]);

        $isEqualOp = self::OP_EQ === $this->_operator;
        $isNonEqualOp = self::OP_NE === $this->_operator;
        $isProviderEqualOp = self::OP_EQ === $provider->_operator;
        $isProviderNonEqualOp = self::OP_NE === $provider->_operator;

        // '!=' operator is match when other operator is not '==' operator or version is not match
        // these kinds of comparisons always have a solution
        if ($isNonEqualOp || $isProviderNonEqualOp) {
            return !$isEqualOp && !$isProviderEqualOp
                || $this->versionCompare($provider->_version, $this->_version, '!=', $compareBranches);
        }

        // an example for the condition is <= 2.0 & < 1.0
        // these kinds of comparisons always have a solution
        if ($this->_operator !== self::OP_EQ && $noEqualOp === $providerNoEqualOp) {
            return true;
        }

        if ($this->versionCompare(
            $provider->_version, $this->_version,
            self::$_transOpInt[$this->_operator], $compareBranches
        )) {
            // special case, e.g. require >= 1.0 and provide < 1.0
            // 1.0 >= 1.0 but 1.0 is outside of the provided interval
            if ($provider->_version === $this->_version
                && self::$_transOpInt[$provider->_operator] === $providerNoEqualOp
                && self::$_transOpInt[$this->_operator] !== $noEqualOp) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::$_transOpInt[$this->_operator] . ' ' . $this->_version;
    }

    public function getVersion()
    {
        $replace = Ess_M2ePro_Model_Requirements_Semver_VersionParser::$stabilities;
        $replace[] = '-';

        return str_replace($replace, '', $this->_version);
    }
}