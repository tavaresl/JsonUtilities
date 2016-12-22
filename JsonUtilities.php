<?php
/**
 * Class JsonUtilities, which provides an interface between your code and json specs.<br>
 * Basically, what JsonUtilities does is parse the given parameter, which can be of any type, to a json format, <br>
 * encoded in UTF-8. Numeric strings will be parsed to their int or float equivalent values, <br>
 * and boolean will be parsed to integer.
 */
abstract class JsonUtilities
{
    public static function parse($source)
    {
        if (is_array($source))
            return self::fromArray($source);
        elseif (is_object($source))
            return self::fromObject($source);
        else
            return '[]';
    }
    private static function fromArray($array)
    {
        $string = '[';
        for ($i = 0, $separator = ''; $i < count($array); $i++, $separator = ',') {
            $string .= $separator . self::fromObject($array[$i]);
        }
        $string .= ']';
        return $string;
    }
    private static function fromObject($object)
    {
        $reflection = new ReflectionClass($object);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $vector = (array)$object;
        $values = array_values($vector);
        $string = '{';

        for ($i = 0, $separator = ''; $i < count($properties); $i++, $separator = ',') {
            $key = (string)$properties[$i]->name;
            $value = "";
            if (is_null($values[$i]))
                continue;
            elseif (is_string($values[$i]) && !mb_detect_encoding($values[$i], 'UTF-8', true))
                $value .= "\"".utf8_encode($values[$i])."\"";
            elseif (is_numeric($values[$i]) && intval($values[$i] == $values[$i]))
                $value .= intval($values[$i]);
            elseif (is_numeric($values[$i]) && floatval($values[$i] == $values[$i]))
                $value .= floatval($values[$i]);
            elseif (is_object($values[$i]))
                $value .= self::fromObject($values[$i]);
            elseif (is_array($values[$i]))
                $value .= self::fromArray($values[$i]);
            else
                $value .= "\"{$values[$i]}\"";
            $string .= $separator . "\"{$key}\":{$value}";
        }

        $string .= '}';
        return $string;
    }
}
