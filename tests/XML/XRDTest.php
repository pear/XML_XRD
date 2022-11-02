<?php

use PHPUnit\Framework\TestCase;
use XRD\Document;
use XRD\Loader\LoaderException;
use XRD\Element\Link;

/**
 * @covers XML_XRD
 */
class XRDTest extends TestCase
{
    public $xrd;

    public function setUp(): void
    {
        $this->xrd = new Document();
    }

    public function testLoadStringNoLoader()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('No loader for XRD type "batty"');
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

    public function testLoadStringFailEmpty()
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Detecting file type failed');
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
            $this->assertInstanceOf(Link::class, $link);
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
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('http://example.com/lrdd/1', $link->href);
    }

    public function testGetRelationTypeOptional()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $link = $this->xrd->get('picture', 'image/jpeg');
        $this->assertInstanceOf(Link::class, $link);
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
        $this->assertInstanceOf(Link::class, $link);
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
        $this->assertInstanceOf(Link::class, $link);
        $this->assertEquals('http://example.com/cv.html', $link->href);
    }

    public function testGetAllRelation()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $links = $this->xrd->getAll('cv');
        $this->assertIsArray($links);
        $this->assertEquals(3, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf(Link::class, $link);
        }
        $this->assertEquals('http://example.com/cv.txt', $links[0]->href);
        $this->assertEquals('http://example.com/cv.html', $links[1]->href);
        $this->assertEquals('http://example.com/cv.xml', $links[2]->href);
    }

    public function testGetAllRelationTypeOptionalExact()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/html');
        $this->assertIsArray($links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf(Link::class, $link);
        }
        $this->assertEquals('http://example.com/cv.html', $links[0]->href);
    }

    public function testGetAllRelationTypeOptionalNotExact()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/xhtml+xml');
        $this->assertIsArray($links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf(Link::class, $link);
        }
        $this->assertEquals('http://example.com/cv.xml', $links[0]->href);
    }

    public function testGetAllRelationTypeRequired()
    {
        $this->xrd->loadFile(__DIR__ . '/../xrd/multilinks.xrd');
        $links = $this->xrd->getAll('cv', 'text/html', false);
        $this->assertIsArray($links);
        $this->assertEquals(1, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf(Link::class, $link);
        }
        $this->assertEquals('http://example.com/cv.html', $links[0]->href);
    }

    public function testTo()
    {
        $this->xrd->subject = 'foo@example.org';
        $json = $this->xrd->to('json');
        $this->assertIsString($json);
        $this->assertStringContainsString('foo@example.org', $json);
    }

    public function testToXml()
    {
        $this->xrd->subject = 'foo@example.org';
        $xml = $this->xrd->toXML();
        $this->assertIsString($xml);
        $this->assertStringContainsString('<Subject>foo@example.org</Subject>', $xml);
    }

}

?>
