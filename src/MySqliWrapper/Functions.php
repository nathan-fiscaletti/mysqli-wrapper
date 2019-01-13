<?php

if (! function_exists('mysqliwrapper__preparePropertyValue')) {
    function mysqliwrapper__preparePropertyValue($value)
    {
        return (is_string($value))
            ? "'$value'"
            : "$value";
    }
}

if (! function_exists('mysqliwrapper__asQueryBindType')) {
    function mysqliwrapper__asQueryBindType($value)
    {
        if (is_string($value))
            return 's';

        if (is_float($value))
            return 'd';

        if (is_int($value))
            return 'i';

        return 'b';
    }
}

if (! function_exists('mysqliwrapper__selectableToString')) {
    function mysqliwrapper__selectableToString($what) {
        if (is_array($what)) {
            return implode(','.PHP_EOL, $what);
        }

        return $what;
    }
}