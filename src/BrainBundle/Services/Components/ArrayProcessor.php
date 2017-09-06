<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 05-Sep-17
 * Time: 15:22
 */

namespace BrainBundle\Services\Components;

class ArrayProcessor
{
    /**
     * Get random weighted element
     * @param array $weightedValues
     * @return int|mixed|string
     */
    function getRandomWeightedElement(array $weightedValues)
    {
        $rand = mt_rand(0, (int)array_sum($weightedValues));

        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }

        return "";
    }
}