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

namespace XRD;

use XRD\Document;
use XRD\Serializer\SerializerException;

/**
 * Serialization dispatcher - loads the correct serializer for saving XRD data.
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class Serializer
{
    /**
     * XRD data storage
     *
     * @var Document
     */
    protected $xrd;

    /**
     * Init object with xrd object
     *
     * @param Document $xrd Data storage the data are fetched from
     */
    public function __construct(Document $xrd)
    {
        $this->xrd = $xrd;
    }

    /**
     * Convert the XRD data into a string of the given type
     *
     * @param string $type File type: xml or json
     *
     * @return string Serialized data
     */
    public function to($type)
    {
        return (string)$this->getSerializer($type);
    }

    /**
     * Creates a XRD loader object for the given type
     *
     * @param string $type File type: xml or json
     *
     * @return Serializer
     */
    protected function getSerializer($type)
    {
        $class = 'XRD\\Serializer\\' . strtoupper($type);
        if (class_exists($class)) {
            return new $class($this->xrd);
        }

        throw new SerializerException(
            'No serializer for type "' . $type . '"',
            SerializerException::NO_SERIALIZER
        );
    }
}
?>
