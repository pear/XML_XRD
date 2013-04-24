<?php
require_once 'XML/XRD.php';
require_once 'XML/XRD/Loader/XML.php';

class XML_XRD_Loader_XMLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException XML_XRD_LoadFileException
     * @expectedExceptionMessage Error loading XML file: failed to load external entity
     */
    public function testLoadFileDoesNotExist()
    {
        $this->jl = new XML_XRD_Loader_XML(new XML_XRD());
        $this->jl->loadFile(__DIR__ . '/../doesnotexist');
    }
    /**
     * @expectedException XML_XRD_LoadFileException
     * @expectedExceptionMessage Error loading XML string: string empty
     */
    public function testLoadStringEmpty()
    {
        $this->jl = new XML_XRD_Loader_XML(new XML_XRD());
        $this->jl->loadString('');
    }
}

?>
