<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialAuthVerifier
{
    /**
     * @return array{email: string, subject: string}|null
     */
    public function verify(string $loginType, string $idToken): ?array
    {
        $loginType = strtolower(trim($loginType));

        return match ($loginType) {
            'google' => $this->verifyGoogle($idToken),
            'apple' => $this->verifyApple($idToken),
            default => null,
        };
    }

    /**
     * @return array{email: string, subject: string}|null
     */
    private function verifyGoogle(string $idToken): ?array
    {
        $clientId = config('services.google.client_id');
        if (empty($clientId)) {
            Log::warning('Google client_id is not configured for social login verification.');

            return null;
        }

        $client = new GoogleClient(['client_id' => $clientId]);
        $payload = $client->verifyIdToken($idToken);
        if (! is_array($payload) || empty($payload['email'])) {
            return null;
        }

        if (($payload['email_verified'] ?? false) !== true && ($payload['email_verified'] ?? '') !== 'true') {
            return null;
        }

        return [
            'email' => strtolower((string) $payload['email']),
            'subject' => (string) ($payload['sub'] ?? ''),
        ];
    }

    /**
     * @return array{email: string, subject: string}|null
     */
    private function verifyApple(string $identityToken): ?array
    {
        $parts = explode('.', $identityToken);
        if (count($parts) !== 3) {
            return null;
        }

        $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payloadJson === false) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (! is_array($payload)) {
            return null;
        }

        $audience = config('services.apple.client_id');
        if (empty($audience) || ($payload['aud'] ?? null) !== $audience) {
            return null;
        }

        if (($payload['iss'] ?? null) !== 'https://appleid.apple.com') {
            return null;
        }

        $exp = (int) ($payload['exp'] ?? 0);
        if ($exp < time()) {
            return null;
        }

        if (! $this->verifyAppleJwtSignature($identityToken)) {
            return null;
        }

        $email = $payload['email'] ?? null;
        if (empty($email)) {
            return null;
        }

        return [
            'email' => strtolower((string) $email),
            'subject' => (string) ($payload['sub'] ?? ''),
        ];
    }

    private function verifyAppleJwtSignature(string $jwt): bool
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/'), true) ?: '', true);
        $kid = $header['kid'] ?? null;
        $alg = $header['alg'] ?? null;
        if ($kid === null || $alg !== 'RS256') {
            return false;
        }

        try {
            $keysResponse = Http::timeout(10)->get('https://appleid.apple.com/auth/keys');
            if (! $keysResponse->successful()) {
                return false;
            }

            $jwks = $keysResponse->json('keys', []);
            $jwk = collect($jwks)->firstWhere('kid', $kid);
            if (! is_array($jwk)) {
                return false;
            }

            $publicKey = $this->jwkToPem($jwk);
            if ($publicKey === null) {
                return false;
            }

            $signed = $parts[0].'.'.$parts[1];
            $signature = base64_decode(strtr($parts[2], '-_', '+/'), true);
            if ($signature === false) {
                return false;
            }

            return openssl_verify($signed, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
        } catch (\Throwable $e) {
            Log::warning('Apple identity token verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $jwk
     */
    private function jwkToPem(array $jwk): ?string
    {
        if (($jwk['kty'] ?? '') !== 'RSA' || empty($jwk['n']) || empty($jwk['e'])) {
            return null;
        }

        $modulus = $this->base64UrlDecode((string) $jwk['n']);
        $exponent = $this->base64UrlDecode((string) $jwk['e']);
        if ($modulus === null || $exponent === null) {
            return null;
        }

        $modulus = "\x00".$modulus;
        $exponent = "\x00".$exponent;

        $modulus = ltrim($modulus, "\x00");
        $exponent = ltrim($exponent, "\x00");
        if (ord($modulus[0]) > 0x7f) {
            $modulus = "\x00".$modulus;
        }
        if (ord($exponent[0]) > 0x7f) {
            $exponent = "\x00".$exponent;
        }

        $modulusEnc = $this->encodeAsn1Integer($modulus);
        $exponentEnc = $this->encodeAsn1Integer($exponent);
        $rsaPublicKey = $this->encodeAsn1Sequence($modulusEnc.$exponentEnc);

        $rsaOid = hex2bin('300d06092a864886f70d0101010500');
        $bitString = "\x00".$rsaPublicKey;
        $bitStringEnc = "\x03".$this->encodeLength(strlen($bitString)).$bitString;
        $publicKeyInfo = $this->encodeAsn1Sequence($rsaOid.$bitStringEnc);

        return "-----BEGIN PUBLIC KEY-----\n".
            chunk_split(base64_encode($publicKeyInfo), 64, "\n").
            "-----END PUBLIC KEY-----\n";
    }

    private function base64UrlDecode(string $data): ?string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    private function encodeLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), "\x00");
        if ($temp === '') {
            $temp = "\x00";
        }

        return chr(0x80 | strlen($temp)).$temp;
    }

    private function encodeAsn1Integer(string $value): string
    {
        return "\x02".$this->encodeLength(strlen($value)).$value;
    }

    private function encodeAsn1Sequence(string $value): string
    {
        return "\x30".$this->encodeLength(strlen($value)).$value;
    }
}