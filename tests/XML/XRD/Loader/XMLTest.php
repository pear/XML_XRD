<?php
require_once 'XML/XRD.php';
require_once 'XML/XRD/Loader/XML.php';

/**
 * @covers XML_XRD_Loader_XML
 */
class XML_XRD_Loader_XMLTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->xrd = new XML_XRD();
        $this->xl = new XML_XRD_Loader_XML($this->xrd);
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage Error loading XML file: failed to load external entity
     */
    public function testLoadFileDoesNotExist()
    {
        $this->xl->loadFile(__DIR__ . '/../doesnotexist');
    }
    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage Error loading XML string: string empty
     */
    public function testLoadStringEmpty()
    {
        $this->xl->loadString('');
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage Error loading XML string: Start tag expected
     */
    public function testLoadStringFailBrokenXml()
    {
        $this->xl->loadString("<?xml");
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage Wrong document namespace
     */
    public function testLoadXmlWrongNamespace()
    {
        $xrdstring = <<<XRD
<?xml version="1.0"?>
<XRD xmlns="http://this/is/wrong">
  <Subject>http://example.com/gpburdell</Subject>
</XRD>
XRD;
        $this->xl->loadString($xrdstring);
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage XML root element is not "XRD"
     */
    public function testLoadXmlWrongRootElement()
    {
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
        $this->assertInternalType('array', $this->xrd->aliases);
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
        $this->assertInternalType('array', $this->xrd->aliases);
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
