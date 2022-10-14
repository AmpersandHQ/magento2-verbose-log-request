<?php
declare(strict_types=1);
namespace Ampersand\VerboseLogRequest\Service\IsVerbose;

/**
 * phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
 */
class Storage
{
    /** @var boolean|null */
    private static $isVerbose;

    /**
     * Set the isVerbose flag
     *
     * @param false|true $value
     * @param false|true $replace
     * @return void
     */
    public static function setFlag(bool $value, bool $replace = false)
    {
        if (self::flagIsSet() && !$replace) {
            return;
        }
        self::$isVerbose = $value;
    }

    /**
     * Get the isVerbose Flag
     *
     * @return bool
     */
    public static function getFlag()
    {
        if (!self::flagIsSet()) {
            return false;
        }
        return (bool) self::$isVerbose;
    }

    /**
     * Has the flag been set
     *
     * @return bool
     */
    public static function flagIsSet()
    {
        return (isset(self::$isVerbose));
    }

    /**
     * Used in testing
     *
     * @param false|true $force
     * @return void
     */
    public static function reset(bool $force = false)
    {
        if ($force) {
            self::$isVerbose = null;
        }
    }
}
