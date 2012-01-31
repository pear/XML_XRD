<?php
require_once 'XML/XRD/Element/Link.php';
require_once 'XML/XRD/Element/Property.php';

class XML_XRD implements ArrayAccess
{
    /**
     * Loads the contents of the given file
     *
     * @param string $file Path to an XRD file
     *
     * @return boolean True when all went well
     */
    public function loadFile($file)
    {
        return $this->load(simplexml_load_file($file));
    }

    /**
     * Loads the contents of the given string
     *
     * @param string $xml XML string
     *
     * @return boolean True when all went well
     */
    public function loadString($xml)
    {
        return $this->load(simplexml_load_string($xml));
    }

    /**
     * Loads the XML element into the classes' data structures
     *
     * @param object $x XML element containing the whole XRD document
     *
     * @return boolean True when all went well
     */
    protected function load(SimpleXMLElement $x)
    {
    }

    public function describes()
    {
        //FIXME
    }

    public function get()
    {
        //FIXME
    }

    public function getAll()
    {
        //FIXME
    }

    public function offsetExists($key)
    {
        //FIXME
    }
    public function offsetGet($key)
    {
        //FIXME
    }
    public function offsetSet($key, $value)
    {
        //FIXME
    }
    public function offsetUnset($key)
    {
        //FIXME
    }

    public function getProperties()
    {
        //FIXME
    }
}

?>