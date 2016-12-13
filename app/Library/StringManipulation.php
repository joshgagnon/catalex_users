<?php

namespace App\Library;

class StringManipulation
{
    /**
     * Take a collection of words and put them in to a list that can be displayed in text.
     *
     * Examples:
     * One item: one | Two items: one and two | Three items: one, two, and three
     */
    public static function buildCommaList(\Illuminate\Database\Eloquent\Collection $collection)
    {
        $commaList = '';

        if ($collection->count() == 0) {
            throw new \Exception('Cannot build comma list of zero items');
        } else if ($collection->count() == 1) {
            $commaList = $collection[0];
        } else if ($collection->count() == 2) {
            $commaList = $collection[0] . ' and ' . $collection[1];
        } else {
            $lastItem = $collection->pop();
            $commaList = implode(', ', $collection->toArray()) . ', and ' . $lastItem;
        }

        return $commaList;
    }
}
