<?php

namespace App\Library;

class Billing
{
    const DECIMAL_PLACES = 2;
    const DAYS_IN_TRIAL_PERIOD = 14;
    const PRECISION = 20;

	/**
	 * Calculate the included GST component given an inclusive total. Works with string
	 * representations of numbers to be easily intergrated with bc math.
	 *
	 * @param  string $totalAmount
	 * @return string
	 */
	public static function includingGst($totalAmount)
    {
        // IRD recommended way of calculating GST: (total amount x 3) / 23
        $totalAmountTimesThree = bcmul((string) $totalAmount, '3', self::PRECISION);
        $totalGst = bcdiv($totalAmountTimesThree, '23', self::PRECISION);

        return self::formatDollars($totalGst);
	}
    
    /**
     * Subtract discount for a value
     *
     * @param $amount
     * @param $discountPercent
     *
     * @return string
     */
	public static function applyDiscount($amount, $discountPercent)
    {
        // Calculate the percent the use pays of the amount
        $payPercent = bcsub('100', (string) $discountPercent, self::PRECISION);
        
        // Convert the pay percent to a decimal between 0 and 1
        $payDecimal = bcdiv($payPercent, '100', self::PRECISION);
        
        // Calculate the amount after discount
        $amountAfterDiscount = bcmul((string) $amount, $payDecimal, self::PRECISION);
        
        return number_format($amountAfterDiscount, self::DECIMAL_PLACES, null, '');
    }

	/**
	 * Convert number of cents to a string with the dollar amount
	 */
    public static function centsToDollars($cents)
    {
        $dollars = bcdiv($cents, 100, 2);
        return number_format($dollars, self::DECIMAL_PLACES, null, '');
    }

    /**
     * Format dollars with commas and the correct decimal places
     */
    public static function formatDollars($dollars)
    {
        return number_format($dollars, self::DECIMAL_PLACES);
    }
}
