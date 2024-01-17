<?php

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
    protected readonly string $originalUrl;

    private readonly array $parsedUrl;

    private ?string $protocol;
    private ?string $host;
    private ?string $path;
    private ?string $query;
    private ?string $fragment;
    private array $queryParams;

    public const DEFAULT_PROTOCOL = 'https';
    public const DEFAULT_PATH = '/';
    public const PARTS = ['scheme', 'host', 'path', 'query', 'fragment'];

    public function __construct(private readonly string $url)
    {
        $this->validateUrl($url);

        $this->originalUrl = $url;
        $this->protocol = $this::DEFAULT_PROTOCOL;
        $this->path = $this::DEFAULT_PATH;

        $this->destructure();
    }

    // public functions

    // static functions

    public static function fromString(string $url)
    {
        return new self($url);
    }

    // instance functions

    public function getParam(string $key): string | null
    {
        if (!isset($this->queryParams[$key])) {
            return null;
        }

        return $this->queryParams[$key];
    }

    public function setParam(string $key, string $value): string
    {
        $key = $this->sanitizeKeyString($key);
        $value = $this->sanitizeValueString($value);

        $this->queryParams[$key] = $value;
        return $this->queryParams[$key];
    }

    public function removeParam(string $key): void
    {
        unset($this->queryParams[$key]);
    }

    public function getParams(): array
    {
        return $this->queryParams;
    }

    public function toString(): string
    {
        $this->query = http_build_query($this->queryParams);
        if ($this->query) {
            $this->query = "?{$this->query}";
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

        foreach($this::PARTS as $key) {
            $this->$key = $this->parsedUrl[$key] ?? null;
        }

        $this->parseQuery();
    }

    private function parseQuery(): void
    {
        if (!$this->query) {
            return;
        }

        foreach(explode("&", $this->query) as $term) {
            [$key, $value] = explode("=", $term);
            if (!isset($key)) {
                continue;
            }

            $sanitizedKey = $this->sanitizeKeyString($key);
            $sanitizedValue = $this->sanitizeValueString($value ?? '');

            $this->queryParams[$sanitizedKey] = $sanitizedValue;
        }
    }

    private function sanitizeKeyString(string $string): string
    {
        // remove leading and trailing spaces
        $str = trim($string);
        // turn into low caps
        $str = strtolower($str);
        // turn \s and - into _
        $str = preg_replace('/\s|\-/', '_', $str);
        if (!$str) {
            throw new Exception("'{$str}' is an invalid key");
        }

        // remove any character except alphanumerical and underscore
        $str = preg_replace('/[^a-zA-Z0-9_]/', '', $str);
        if (!$str) {
            throw new Exception("'{$string}' is an invalid key");
        }

        return $str;
    }

    private function sanitizeValueString(string $string): string
    {
        $str = trim(urlencode($string));
        return $str;
    }

    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("'{$url}' is not a valid URL");
        }
    }
}
