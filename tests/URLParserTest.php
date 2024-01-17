<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../lib/URLParser.php';


class PHPUnitUtil
{
    public static function callMethod($obj, $name, $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}

final class URLParserTest extends TestCase
{
    public function testCanBeCreatedFromValidUrl()
    {
        $this->assertInstanceOf(
            URLParser::class,
            URLParser::fromString('https://foo.bar')
        );
    }

    public function testCannotBeCreatedFromInvalidUrl()
    {
        $this->expectException(InvalidArgumentException::class);

        URLParser::fromString('invalid');
    }

    public function testCanBeUsedAsString()
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');

        $this->assertEquals(
            'https://foo.bar/?foo=bar',
            $instance->toString()
        );
    }

    public function testCanGetQueryStringValue()
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');

        $this->assertEquals(
            'bar',
            $instance->getParam('foo')
        );
    }

    public function testCanModifyQueryStringValue()
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

    public function testCanSetNewQueryStringValue()
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');
        $instance->setParam('bar', 'foo');

        $this->assertEquals(
            'foo',
            $instance->getParam('bar')
        );
    }


    public function testCanSetParamWithDirtyKeyAndValue()
    {
        $instance = URLParser::fromString('https://foo.bar/');
        $instance->setParam(' #Mega Foo~ ', 'foo bar');

        $this->AssertNull(
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

    public function testCanSanitizeKeyString()
    {
        $instance = URLParser::fromString('https://foo.bar');

        $returnVal = PHPUnitUtil::callMethod(
            $instance,
            'sanitizeKeyString',
            array(" #Mega Foo-bar~ ")
        );

        $this->assertEquals(
            'mega_foo_bar',
            $returnVal
        );
    }


    public function testCanSanitizeValueString()
    {
        $instance = URLParser::fromString('https://foo.bar');

        $returnVal = PHPUnitUtil::callMethod(
            $instance,
            'sanitizeValueString',
            array("Foo Bar")
        );

        $this->assertEquals(
            'Foo+Bar',
            $returnVal
        );
    }

    public function testCanRemoveParam()
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar');
        $instance->removeParam('foo');

        $this->assertNull(
            $instance->getParam('foo')
        );
    }

    public function testCanGetParams()
    {
        $instance = URLParser::fromString('https://foo.bar/?foo=bar&bar=foo');

        $this->assertEquals(
            array(
            'foo' => 'bar',
            'bar' => 'foo'
      ),
            $instance->getParams()
        );
    }
}
