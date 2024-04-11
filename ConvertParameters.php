<?php

namespace FpDbTest;

use Exception;

class ConvertParameters
{
    /**
     * @throws Exception
     */
    public static function getMethod(mixed $value, string $key = ' '): string
    {
        return match ($key) {
            'd' => 'setIntValue',
            'f' => 'setFloatValue',
            'a' => 'setArrayValue',
            '#' => 'setIdentValue',
            ' ' => match (gettype($value)) {
                'string' => 'setStringValue',
                'integer' => 'setIntValue',
                'double' => 'setFloatValue',
                'boolean' => 'setBoolValue',
                'NULL' => 'setNULLValue',
                default => throw new Exception('Не допустимый тип данных'),
            },
            default => throw new Exception('Не допустимый спецификатор'),
        };
    }

    public static function setIntValue($value): ?int
    {
        return (int)$value;
    }

    public static function setFloatValue($value): ?float
    {
        return (float)$value;
    }

    /**
     * @throws Exception
     */
    public static function setArrayValue(array $value): ?string
    {
        $result = '';
        $withoutKey = array_is_list($value);
        foreach ($value as $key => $item) {
            $method = self::getMethod($item);
            $result .= ($result ? ", " : '') . ($withoutKey ? '' : "`{$key}` = ") . (self::{$method}($item));
        }
        return $result;
    }

    public static function setBoolValue($value): ?int
    {
        return (int)(bool)$value;
    }

    public static function setStringValue($value): ?string
    {
        return "'" . (string)$value . "'";
    }

    public static function setIdentValue($value): string|array
    {
        return '`' . (is_array($value) ? implode('`, `', $value) : $value) . '`';
    }

    public static function setNullValue($value): string
    {
        return 'NULL';
    }

}