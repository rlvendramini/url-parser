<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require __DIR__ . "/../lib/URLParser.php";

final class URLParserTest extends TestCase
{
    public function testCanBeCreatedFromValidUrl(): void
    {
        $this->assertInstanceOf(
            URLParser::class,
            URLParser::fromString("https://foo.bar"),
        );
    }

    public function testCannotBeCreatedFromInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        URLParser::fromString("invalid");
    }

    public function testCanBeUsedAsString(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");

        $this->assertEquals("https://foo.bar/?foo=bar", $instance->toString());

        $this->assertEquals("https://foo.bar/?foo=bar", (string) $instance);
    }

    public function testCanGetQueryStringValue(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");

        $this->assertEquals("bar", $instance->getParam("foo"));
    }

    public function testCanModifyQueryStringValue(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");
        $instance->setParam("foo", "foobar");

        $this->assertNotEquals("bar", $instance->getParam("foo"));

        $this->assertEquals("foobar", $instance->getParam("foo"));
    }

    public function testCanSetNewQueryStringValue(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");
        $instance->setParam("bar", "foo");

        $this->assertEquals("foo", $instance->getParam("bar"));
    }

    public function testCanSetParamWithDirtyKeyAndValue(): void
    {
        $instance = URLParser::fromString("https://foo.bar/");
        $instance->setParam(" #Mega Foo~ ", "foo bar");

        $this->assertEquals("foo+bar", $instance->getParam(" #Mega Foo~ "));

        $this->assertEquals("foo+bar", $instance->getParam("mega_foo"));

        $this->assertNotEquals("foo bar", $instance->getParam("mega_foo"));
    }

    public function testCanSanitizeKeyString(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $ref = new \ReflectionClass($instance);
        $method = $ref->getMethod("sanitizeKeyString");

        $result = $method->invoke($instance, " #Mega Foo-bar~ ");

        $this->assertEquals("mega_foo_bar", $result);
    }

    public function testCanSanitizeValueString(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $ref = new \ReflectionClass($instance);
        $method = $ref->getMethod("sanitizeValueString");

        $result = $method->invoke($instance, "Foo Bar");

        $this->assertEquals("Foo+Bar", $result);
    }

    public function testCanRemoveParam(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar&baz=qux");

        $this->assertEquals("bar", $instance->getParam("foo"));
        $this->assertEquals("qux", $instance->getParam("baz"));

        $instance->removeParam("foo");

        $this->assertNull($instance->getParam("foo"));
        $this->assertEquals("qux", $instance->getParam("baz"));
    }

    public function testCanCheckIfParamExists(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");

        $this->assertTrue($instance->hasParam("foo"));
        $this->assertFalse($instance->hasParam("baz"));
    }

    public function testCanGetUrlParts(): void
    {
        $instance = URLParser::fromString(
            "https://example.com/path/to/resource?foo=bar#section",
        );

        $this->assertEquals("https", $instance->getProtocol());
        $this->assertEquals("example.com", $instance->getHost());
        $this->assertEquals("/path/to/resource", $instance->getPath());
        $this->assertEquals("#section", $instance->getFragment());
    }

    public function testCacheIsInvalidatedOnSet(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");

        $first = $instance->toString();
        $instance->setParam("foo", "changed");

        $this->assertNotEquals($first, $instance->toString());
        $this->assertStringContainsString("foo=changed", $instance->toString());
    }

    public function testCacheIsInvalidatedOnRemove(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?foo=bar");

        $first = $instance->toString();
        $instance->removeParam("foo");

        $this->assertNotEquals($first, $instance->toString());
        $this->assertStringNotContainsString("?", $instance->toString());
    }

    public function testSanitizeKeyBlowsUpOnInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $instance = URLParser::fromString("https://foo.bar");
        $instance->setParam("!!!", "value");
    }

    public function testProtocolDefaultsToHttps(): void
    {
        $instance = URLParser::fromString("http://foo.bar");

        $this->assertEquals("http", $instance->getProtocol());
    }

    public function testPathDefaultsToSlash(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $this->assertEquals("/", $instance->getPath());
    }

    public function testFragmentIsNullWhenAbsent(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $this->assertNull($instance->getFragment());
    }

    public function testSetParamReturnsSanitizedValue(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $result = $instance->setParam("key", "hello world");

        $this->assertEquals("hello+world", $result);
    }

    public function testToStringWithNoQueryAndNoFragment(): void
    {
        $instance = URLParser::fromString("https://foo.bar");

        $this->assertEquals("https://foo.bar/", $instance->toString());
    }

    public function testRemoveParamIsChainable(): void
    {
        $instance = URLParser::fromString("https://foo.bar/?a=1&b=2");

        $result = $instance->removeParam("a")->removeParam("b");

        $this->assertSame($instance, $result);
        $this->assertNull($instance->getParam("a"));
        $this->assertNull($instance->getParam("b"));
    }

    public function testEmptyStringParam(): void
    {
        $instance = URLParser::fromString("https://foo.bar/");
        $instance->setParam("empty", "");

        $this->assertEquals("", $instance->getParam("empty"));
        $this->assertStringContainsString("empty=", $instance->toString());
    }
}
