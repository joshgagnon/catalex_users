<?php namespace App\Library;

class Billing {

	/**
	 * Calculate the included GST component given an inclusive total. Works with string
	 * representations of numbers to be easily intergrated with bc math.
	 *
	 * @param  string $totalAmount
	 * @return string
	 */
	public static function includingGst($totalAmount) {
		return bcmul($totalAmount, '0.13043478260869565217', 2);
	}

	/**
	 * Convert number of cents to a string with the dollar amount
	 */
    public static function centsToDollars($cents)
    {
        $centsInDollar = 100;
        $decimalPlaces = 2;

        return number_format(($cents / $centsInDollar), $decimalPlaces);
    }
}
