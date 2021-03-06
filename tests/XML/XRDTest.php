<?php
require_once 'XML/XRD.php';

/**
 * @covers XML_XRD
 */
class XML_XRDTest extends PHPUnit_Framework_TestCase
{
    public $xrd;

    public function setUp()
    {
        $this->xrd = new XML_XRD();
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage No loader for XRD type "batty"
     */
    public function testLoadStringNoLoader()
    {
        @$this->xrd->loadString('foo', 'batty');
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
        $this->assertNull($this->xrd->loadString($xrdstring));
        $this->assertEquals('http://example.com/gpburdell', $this->xrd->subject);
    }

    /**
     * @expectedException XML_XRD_Loader_Exception
     * @expectedExceptionMessage Detecting file type failed
     */
    public function testLoadStringFailEmpty()
    {
        $this->xrd->loadString("");
    }

    public function testLoadFile()
    {
        $this->assertNull(
            $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b1.xrd')
        );
    }

    public function testDescribesNoAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b1.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://example.com/gpburdell')
        );
    }

    public function testDescribesNoAliasFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b1.xrd');
        $this->assertFalse(
            $this->xrd->describes('http://example.com/stevie')
        );
    }

    public function testDescribesAliasSubject()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://example.com/gpburdell')
        );
    }

    public function testDescribesAliasAlias()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('http://people.example.com/gpburdell')
        );
    }

    public function testDescribesAliasAlias2()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b2.xrd');
        $this->assertTrue(
            $this->xrd->describes('acct:gpburdell@example.com')
        );
    }

    public function testDescribesAliasFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b2.xrd');
        $this->assertFalse(
            $this->xrd->describes('acct:stevie@example.com')
        );
    }

    public function testIterator()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/xrd-1.0-b1.xrd');
        $links = array();
        foreach ($this->xrd as $key => $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
            $links[] = $link;
        }
        $this->assertEquals(2, count($links));
        $this->assertEquals('http://services.example.com/auth', $links[0]->href);
        $this->assertEquals('http://photos.example.com/gpburdell.jpg', $links[1]->href);
    }

    public function testGetRelation()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $link = $this->xrd->get('lrdd');
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals('http://example.com/lrdd/1', $link->href);
    }

    public function testGetRelationTypeOptional()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
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
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $link = $this->xrd->get('picture', 'image/svg+xml');
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals(
            'http://example.com/picture-notype.jpg', $link->href
        );
    }

    public function testGetRelationTypeRequiredFail()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $this->assertNull(
            $this->xrd->get('picture', 'image/svg+xml', false)
        );
    }

    public function testGetRelationTypeRequiredOk()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $link = $this->xrd->get('cv', 'text/html', false);
        $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        $this->assertEquals('http://example.com/cv.html', $link->href);
    }

    public function testGetAllRelation()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
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
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
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
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
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
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/html', false);
        $this->assertInternalType('array', $links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf('XML_XRD_Element_Link', $link);
        }
        $this->assertEquals('http://example.com/cv.html', $links[0]->href);
    }

    public function testTo()
    {
        $this->xrd->subject = 'foo@example.org';
        $json = $this->xrd->to('json');
        $this->assertInternalType('string', $json);
        $this->assertContains('foo@example.org', $json);
    }

    public function testToXml()
    {
        $this->xrd->subject = 'foo@example.org';
        $xml = $this->xrd->toXML();
        $this->assertInternalType('string', $xml);
        $this->assertContains('<Subject>foo@example.org</Subject>', $xml);
    }

}

?>