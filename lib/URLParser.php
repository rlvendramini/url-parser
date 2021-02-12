<?php

/**
 * A class to parse an URL from its string, modify the parts independently
 * and then turn it to a string again.
 * Works like the URL class from Javascript.
 * 
 * @author Renan Luiz Vendramini <renanlvendramini@gmail.com>
 * @version 1.0
 */
final class URLParser {
  protected $originalUrl;

  private $parsedUrl;

  private $protocol = null;
  private $host = null;
  private $path = null;
  private $query = null;
  private $fragment = null;
  private $queryParams = [];

  const DEFAULT_PROTOCOL = 'https';
  const DEFAULT_PATH = '/';

  function __construct($url) {
    $this->validateUrl($url);
    
    $this->originalUrl = $url;
    $this->destructure();
  }

  // public functions

  public static function fromString($url) {
    return new self($url);
  }

  public function getParam($key) {
    if (!isset($this->queryParams[$key])) return;

    return $this->queryParams[$key];
  }

  public function setParam($key, $value) {
    $this->queryParams[$key] = $value;
    return $this->queryParams[$key];
  }

  public function toString() {
    $this->query = http_build_query($this->queryParams);
    if ($this->query) $this->query = "?{$this->query}";

    if (!$this->protocol) $this->protocol = $this::DEFAULT_PROTOCOL;
    if (!$this->path) $this->path = $this::DEFAULT_PATH;

    return "{$this->protocol}://{$this->host}{$this->path}{$this->query}{$this->fragment}";
  }

  // Private Functions

  private function destructure() {
    $this->parsedUrl = parse_url($this->originalUrl);
    if (!$this->parsedUrl) return;

    foreach(['scheme', 'host', 'path', 'query', 'fragment'] as $key) {
      $this->$key = isset($this->parsedUrl[$key]) ? $this->parsedUrl[$key] : null;
    }

    $this->parseQuery();
  }

  private function parseQuery() {
    if (!$this->query) return;

    foreach(explode("&", $this->query) as $term) {
      $part = explode("=", $term);
      if (!isset($part[0])) continue;

      $this->queryParams[$part[0]] = isset($part[1]) ? $part[1] : '';
    }
  }

  private function validateUrl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) 
      throw new InvalidArgumentException("'{$url}' is not a valid URL");
  }
}
