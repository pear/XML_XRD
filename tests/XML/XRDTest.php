<?php
require_once 'XML/XRD.php';

class XML_XRDTest extends PHPUnit_Framework_TestCase
{
    public $xrd;

    public function setUp()
    {
        $this->xrd = new XML_XRD();
    }

    public function testLoadString()
    {
        $xrdstring = <<<XRD
<?xml version="1.0"?>
<XRD xmlns="http://docs.oasis-open.org/ns/xri/xrd-1.0">
  <Subject>http://example.com/gpburdell</Subject>
  <Link rel="http://spec.example.net/photo/1.0" type="image/jpeg"
    href="http://photos.example.com/gpburdell.jpg">
  </Link>
</XRD>
XRD;
        $this->assertTrue(
            $this->xrd->loadString($xrdstring)
        );
    }

    public function testLoadStringFailEmpty()
    {
        $this->assertFalse($this->xrd->loadString(""));
    }

    public function testLoadFile()
    {
        $this->assertTrue(
            $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd')
        );
    }

    public function testLoadFileNonExisting()
    {
        $this->assertFalse(
            $this->xrd->loadFile(__DIR__ . '/../doesnotexist')
        );
    }

    public function testDescribesNoAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://example.com/gpburdell')
        );
    }

    public function testDescribesNoAliasFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $this->assertFalse(
            $this->xrd->describes('http://example.com/stevie')
        );
    }

    public function testDescribesAliasSubject()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://example.com/gpburdell')
        );
    }

    public function testDescribesAliasAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://people.example.com/gpburdell')
        );
    }

    public function testDescribesAliasAlias2()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('acct:gpburdell@example.com')
        );
    }

    public function testDescribesAliasFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertFalse(
            $this->xrd->describes('acct:stevie@example.com')
        );
    }

    public function testIterator()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $links = array();
        foreach ($this->xrd as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
            $links[] = $link;
        }
        $this->assertEquals(2, count($links));
        $this->assertEquals('http://services.example.com/auth', $links[0]->href);
        $this->assertEquals('http://photos.example.com/gpburdell.jpg', $links[1]->href);
    }

    public function testGetRelation()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $link = $this->xrd->get('lrdd');
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals('http://example.com/lrdd/1', $link->href);
    }

    public function testGetRelationTypeOptional()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $link = $this->xrd->get('picture', 'image/jpeg');
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals(
            'http://example.com/picture.jpg', $link->href,
            'Image without type is first, but with correct type is more'
            . ' specific and thus has higher priority'
        );
    }

    public function testGetRelationTypeOptionalNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $link = $this->xrd->get('picture', 'image/svg+xml');
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals(
            'http://example.com/picture-notype.jpg', $link->href
        );
    }

    public function testGetRelationTypeRequiredFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $this->assertNull(
            $this->xrd->get('picture', 'image/svg+xml', false)
        );
    }

    public function testGetRelationTypeRequiredOk()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $link = $this->xrd->get('cv', 'text/html', false);
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals('http://example.com/cv.html', $link->href);
    }

    public function testGetAllRelation()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $links = $this->xrd->getAll('cv');
        $this->assertInternalType('array', $links);
        $this->assertEquals(3, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        }
        $this->assertEquals('http://example.com/cv.txt', $links[0]->href);
        $this->assertEquals('http://example.com/cv.html', $links[1]->href);
        $this->assertEquals('http://example.com/cv.xml', $links[2]->href);
    }

    public function testGetAllRelationTypeOptionalExact()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/html');
        $this->assertInternalType('array', $links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        }
        $this->assertEquals('http://example.com/cv.html', $links[0]->href);
    }

    public function testGetAllRelationTypeOptionalNotExact()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/xhtml+xml');
        $this->assertInternalType('array', $links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        }
        $this->assertEquals('http://example.com/cv.xml', $links[0]->href);
    }

    public function testGetAllRelationTypeRequired()
    {
        $this->xrd->loadFile(__DIR__ . '/../multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/html', false);
        $this->assertInternalType('array', $links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        }
        $this->assertEquals('http://example.com/cv.html', $links[0]->href);
    }

    public function testArrayAccess()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $this->assertEquals('Stevie', $this->xrd['name']);
        $this->assertEquals('green', $this->xrd['color']);
        $this->assertNull($this->xrd['empty']);
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testArrayAccessNull()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $this->assertNull($this->xrd['empty']);
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testArrayAccessDoesNotExist()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $this->assertFalse(isset($this->xrd['doesnotexist']));
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testGetPropertiesAll()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $props = array();
        foreach ($this->xrd->getProperties() as $property) {
            $this->assertInstanceOf('XML_XRD_Element_Property', $property);
            $props[] = $property;
        }
        $this->assertEquals(6, count($props));
        
        $this->assertEquals('name', $props[0]->type);
        $this->assertEquals('Stevie', $props[0]->value);
        
        $this->assertEquals('color', $props[2]->type);
        $this->assertEquals('orange', $props[2]->value);
    }

    public function testGetPropertiesType()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $props = array();
        foreach ($this->xrd->getProperties('color') as $property) {
            $this->assertInstanceOf('XML_XRD_Element_Property', $property);
            $props[] = $property;
        }
        $this->assertEquals(2, count($props));
        
        $this->assertEquals('color', $props[0]->type);
        $this->assertEquals('green', $props[0]->value);

        $this->assertEquals('color', $props[1]->type);
        $this->assertEquals('orange', $props[1]->value);
    }

    public function testPropertyExpiresNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $this->assertNull($this->xrd->expires);
    }

    public function testPropertyExpiresTimestampZero()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $this->assertEquals(0, $this->xrd->expires);
    }

    public function testPropertyExpiresTimestamp()
    {
        $this->xrd->loadFile(__DIR__ . '/../expires.xrd');
        $this->assertEquals(123456, $this->xrd->expires);
    }

    public function testPropertySubjectNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../expires.xrd');
        $this->assertNull($this->xrd->subject);
    }

    public function testPropertySubject()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $this->assertEquals('http://example.com/gpburdell', $this->xrd->subject);
    }

    public function testPropertyAliasNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b1.xrd');
        $this->assertEquals(array(), $this->xrd->aliases);
    }

    public function testPropertyAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertInternalType('array', $this->xrd->aliases);
        $this->assertEquals(2, count($this->xrd->aliases));
        $this->assertEquals('http://people.example.com/gpburdell', $this->xrd->aliases[0]);
        $this->assertEquals('acct:gpburdell@example.com', $this->xrd->aliases[1]);
    }

    public function testPropertyIdNone()
    {
        $this->xrd->loadFile(__DIR__ . '/../expires.xrd');
        $this->assertNull($this->xrd->id);
    }

    public function testPropertyId()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd-1.0-b2.xrd');
        $this->assertEquals('foo', $this->xrd->id);
    }
}

?>