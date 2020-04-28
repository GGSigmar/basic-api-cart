<?php

namespace App\Service;

use Money\Money;

class PriceFormatter
{
    /**
     * @param Money $price
     *
     * @return string
     */
    public static function formatMoneyPrice(Money $price): string
    {
        return (string) (round(($price->getAmount() / 100), 2)) . " USD";
    }
}