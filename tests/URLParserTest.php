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
    $instance = URLParser::fromString('https://foo.bar/?foo=bar');

    $this->assertEquals(
      'https://foo.bar/?foo=bar',
      $instance->toString()
    );
  }

  public function testCanGetQueryStringValue() {
    $instance = URLParser::fromString('https://foo.bar/?foo=bar');

    $this->assertEquals(
      'bar',
      $instance->getParam('foo')
    );
  }

  public function testCanModifyQueryStringValue() {
    $instance = URLParser::fromString('https://foo.bar/?foo=bar');
    $instance->setParam('foo', 'foobar');

    $this->assertNotEquals(
      'bar',
      $instance->getParam('foo')
    );

    $this->assertEquals(
      'foobar',
      $instance->getParam('foo')
    );
  }

  public function testCanSetNewQueryStringValue() {
    $instance = URLParser::fromString('https://foo.bar/?foo=bar');
    $instance->setParam('bar', 'foo');

    $this->assertEquals(
      'foo',
      $instance->getParam('bar')
    );
  }
}