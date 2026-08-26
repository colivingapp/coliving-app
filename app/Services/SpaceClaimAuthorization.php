<?php

namespace App\Services;

use App\Models\Space;
use App\Models\User;

class SpaceClaimAuthorization
{
    public const BLOCKED_EMAILS = [
        'spaces@coliving.app',
    ];

    public const PUBLIC_EMAIL_DOMAINS = [
        'gmail.com',
        'googlemail.com',
        'hotmail.com',
        'icloud.com',
        'live.com',
        'outlook.com',
        'proton.me',
        'protonmail.com',
        'yahoo.com',
        'aol.com',
        'me.com',
        'msn.com',
        'ymail.com',
    ];

    public static function canAutoApprove(User $user, Space $space): bool
    {
        if ((int) $space->claimed === 1) {
            return false;
        }

        if (!$user->hasVerifiedEmail()) {
            return false;
        }

        $userEmail = self::normalizedEmail($user->email);
        $spaceEmail = self::normalizedEmail($space->email);

        if ($userEmail === '' || in_array($spaceEmail, self::BLOCKED_EMAILS, true)) {
            return false;
        }

        if ($spaceEmail !== '' && hash_equals($spaceEmail, $userEmail)) {
            return true;
        }

        $userDomain = self::registrableDomainFromEmail($userEmail);
        if (!$userDomain || in_array($userDomain, self::PUBLIC_EMAIL_DOMAINS, true) || $userDomain === 'coliving.app') {
            return false;
        }

        $spaceEmailDomain = self::registrableDomainFromEmail($spaceEmail);
        if ($spaceEmailDomain && $userDomain === $spaceEmailDomain) {
            return true;
        }

        $spaceWebsiteDomain = self::registrableDomainFromUrl($space->website ?? null);
        if ($spaceWebsiteDomain && $userDomain === $spaceWebsiteDomain) {
            return true;
        }

        return false;
    }

    public static function normalizedEmail(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    public static function registrableDomainFromEmail(?string $email): ?string
    {
        $email = self::normalizedEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return self::registrableDomain(substr((string) strrchr($email, '@'), 1));
    }

    public static function registrableDomainFromUrl(?string $url): ?string
    {
        $url = strtolower(trim((string) $url));
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#', $url)) {
            $url = 'https://'.$url;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return self::registrableDomain(is_string($host) ? $host : null);
    }

    public static function registrableDomain(?string $host): ?string
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if ($host === '' || !preg_match('/^[a-z0-9.-]+$/', $host)) {
            return null;
        }

        $parts = explode('.', $host);
        if (count($parts) < 2) {
            return null;
        }

        $multiPartTlds = [
            'ac.uk',
            'co.jp',
            'co.nz',
            'co.uk',
            'com.au',
            'com.br',
            'net.au',
            'org.uk',
        ];

        $lastTwo = implode('.', array_slice($parts, -2));
        if (in_array($lastTwo, $multiPartTlds, true)) {
            if (count($parts) < 3) {
                return null;
            }

            return implode('.', array_slice($parts, -3));
        }

        return $lastTwo;
    }
}
