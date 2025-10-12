<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Currency data transfer object.
 *
 * Handles Twenty CRM currency format which stores amounts in micros (10^6).
 */
class Currency
{
    /**
     * Currency constructor.
     *
     * @param int $amountMicros
     *   The amount in micros (1 unit = 1,000,000 micros).
     * @param string $currencyCode
     *   The ISO 4217 currency code (e.g., 'USD', 'EUR', 'GBP').
     */
    public function __construct(
        private int $amountMicros,
        private string $currencyCode = 'USD',
    ) {
    }

    /**
     * Create Currency from array data (API format).
     *
     * @param array $data
     *   The currency data from API.
     *
     * @return self
     *   The Currency instance.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amountMicros: $data['amountMicros'] ?? 0,
            currencyCode: $data['currencyCode'] ?? 'USD',
        );
    }

    /**
     * Create Currency from standard amount (converts to micros).
     *
     * @param float $amount
     *   The amount in standard currency units (e.g., 50000.00 for $50,000).
     * @param string $currencyCode
     *   The ISO 4217 currency code.
     *
     * @return self
     *   The Currency instance.
     */
    public static function fromAmount(float $amount, string $currencyCode = 'USD'): self
    {
        return new self(
            amountMicros: (int) round($amount * 1000000),
            currencyCode: $currencyCode,
        );
    }

    /**
     * Convert Currency to array (API format).
     *
     * @return array
     *   The currency data as array.
     */
    public function toArray(): array
    {
        return [
            'amountMicros' => $this->amountMicros,
            'currencyCode' => $this->currencyCode,
        ];
    }

    // Getters

    /**
     * Get the amount in micros.
     *
     * @return int
     *   The amount in micros.
     */
    public function getAmountMicros(): int
    {
        return $this->amountMicros;
    }

    /**
     * Get the amount in standard currency units.
     *
     * @return float
     *   The amount (e.g., 50000.00 for $50,000).
     */
    public function getAmount(): float
    {
        return $this->amountMicros / 1000000;
    }

    /**
     * Get the currency code.
     *
     * @return string
     *   The ISO 4217 currency code.
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * Get formatted currency string.
     *
     * @param int $decimals
     *   Number of decimal places (default: 2).
     *
     * @return string
     *   Formatted amount with currency code (e.g., "$50,000.00 USD").
     */
    public function getFormatted(int $decimals = 2): string
    {
        $amount = $this->getAmount();
        $symbol = $this->getCurrencySymbol();

        return sprintf(
            '%s%s %s',
            $symbol,
            number_format($amount, $decimals),
            $this->currencyCode
        );
    }

    /**
     * Get currency symbol based on currency code.
     *
     * @return string
     *   The currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return match ($this->currencyCode) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CHF' => 'CHF ',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'CNY' => '¥',
            'INR' => '₹',
            default => '',
        };
    }

    // Setters

    /**
     * Set the amount in micros.
     *
     * @param int $amountMicros
     *   The amount in micros.
     *
     * @return self
     */
    public function setAmountMicros(int $amountMicros): self
    {
        $this->amountMicros = $amountMicros;

        return $this;
    }

    /**
     * Set the amount in standard currency units (converts to micros).
     *
     * @param float $amount
     *   The amount in standard units.
     *
     * @return self
     */
    public function setAmount(float $amount): self
    {
        $this->amountMicros = (int) round($amount * 1000000);

        return $this;
    }

    /**
     * Set the currency code.
     *
     * @param string $currencyCode
     *   The ISO 4217 currency code.
     *
     * @return self
     */
    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * Convert to string.
     *
     * @return string
     *   Formatted currency string.
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }
}
