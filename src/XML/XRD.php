<?php
require_once 'XML/XRD/Element/Link.php';
require_once 'XML/XRD/Element/Property.php';

class XML_XRD implements ArrayAccess, Iterator
{
    /**
     * XRD subject
     *
     * @var string
     */
    public $subject;

    /**
     * Array of subject alias strings
     *
     * @var array
     */
    public $aliases = array();

    /**
     * Array of link objects
     *
     * @var array
     */
    public $links = array();

    /**
     * Array of property objects
     *
     * @var array
     */
    public $properties = array();

    /**
     * Unix timestamp when the document expires.
     * NULL when no expiry date set.
     *
     * @var integer|null
     */
    public $expires;

    /**
     * xml:id of the XRD document
     *
     * @var string|null
     */
    public $id;

    /**
     * Position of the iterator
     */
    protected $iteratorPos = 0;

    /**
     * XRD 1.0 namespace
     */
    const NS_XRD = 'http://docs.oasis-open.org/ns/xri/xrd-1.0';



    /**
     * Loads the contents of the given file
     *
     * @param string $file Path to an XRD file
     *
     * @return boolean True when all went well
     */
    public function loadFile($file)
    {
        $old = libxml_use_internal_errors(true);
        $x = simplexml_load_file($file);
        libxml_use_internal_errors($old);
        //FIXME: throw exception?
        if ($x === false) {
            return false;
        }
        return $this->load($x);
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
        $x = simplexml_load_string($xml);
        if ($x === false) {
            return false;
        }
        return $this->load($x);
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
        $ns = $x->getDocNamespaces();
        if ($ns[''] !== self::NS_XRD) {
            return false;
        }
        if ($x->getName() != 'XRD') {
            return false;
        }

        if (isset($x->Subject)) {
            $this->subject = (string)$x->Subject;
        }
        foreach ($x->Alias as $xAlias) {
            $this->aliases[] = (string)$xAlias;
        }

        foreach ($x->Link as $xLink) {
            $this->links[] = new XML_XRD_Element_Link($xLink);
        }

        foreach ($x->Property as $xProp) {
            $this->properties[] = new XML_XRD_Element_Property($xProp);
        }

        if (isset($x->Expires)) {
            $this->expires = strtotime($x->Expires);
        }

        $xmlAttrs = $x->attributes('http://www.w3.org/XML/1998/namespace');
        if (isset($xmlAttrs['id'])) {
            $this->id = (string)$xmlAttrs['id'];
        }

        return true;
    }

    /**
     * Checks if the XRD document describes the given URI.
     *
     * This should always be used to make sure the XRD file
     * is the correct one for e.g. the given host, and not a copycat.
     *
     * Checks against the subject and aliases
     *
     * @param string $uri An URI that the document is expected to describe
     *
     * @return boolean True or false
     */
    public function describes($uri)
    {
        if ($this->subject == $uri) {
            return true;
        }
        foreach ($this->aliases as $alias) {
            if ($alias == $uri) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the link with highest priority for the given relation and type.
     *
     * @param string  $rel          Relation name
     * @param string  $type         MIME Type
     * @param boolean $typeFallback When true and no link with the given type
     *                              could be found, the best link without a
     *                              type will be returned
     *
     * @return XML_XRD_Element_Link Link object or NULL if none found
     */
    public function get($rel, $type = null, $typeFallback = true)
    {
        $links = $this->getAll($rel, $type, $typeFallback);
        if (count($links) == 0) {
            return null;
        }

        return $links[0];
    }


    /**
     * Get all links with the given relation and type, highest priority first.
     *
     * @param string  $rel          Relation name
     * @param string  $type         MIME Type
     * @param boolean $typeFallback When true and no link with the given type
     *                              could be found, the best link without a
     *                              type will be returned
     *
     * @return array Array of XML_XRD_Element_Link objects
     */
    public function getAll($rel, $type = null, $typeFallback = true)
    {
        $links = array();
        $exactType = false;
        foreach ($this->links as $link) {
            if ($link->rel == $rel
                && ($type === null || $link->type == $type
                    || $typeFallback && $link->type === null)
            ) {
                $links[] = $link;
                $exactType |= $typeFallback && $type !== null && $link->type == $type;
            }
        }
        if ($exactType) {
            //remove all links without type
            $exactlinks = array();
            foreach ($links as $link) {
                if ($link->type !== null) {
                    $exactlinks[] = $link;
                }
            }
            $links = $exactlinks;
        }
        return $links;
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

    /**
     * Get the current iterator's link object
     *
     * Part of the Iterator interface
     *
     * @return XML_XRD_Element_Link Link element
     */
    public function current()
    {
        return $this->links[$this->iteratorPos];
    }

    /**
     * Move to the next link object
     *
     * Part of the Iterator interface
     *
     * @return void
     */
    public function next()
    {
        ++$this->iteratorPos;
    }

    /**
     * Get the current iterator key
     *
     * Part of the Iterator interface
     *
     * @return integer Iterator position
     */
    public function key()
    {
        return $this->iteratorPos;
    }

    /**
     * Check if the current iterator position is valid
     *
     * Part of the Iterator interface
     *
     * @return boolean True if the current position is valid
     */
    public function valid()
    {
        return isset($this->links[$this->iteratorPos]);
    }

    /**
     * Reset the iterator position back to the first element
     *
     * Part of the Iterator interface
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteratorPos = 0;
    }
}

?>