<?php
require_once 'XML/XRD.php';

class XML_XRD_Element_LinkTest extends PHPUnit_Framework_TestCase
{
    public $xrd;
    public $link;

    public function setUp()
    {
        $this->xrd = new XML_XRD();
        $this->xrd->loadFile(__DIR__ . '/../../../xrd-1.0-b1.xrd');
        $this->link = $this->xrd->get('http://spec.example.net/photo/1.0');
        $this->assertInstanceOf('XML_XRD_Element_Link', $this->link);
    }

    public function testPropertyRel()
    {
        $this->assertEquals('http://spec.example.net/photo/1.0', $this->link->rel);
    }

    public function testPropertyType()
    {
        $this->assertEquals('image/jpeg', $this->link->type);
    }

    public function testPropertyHref()
    {
        $this->assertEquals('http://photos.example.com/gpburdell.jpg', $this->link->href);
    }

    public function testPropertyTemplate()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../link-template.xrd');
        $this->link = $this->xrd->get('title');
        $this->assertEquals('http://photos.example.com/{uri}.jpg', $this->link->template);
    }

    public function testPropertyTemplateNone()
    {
        $this->assertNull($this->link->template);
    }

    public function testPropertyTitles()
    {
        $this->assertEquals(
            array('en' => 'User Photo', 'de' => 'Benutzerfoto'),
            $this->link->titles
        );
    }

    public function testGetTitleNoParam()
    {
        $this->assertEquals('User Photo', $this->link->getTitle(), 'First title returned');
    }

    public function testGetTitle()
    {
        $this->assertEquals('Benutzerfoto', $this->link->getTitle('de'));
    }

    public function testGetTitleLangNotFound()
    {
        $this->assertEquals(
            'User Photo', $this->link->getTitle('fr'),
            'First language returned when not found and none without language'
        );
    }

    public function testGetTitleLangNotFoundFallbackNoLang()
    {
        $this->xrd->loadFile(__DIR__ . '/../../../link-title.xrd');
        $link = $this->xrd->get('name');
        $this->assertEquals(
            'Stevie', $link->getTitle('fr'),
            'First title without language when not found'
        );
    }


    public function testArrayAccess()
    {
        $this->xrd->loadFile(__DIR__ . '/../properties.xrd');
        $link = $this->xrd->get('link');
        $this->assertEquals('Stevie', $link['name']);
        $this->assertEquals('green', $link['color']);
        $this->assertNull($link['empty']);
        $this->assertNull($link['doesnotexist']);
    }

}
?>