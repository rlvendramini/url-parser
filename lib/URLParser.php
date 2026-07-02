<?php

declare(strict_types=1);

/**
 * A class to parse an URL from its string, modify the parts independently
 * and then turn it to a string again.
 * Works like the URL class from Javascript.
 *
 * @author Renan Luiz Vendramini <renanlvendramini@gmail.com>
 */
final class URLParser
{
    public readonly string $protocol;
    public readonly string $host;
    public readonly string $path;

    private ?string $fragment = null;

    /** @var array<string, string> */
    private array $queryParams = [];

    private ?string $cachedUrl = null;

    private const string DEFAULT_PROTOCOL = "https";
    private const string DEFAULT_PATH = "/";

    public function __construct(string $url)
    {
        $this->validateUrl($url);

        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new \InvalidArgumentException(
                "Failed to parse URL: '{$url}'",
            );
        }

        $this->protocol = $parsed["scheme"] ?? self::DEFAULT_PROTOCOL;
        $this->host =
            $parsed["host"] ??
            throw new \InvalidArgumentException(
                "URL '{$url}' is missing a host",
            );
        $this->path = $parsed["path"] ?? self::DEFAULT_PATH;
        $this->fragment = isset($parsed["fragment"])
            ? "#{$parsed["fragment"]}"
            : null;

        if (isset($parsed["query"])) {
            $this->parseQuery($parsed["query"]);
        }
    }

    // public API

    public static function fromString(string $url): self
    {
        return new self($url);
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    public function getParam(string $key): ?string
    {
        return $this->queryParams[$this->sanitizeKeyString($key)] ?? null;
    }

    public function setParam(string $key, string $value): string
    {
        $key = $this->sanitizeKeyString($key);
        $sanitizedValue = $this->sanitizeValueString($value);

        $this->queryParams[$key] = $sanitizedValue;
        $this->cachedUrl = null;

        return $sanitizedValue;
    }

    public function removeParam(string $key): self
    {
        $key = $this->sanitizeKeyString($key);
        unset($this->queryParams[$key]);
        $this->cachedUrl = null;

        return $this;
    }

    public function hasParam(string $key): bool
    {
        return array_key_exists(
            $this->sanitizeKeyString($key),
            $this->queryParams,
        );
    }

    public function toString(): string
    {
        if ($this->cachedUrl !== null) {
            return $this->cachedUrl;
        }

        $query =
            $this->queryParams !== []
                ? "?" . http_build_query($this->queryParams)
                : "";
        $fragment = $this->fragment ?? "";

        $this->cachedUrl = "{$this->protocol}://{$this->host}{$this->path}{$query}{$fragment}";

        return $this->cachedUrl;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    // Private helpers

    private function parseQuery(string $query): void
    {
        parse_str($query, $params);

        foreach ($params as $key => $value) {
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
        $str = strtolower(trim($string));

        // Substitui espaços e hífens por underscore, remove caracteres inválidos
        $str = (string) preg_replace(
            ["/[\s\-]/", "/[^a-z0-9_]/"],
            ["_", ""],
            $str,
        );

        if ($str === "") {
            throw new \InvalidArgumentException(
                "'{$string}' is an invalid key: sanitized result is empty",
            );
        }

        return $str;
    }

    private function sanitizeValueString(string $string): string
    {
        if ($string === "") {
            return "";
        }

        return urlencode(trim($string));
    }

    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("'{$url}' is not a valid URL");
        }
    }
}
