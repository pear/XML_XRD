<?php
require_once 'XML/XRD.php';

use PHPUnit\Framework\TestCase;

/**
 * @covers XML_XRD_PropertyAccess
 */
class XML_XRD_PropertyAccessTest extends TestCase
{
    public $xrd;

    public function setUp(): void
    {
        $this->xrd = new XML_XRD();
    }
    public function testArrayAccess()
    {
        $this->xrd->loadFile(__DIR__ . '/../../xrd/properties.xrd');
        $this->assertTrue(isset($this->xrd['name']));
        $this->assertEquals('Stevie', $this->xrd['name']);
        $this->assertEquals('green', $this->xrd['color']);
        $this->assertNull($this->xrd['empty']);
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testArrayAccessNull()
    {
        $this->xrd->loadFile(__DIR__ . '/../../xrd/properties.xrd');
        $this->assertNull($this->xrd['empty']);
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testArrayAccessDoesNotExist()
    {
        $this->xrd->loadFile(__DIR__ . '/../../xrd/properties.xrd');
        $this->assertFalse(isset($this->xrd['doesnotexist']));
        $this->assertNull($this->xrd['doesnotexist']);
    }

    public function testArrayAccessSet()
    {
        $this->expectException(XML_XRD_LogicException::class);
        $this->xrd['foo'] = 'bar';
    }

    public function testArrayAccessUnset()
    {
        $this->expectException(XML_XRD_LogicException::class);
        unset($this->xrd['foo']);
    }

    public function testGetPropertiesAll()
    {
        $this->xrd->loadFile(__DIR__ . '/../../xrd/properties.xrd');
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
        $this->xrd->loadFile(__DIR__ . '/../../xrd/properties.xrd');
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

}

?>
