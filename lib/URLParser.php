<?php

declare(strict_types=1);

/**
 * A class to parse an URL from its string, modify the parts independently
 * and then turn it to a string again.
 * Works like the URL class from Javascript.
 *
 * @author Renan Luiz Vendramini <renanlvendramini@gmail.com>
 * @version 1.0
 */
final class URLParser
{
    private ?string $protocol = null;
    private ?string $host = null;
    private ?string $path = null;
    private ?string $fragment = null;

    /** @var array<string, string> */
    private array $queryParams = [];

    private ?string $cachedUrl = null;

    private const DEFAULT_PROTOCOL = 'https';
    private const DEFAULT_PATH = '/';

    public function __construct(string $url)
    {
        $this->validateUrl($url);
        $this->destructure($url);
    }

    // public functions

    public static function fromString(string $url): self
    {
        return new self($url);
    }

    public function getParam(string $key): ?string
    {
        return $this->queryParams[$key] ?? null;
    }

    public function setParam(string $key, string $value): string
    {
        $this->cachedUrl = null; // Invalida o cache
        $key = $this->sanitizeKeyString($key);
        $sanitizedValue = $this->sanitizeValueString($value);

        $this->queryParams[$key] = $sanitizedValue;

        return $this->queryParams[$key];
    }

    public function toString(): string
    {
        // Retorna cache se disponível
        if ($this->cachedUrl !== null) {
            return $this->cachedUrl;
        }

        $protocol = $this->protocol ?? self::DEFAULT_PROTOCOL;
        $path = $this->path ?? self::DEFAULT_PATH;

        $query = $this->queryParams ? '?' . http_build_query($this->queryParams) : '';
        $fragment = $this->fragment ?? '';

        $this->cachedUrl = "{$protocol}://{$this->host}{$path}{$query}{$fragment}";

        return $this->cachedUrl;
    }

    // Private Functions

    private function destructure(string $url): void
    {
        $parsed = parse_url($url);
        if (!$parsed) {
            return;
        }

        $this->protocol = $parsed['scheme'] ?? null;
        $this->host = $parsed['host'] ?? null;
        $this->path = $parsed['path'] ?? null;
        $this->fragment = isset($parsed['fragment']) ? "#{$parsed['fragment']}" : null;

        // Usa parse_str() nativo do PHP, que é mais eficiente
        if (isset($parsed['query'])) {
            $this->parseQuery($parsed['query']);
        }
    }

    private function parseQuery(string $query): void
    {
        // parse_str é mais eficiente e robusto que explode manual
        parse_str($query, $params);

        foreach ($params as $key => $value) {
            // parse_str pode retornar arrays, ignoramos esses casos
            if (!is_string($key) || is_array($value)) {
                continue;
            }

            $sanitizedKey = $this->sanitizeKeyString($key);
            $sanitizedValue = $this->sanitizeValueString((string) $value);

            $this->queryParams[$sanitizedKey] = $sanitizedValue;
        }
    }

    private function sanitizeKeyString(string $string): string
    {
        // Combina trim, lowercase e substitui em uma operação
        $str = strtolower(trim($string));

        // Combina ambas as regex em uma única operação
        $str = preg_replace(['/[\s\-]/', '/[^a-z0-9_]/'], ['_', ''], $str);

        if ($str === '' || $str === null) {
            throw new \Exception("'{$string}' is an invalid key");
        }

        return $str;
    }

    private function sanitizeValueString(string $string): string
    {
        if ($string === '') {
            return '';
        }

        // urlencode já não adiciona espaços, então trim primeiro é mais eficiente
        return urlencode(trim($string));
    }

    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("'{$url}' is not a valid URL");
        }
    }
}
