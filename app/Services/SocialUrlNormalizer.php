<?php

namespace App\Services;

class SocialUrlNormalizer
{
    public const MATE_FIELDS = [
        'twitter', 'facebook', 'instagram', 'youtube', 'tiktok',
        'pinterest', 'linkedin', 'github', 'substack', 'medium',
    ];

    public const SPACE_FIELDS = [
        'twitter', 'facebook', 'instagram', 'youtube', 'tiktok',
        'pinterest', 'linkedin', 'linktree', 'telegram', 'github',
        'substack', 'medium',
    ];

    private const BASES = [
        'twitter' => 'https://x.com/',
        'facebook' => 'https://www.facebook.com/',
        'instagram' => 'https://www.instagram.com/',
        'youtube' => 'https://www.youtube.com/@',
        'tiktok' => 'https://www.tiktok.com/@',
        'pinterest' => 'https://www.pinterest.com/',
        'linkedin' => 'https://www.linkedin.com/in/',
        'github' => 'https://github.com/',
        'substack' => 'https://substack.com/@',
        'medium' => 'https://medium.com/@',
        'telegram' => 'https://t.me/',
        'linktree' => 'https://linktr.ee/',
    ];

    public static function normalize(string $network, ?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value)) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            if ($host === '' || str_contains($host, '.')) {
                return ChatAuthorization::safeHttpUrl($value);
            }
            $value = $host;
        } elseif (str_contains($value, '/') || str_starts_with(strtolower($value), 'www.')) {
            return ChatAuthorization::safeHttpUrl($value);
        }

        $handle = ltrim($value, '@');
        $base = self::BASES[strtolower($network)] ?? '';
        if ($base === '' || $handle === '') {
            return ChatAuthorization::safeHttpUrl($value);
        }

        return ChatAuthorization::safeHttpUrl($base.$handle);
    }

    public static function fromRequest($request, array $fields): array
    {
        $normalized = [];
        foreach ($fields as $field) {
            $normalized[$field] = self::normalize($field, $request->input($field));
        }

        return $normalized;
    }
}
