<?php
/**
 * Part of XML_XRD
 *
 * PHP version 5
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @link     http://pear.php.net/package/XML_XRD
 */

require_once 'XML/XRD/PropertyAccess.php';
require_once 'XML/XRD/Element/Link.php';
require_once 'XML/XRD/LoadFileException.php';
require_once 'XML/XRD/Loader/XML.php';

/**
 * Main class used to load XRD documents from string or file.
 *
 * After loading the file, access to links is possible with get() and getAll(),
 * as well as foreach-iterating over the XML_XRD object.
 *
 * Property access is possible with getProperties() and array access (foreach)
 * on the XML_XRD object.
 *
 * Verification that the subject/aliases match the requested URL can be done with
 * describes().
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class XML_XRD extends XML_XRD_PropertyAccess implements IteratorAggregate
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
     * Loads the contents of the given file.
     *
     * Note: Only use file type auto-detection for local files.
     * Do not use it on remote files as the file gets requested several times.
     *
     * @param string $file Path to an XRD file
     * @param string $type File type: xml or json, NULL for auto-detection
     *
     * @return void
     *
     * @throws XML_XRD_LoadFileException When the file is invalid or cannot be
     *                                   loaded
     */
    public function loadFile($file, $type = null)
    {
        if ($type === null) {
            $type = $this->detectTypeFromFile($file);
        }
        $loader = $this->getLoader($type);
        $loader->loadFile($file);
    }

    /**
     * Loads the contents of the given string
     *
     * @param string $str  XRD string
     * @param string $type File type: xml or json, NULL for auto-detection
     *
     * @return void
     *
     * @throws XML_XRD_LoadFileException When the string is invalid or cannot be
     *                                   loaded
     */
    public function loadString($str, $type = null)
    {
        if ($type === null) {
            $type = $this->detectTypeFromString($str);
        }
        $loader = $this->getLoader($type);
        $loader->loadString($str);
    }

    /**
     * Creates a XRD loader object for the given type
     *
     * @param string $type File type: xml or json
     *
     * @return XML_XRD_Loader
     */
    protected function getLoader($type)
    {
        $class = 'XML_XRD_Loader_' . strtoupper($type);
        $file = str_replace('_', '/', $class) . '.php';
        include_once $file;
        if (class_exists($class)) {
            return new $class($this);
        }

        throw new XML_XRD_LoadFileException(
            'No loader for XRD type "' . $type . '"',
            XML_XRD_LoadFileException::NO_LOADER
        );
    }

    /**
     * Tries to detect the file type (xml or json) from the file content
     *
     * @param string $file File name to check
     *
     * @return string File type ('xml' or 'json')
     *
     * @throws XML_XRD_LoadFileException When opening the file fails.
     */
    protected function detectTypeFromFile($file)
    {
        if (!file_exists($file)) {
            throw new XML_XRD_LoadFileException(
                'Error loading XRD file: File does not exist',
                XML_XRD_LoadFileException::OPEN_FILE
            );
        }
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new XML_XRD_LoadFileException(
                'Cannot open file to determine type',
                XML_XRD_LoadFileException::OPEN_FILE
            );
        }

        $str = (string)fgets($handle, 10);
        fclose($handle);
        return $this->detectTypeFromString($str);
    }

    /**
     * Tries to detect the file type from the content of the file
     *
     * @param string $str Content of XRD file
     *
     * @return string File type ('xml' or 'json')
     *
     * @throws XML_XRD_LoadFileException When the type cannot be detected
     */
    protected function detectTypeFromString($str)
    {
        if (substr($str, 0, 1) == '{') {
            return 'json';
        } else if (substr($str, 0, 5) == '<?xml') {
            return 'xml';
        }

        throw new XML_XRD_LoadFileException(
            'Detecting file type failed',
            XML_XRD_LoadFileException::DETECT_TYPE
        );
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
                $links[]    = $link;
                $exactType |= $typeFallback && $type !== null
                    && $link->type == $type;
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
     * Return the iterator object to loop over the links
     *
     * Part of the IteratorAggregate interface
     *
     * @return Traversable Iterator for the links
     */
    public function getIterator()
    {
        return new ArrayIterator($this->links);
    }

    /**
     * Converts this XRD object to XML.
     *
     * @return string Generated XML
     */
    public function toXML()
    {
        include_once 'XML/XRD/Serializer/XML.php';
        $s = new XML_XRD_Serializer_XML($this);
        return (string)$s;
    }

    /**
     * Converts this XRD object to JSON (for JRD files, "host-meta.json").
     *
     * @return string Generated JSON string
     */
    public function toJSON()
    {
        include_once 'XML/XRD/Serializer/JSON.php';
        $s = new XML_XRD_Serializer_JSON($this);
        return (string)$s;
    }
}

?>