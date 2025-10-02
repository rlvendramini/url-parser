<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../lib/URLParser.php';


class PHPUnitUtil
{
    /**
     * @param object $obj
     * @param string $name
     * @param array<mixed> $args
     * @return mixed
     */
    public static function callMethod(object $obj, string $name, array $args): mixed
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}

final class URLParserTest extends TestCase
{
    public function testCanBeCreatedFromValidUrl(): void
    {
        $this->assertInstanceOf(
            URLParser::class,
            URLParser::fromString('https://foo.bar')
        );
    }

    public function testCannotBeCreatedFromInvalidUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);

        URLParser::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');

        $this->assertEquals(
            'https://foo.bar/?foo=bar',
            $instance->toString()
        );
    }

    public function testCanGetQueryStringValue(): void
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');

        $this->assertEquals(
            'bar',
            $instance->getParam('foo')
        );
    }

    public function testCanModifyQueryStringValue(): void
    {
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

    public function testCanSetNewQueryStringValue(): void
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');
        $instance->setParam('bar', 'foo');

        $this->assertEquals(
            'foo',
            $instance->getParam('bar')
        );
    }


    public function testCanSetParamWithDirtyKeyAndValue(): void
    {
        $instance = URLParser::fromString('https://foo.bar/');
        $instance->setParam(' #Mega Foo~ ', 'foo bar');

        $this->assertNull(
            $instance->getParam(' #Mega Foo~ ')
        );

        $this->assertEquals(
            'foo+bar',
            $instance->getParam('mega_foo')
        );

        $this->assertNotEquals(
            'foo bar',
            $instance->getParam('mega_foo')
        );
    }

    public function testCanSanitizeKeyString(): void
    {
        $instance = URLParser::fromString('https://foo.bar');

        $returnVal = PHPUnitUtil::callMethod(
            $instance,
            'sanitizeKeyString',
            [" #Mega Foo-bar~ "]
        );

        $this->assertEquals(
            'mega_foo_bar',
            $returnVal
        );
    }


    public function testCanSanitizeValueString(): void
    {
        $instance = URLParser::fromString('https://foo.bar');

        $returnVal = PHPUnitUtil::callMethod(
            $instance,
            'sanitizeValueString',
            ["Foo Bar"]
        );

        $this->assertEquals(
            'Foo+Bar',
            $returnVal
        );
    }
}
