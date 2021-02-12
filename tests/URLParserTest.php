<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../lib/URLParser.php';

final class URLParserTest extends TestCase {
  public function testCanBeCreatedFromValidUrl() {
    $this->assertInstanceOf(
      URLParser::class,
      URLParser::fromString('https://foo.bar')
    );
  }

  public function testCannotBeCreatedFromInvalidUrl() {
    $this->expectException(InvalidArgumentException::class);

    URLParser::fromString('invalid');
  }

  public function testCanBeUsedAsString() {
    $this->assertEquals(
      'https://foo.bar/?foo=bar',
      URLParser::fromString('https://foo.bar/?foo=bar')->toString()
    );
  }

  public function testCanGetQueryStringValue() {
    $this->assertEquals(
      'bar',
      URLParser::fromString('https://foo.bar/?foo=bar')->getParam('foo')
    );
  }
}