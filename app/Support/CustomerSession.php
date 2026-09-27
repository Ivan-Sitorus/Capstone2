<?php

namespace App\Support;

use App\Models\Order;

/**
 * Anonymous customer identity bound to the Laravel session.
 *
 * The customer (mahasiswa) flow has no real authentication: the identity form
 * and the order-placement endpoint bind the submitted name/phone to the
 * session, and any order created by that session is recorded. Every
 * customer-facing read/write is then authorised against this session state so
 * that one browser session can never read or mutate another customer's order.
 *
 * Phone matching is the identity control; the recorded order-id list is the
 * precise ownership control that also covers phone-less orders. Both live in
 * the server-side session, which the client cannot forge.
 */
final class CustomerSession
{
    public const PHONE_KEY = 'customer_phone';

    public const NAME_KEY = 'customer_name';

    public const ORDERS_KEY = 'customer_orders';

    /**
     * Bind the submitted customer identity to the current session.
     *
     * Empty values never overwrite a previously bound identity, so a later
     * phone-less submission cannot silently widen access.
     */
    public static function bind(?string $name, ?string $phone): void
    {
        $phone = self::normalize($phone);
        $name = is_string($name) ? trim($name) : '';

        if ($phone !== null) {
            session([self::PHONE_KEY => $phone]);
        }

        if ($name !== '') {
            session([self::NAME_KEY => $name]);
        }
    }

    /**
     * Record that this session created the given order.
     */
    public static function rememberOrder(Order $order): void
    {
        $owned = array_map('strval', (array) session(self::ORDERS_KEY, []));
        $id = (string) $order->getKey();

        if (! in_array($id, $owned, true)) {
            $owned[] = $id;
        }

        session([self::ORDERS_KEY => array_values($owned)]);
    }

    public static function phone(): ?string
    {
        return self::normalize(session(self::PHONE_KEY));
    }

    public static function name(): ?string
    {
        $name = session(self::NAME_KEY);

        return is_string($name) && trim($name) !== '' ? trim($name) : null;
    }

    /**
     * Determine whether the current session owns the given order.
     *
     * Ownership is true when the order was created in this session, or when
     * both the session and the order carry the same non-empty phone (compared
     * in constant time). Two phone-less orders never match each other.
     */
    public static function owns(Order $order): bool
    {
        $owned = array_map('strval', (array) session(self::ORDERS_KEY, []));

        if (in_array((string) $order->getKey(), $owned, true)) {
            return true;
        }

        $phone = self::phone();
        $orderPhone = $order->phone;

        return $phone !== null
            && is_string($orderPhone)
            && $orderPhone !== ''
            && hash_equals($phone, $orderPhone);
    }

    private static function normalize(mixed $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $phone = trim($phone);

        return $phone === '' ? null : $phone;
    }
}
