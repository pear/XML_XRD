<?php

namespace Loader;

use PHPUnit\Framework\TestCase;
use XRD\Document;
use XRD\Loader\XML;
use XRD\Loader\LoaderException;

/**
 * @covers XML
 */
class XMLTest extends TestCase
{
    public function setUp(): void
    {
        $this->xrd = new Document();
        $this->xl = new XML($this->xrd);
    }

    public function testLoadFileDoesNotExist()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Error loading XML file: failed to load external entity');
        $this->xl->loadFile(__DIR__ . '/../doesnotexist');
    }

    public function testLoadStringEmpty()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Error loading XML string: string empty');
        $this->xl->loadString('');
    }

    public function testLoadStringFailBrokenXml()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Error loading XML string: Start tag expected');
        $this->xl->loadString("<?xml");
    }

    public function testLoadXmlWrongNamespace()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Wrong document namespace');
        $xrdstring = <<<XRD
<?xml version="1.0"?>
<XRD xmlns="http://this/is/wrong">
  <Subject>http://example.com/gpburdell</Subject>
</XRD>
XRD;
        $this->xl->loadString($xrdstring);
    }

    public function testLoadXmlWrongRootElement()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('XML root element is not "XRD"');
        $xrdstring = <<<XRD
<?xml version="1.0"?>
<FOO xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
  <Subject>http://example.com/gpburdell</Subject>
</FOO>
XRD;
        $this->xl->loadString($xrdstring);
    }

    public function testPropertyExpiresNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/properties.xrd');
        $this->assertNull($this->xrd->expires);
    }

    public function testPropertyExpiresTimestampZero()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b1.xrd');
        $this->assertEquals(0, $this->xrd->expires);
    }

    public function testPropertyExpiresTimestamp()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/expires.xrd');
        $this->assertEquals(123456, $this->xrd->expires);
    }

    public function testPropertySubjectNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/expires.xrd');
        $this->assertNull($this->xrd->subject);
    }

    public function testPropertySubject()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b1.xrd');
        $this->assertEquals('http://example.com/gpburdell', $this->xrd->subject);
    }

    public function testPropertyAliasNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b1.xrd');
        $this->assertEquals(array(), $this->xrd->aliases);
    }

    public function testPropertyAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b2.xrd');
        $this->assertIsArray($this->xrd->aliases);
        $this->assertEquals(2, count($this->xrd->aliases));
        $this->assertEquals('http://people.example.com/gpburdell', $this->xrd->aliases[0]);
        $this->assertEquals('acct:gpburdell@example.com', $this->xrd->aliases[1]);
    }

    public function testPropertyIdNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/expires.xrd');
        $this->assertNull($this->xrd->id);
    }

    public function testPropertyId()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b2.xrd');
        $this->assertEquals('foo', $this->xrd->id);
    }

    public function testLoadAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../xrd/xrd-1.0-b2.xrd');
        $this->assertIsArray($this->xrd->aliases);
        $this->assertEquals(2, count($this->xrd->aliases));
        $this->assertEquals('http://people.example.com/gpburdell', $this->xrd->aliases[0]);
        $this->assertEquals('acct:gpburdell@example.com', $this->xrd->aliases[1]);
    }

    public function testLoadProperties()
    {
        $this->xl->loadFile(__DIR__ . '/../../../xrd/properties.xrd');
        $this->assertEquals('Stevie', $this->xrd['name']);
        $this->assertEquals('green', $this->xrd['color']);
    }
}

?>
