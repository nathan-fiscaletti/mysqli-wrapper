<?php

if (! function_exists('mysqliwrapper__asQueryBindType')) {
    /**
     * Retrieve the corresponding query bind type string
     * based on the type of the value.
     *
     * @param mixed $value
     *
     * @return string
     */
    function mysqliwrapper__asQueryBindType($value)
    {
        if (is_string($value)) {
            return 's';
        }

        if (is_float($value)) {
            return 'd';
        }

        if (is_int($value)) {
            return 'i';
        }

        return 'b';
    }
}

if (! function_exists('mysqliwrapper__selectableToString')) {
    /**
     * Convert a selectable parameter to a MySql string.
     *
     * @param array|string $what
     *
     * @return string
     */
    function mysqliwrapper__selectableToString($what)
    {
        if (is_array($what)) {
            return '`'.implode('`, `', $what).'`';
        }

        return $what;
    }
}
