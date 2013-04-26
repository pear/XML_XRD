<?php
require_once 'XML/XRD.php';

/**
 * @covers XML_XRD_Serializer_XML
 */
class XML_XRD_Serializer_XMLTest extends PHPUnit_Framework_TestCase
{
    public function testXrd10B1()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd/xrd-1.0-b1.xrd');
    }

    public function testXrd10B2()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd/xrd-1.0-b2-nosig.xrd');
    }

    public function testXrdTemplate()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd/link-template.xrd');
    }

    public function testXrdRfc6415A()
    {
        $this->assertXmlIsCorrect(__DIR__ . '/../../../xrd/rfc6415-A.xrd');
    }

    protected function assertXmlIsCorrect($file)
    {
        $xrd = new XML_XRD();
        $xrd->loadFile($file);
        $this->assertXmlStringEqualsXmlFile(
            $file, $xrd->to('xml'),
            'Generated XML does not match the expected XML for ' . $file
        );
    }
}

?>