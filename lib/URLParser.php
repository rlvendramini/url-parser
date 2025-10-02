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
    protected string $originalUrl;

    /** @var array<string, mixed>|false */
    private array|false $parsedUrl;

    private ?string $protocol = null;
    private ?string $host = null;
    private ?string $path = null;
    private ?string $query = null;
    private ?string $fragment = null;

    /** @var array<string, string> */
    private array $queryParams = [];

    private const DEFAULT_PROTOCOL = 'https';
    private const DEFAULT_PATH = '/';

    public function __construct(string $url)
    {
        $this->validateUrl($url);

        $this->originalUrl = $url;
        $this->destructure();
    }

    // public functions

    public static function fromString(string $url): self
    {
        return new self($url);
    }

    public function getParam(string $key): ?string
    {
        if (!isset($this->queryParams[$key])) {
            return null;
        }

        return $this->queryParams[$key];
    }

    public function setParam(string $key, string $value): string
    {
        $key = $this->sanitizeKeyString($key);
        $sanitizedValue = $this->sanitizeValueString($value);

        $this->queryParams[$key] = $sanitizedValue;

        return $this->queryParams[$key];
    }

    public function toString(): string
    {
        $this->query = http_build_query($this->queryParams);
        if ($this->query) {
            $this->query = "?{$this->query}";
        }

        if (!$this->protocol) {
            $this->protocol = self::DEFAULT_PROTOCOL;
        }
        if (!$this->path) {
            $this->path = self::DEFAULT_PATH;
        }

        return "{$this->protocol}://{$this->host}{$this->path}{$this->query}{$this->fragment}";
    }

    // Private Functions

    private function destructure(): void
    {
        $this->parsedUrl = parse_url($this->originalUrl);
        if (!$this->parsedUrl) {
            return;
        }

        $propertyMap = [
            'scheme' => 'protocol',
            'host' => 'host',
            'path' => 'path',
            'query' => 'query',
            'fragment' => 'fragment',
        ];

        foreach ($propertyMap as $urlKey => $propertyName) {
            $this->$propertyName = $this->parsedUrl[$urlKey] ?? null;
        }

        $this->parseQuery();
    }

    private function parseQuery(): void
    {
        if (!$this->query) {
            return;
        }

        foreach (explode("&", $this->query) as $term) {
            $part = explode("=", $term);

            $sanitizedKey = $this->sanitizeKeyString($part[0]);
            $sanitizedValue = $this->sanitizeValueString($part[1] ?? '');

            $this->queryParams[$sanitizedKey] = $sanitizedValue;
        }
    }

    private function sanitizeKeyString(string $string): string
    {
        // turn into low caps
        $str = trim(strtolower($string));
        // turn \s and - into _
        $str = preg_replace('/\s|\-/', '_', $str);
        if (!$str) {
            throw new \Exception("'{$str}' is an invalid key");
        }
        // remove any character except alphanumerical and underscore
        $str = preg_replace('/[^a-zA-Z0-9_]/', '', $str);
        if (!$str) {
            throw new \Exception("'{$string}' is an invalid key");
        }

        return $str;
    }

    private function sanitizeValueString(string $string): string
    {
        if (!$string) {
            return '';
        }

        $str = trim(urlencode($string));

        return $str;
    }

    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("'{$url}' is not a valid URL");
        }
    }
}
