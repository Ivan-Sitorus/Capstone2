<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

class WhatsAppReceiptService
{
    /**
     * Normalize an Indonesian phone number to international format (62-prefix).
     *
     * Rules:
     *  - Strip all non-digit characters
     *  - Leading "0" → replace with "62"
     *  - Leading "8" (without "62" prefix) → prepend "62"
     *  - Already starts with "62" → keep as-is
     */
    public function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8') && ! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /**
     * Validate a normalized phone number: must be 10-15 digits, all numeric.
     */
    public function validatePhone(string $phone): bool
    {
        return (bool) preg_match('/^[0-9]{10,15}$/', $phone);
    }

    /**
     * Generate a WhatsApp message for the given order.
     *
     * Reads the `receipt_whatsapp_template` setting and replaces the `(link)`
     * placeholder with the receipt URL.
     */
    public function generateMessage(Order $order): string
    {
        $template = Setting::get('receipt_whatsapp_template') ?? '';

        return str_replace('(link)', $order->receipt_url, $template);
    }

    /**
     * Build a wa.me click-to-chat link for the given order and phone number.
     *
     * Normalizes the phone, generates the message, URL-encodes it,
     * and returns the full wa.me link.
     */
    public function buildWaMeLink(Order $order, string $phone): string
    {
        $normalizedPhone = $this->normalizePhone($phone);

        if (! $this->validatePhone($normalizedPhone)) {
            throw new \InvalidArgumentException("Invalid phone number: {$phone}");
        }

        $message = $this->generateMessage($order);
        $encoded = urlencode($message);

        return "https://wa.me/{$normalizedPhone}?text={$encoded}";
    }
}
