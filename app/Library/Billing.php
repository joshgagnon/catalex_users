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
}
