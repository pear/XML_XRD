<?php

use PHPUnit\Framework\TestCase;
use XRD\Element\Property;

class PropertyTest extends TestCase
{
    public function test__constructParams()
    {
        $prop = new Property(
            'http://spec.example.net/created/1.0', '1970-01-01'
        );
        $this->assertEquals('http://spec.example.net/created/1.0', $prop->type);
        $this->assertEquals('1970-01-01', $prop->value);
    }

    public function test__constructParamsNullValue()
    {
        $prop = new Property(
            'http://spec.example.net/created/1.0'
        );
        $this->assertEquals('http://spec.example.net/created/1.0', $prop->type);
        $this->assertNull($prop->value);
    }

}
?>
