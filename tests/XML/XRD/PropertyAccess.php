<?php
require_once 'XML/XRD/Element/Property.php';

class XML_XRD_PropertyAccess implements ArrayAccess
{

    /**
     * Array of property objects
     *
     * @var array
     */
    public $properties = array();


    /**
     * Loads the Property elements from XML
     *
     * @param object $x XML element
     *
     * @return boolean True when all went well
     */
    protected function loadProperties(SimpleXMLElement $x)
    {
        foreach ($x->Property as $xProp) {
            $this->properties[] = new XML_XRD_Element_Property($xProp);
        }
    }

    /**
     * Check if the property with the given type exists
     *
     * Part of the ArrayAccess interface
     *
     * @return boolean True if it exists
     */
    public function offsetExists($type)
    {
        foreach ($this->properties as $prop) {
            if ($prop->type == $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the highest ranked property with the given type
     *
     * Part of the ArrayAccess interface
     *
     * @return string Property value or NULL if empty
     */
    public function offsetGet($type)
    {
        foreach ($this->properties as $prop) {
            if ($prop->type == $type) {
                return $prop->value;
            }
        }
        return null;
    }

    /**
     * Not implemented.
     *
     * Part of the ArrayAccess interface
     *
     * @return void
     */
    public function offsetSet($type, $value)
    {
        throw new LogicException('Changing properties not implemented');
    }

    /**
     * Not implemented.
     *
     * Part of the ArrayAccess interface
     *
     * @return void
     */
    public function offsetUnset($type)
    {
        throw new LogicException('Changing properties not implemented');
    }

    /**
     * Get all properties with the given type
     *
     * @param string $type Property type to filter by
     *
     * @return array Array of XML_XRD_Element_Property objects
     */
    public function getProperties($type = null)
    {
        if ($type === null) {
            return $this->properties;
        }
        $properties = array();
        foreach ($this->properties as $prop) {
            if ($prop->type == $type) {
                $properties[] = $prop;
            }
        }
        return $properties;
    }

}
?>