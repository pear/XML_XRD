<?php
require_once 'XML/XRD/PropertyAccess.php';
require_once 'XML/XRD/Element/Link.php';

class XML_XRD extends XML_XRD_PropertyAccess implements Iterator
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

        $this->loadProperties($x);

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