<?php
require_once 'XML/XRD.php';

class XML_XRD_Serializer_XMLTest extends PHPUnit_Framework_TestCase
{
    public function testXrd10B1()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd-1.0-b1.xrd');
    }

    public function testXrd10B2()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd-1.0-b2-nosig.xrd');
    }

    protected function assertXmlIsCorrect($file)
    {
        $xrd = new XML_XRD();
        $xrd->loadFile($file);
        $this->assertXmlStringEqualsXmlFile(
            $file, $xrd->toXML(),
            'Generated XML does not match the expected XML for ' . $file
        );
    }
}

?>