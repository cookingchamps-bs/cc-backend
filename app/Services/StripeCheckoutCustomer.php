<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class StripeCheckoutCustomer
{
    /**
     * Params to merge into Stripe Checkout Session::create so email/name/address are prefilled.
     *
     * @return array{customer?: string, customer_email?: string}
     */
    public static function sessionParams(User $user, ?Address $address = null): array
    {
        $email = trim((string) ($user->email ?? ''));
        if ($email === '') {
            return [];
        }

        Stripe::setApiKey(Helper::getStripeSecret());

        $name = self::displayName($user, $address);
        $phone = self::phone($user);
        $shipping = self::shippingPayload($address, $name);

        try {
            $customerId = self::upsertCustomer($user, $email, $name, $phone, $shipping);

            return ['customer' => $customerId];
        } catch (\Throwable $e) {
            Log::warning('Stripe customer upsert failed; falling back to customer_email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return ['customer_email' => $email];
        }
    }

    private static function upsertCustomer(
        User $user,
        string $email,
        ?string $name,
        ?string $phone,
        ?array $shipping
    ): string {
        $payload = array_filter([
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
        ], fn ($value) => $value !== null && $value !== '');

        if ($shipping !== null) {
            $payload['shipping'] = $shipping;
            $payload['address'] = $shipping['address'];
        }

        $existingId = $user->stripe_customer_id;

        if (! empty($existingId)) {
            try {
                Customer::update($existingId, $payload);

                return $existingId;
            } catch (InvalidRequestException $e) {
                // Stale / deleted customer id — create a fresh one below.
                Log::info('Stripe customer missing; recreating', [
                    'user_id' => $user->id,
                    'stripe_customer_id' => $existingId,
                ]);
            }
        }

        $payload['metadata'] = ['user_id' => (string) $user->id];

        $customer = Customer::create($payload);
        $user->forceFill(['stripe_customer_id' => $customer->id])->save();

        return $customer->id;
    }

    private static function displayName(User $user, ?Address $address): ?string
    {
        $fromUser = trim(implode(' ', array_filter([
            $user->first_name ?? null,
            $user->last_name ?? null,
        ])));

        if ($fromUser !== '') {
            return $fromUser;
        }

        $fromAddress = trim((string) ($address->name ?? ''));

        return $fromAddress !== '' ? $fromAddress : null;
    }

    private static function phone(User $user): ?string
    {
        $mobile = trim((string) ($user->mobile_number ?? ''));
        if ($mobile === '') {
            return null;
        }

        $code = trim((string) ($user->country_code ?? ''));

        return $code !== '' ? $code.$mobile : $mobile;
    }

    private static function shippingPayload(?Address $address, ?string $fallbackName): ?array
    {
        if ($address === null) {
            return null;
        }

        $line1 = trim((string) ($address->address ?? ''));
        if ($line1 === '') {
            return null;
        }

        $name = trim((string) ($address->name ?? ''));
        if ($name === '') {
            $name = $fallbackName ?: 'Customer';
        }

        return [
            'name' => $name,
            'address' => [
                'line1' => $line1,
                'country' => 'AU',
            ],
        ];
    }
}
