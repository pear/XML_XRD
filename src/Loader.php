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

use XRD\Loader\LoaderException;

/**
 * File/string loading dispatcher.
 * Loads the correct loader for the type of XRD file (XML or JSON).
 * Also provides type auto-detection.
 *
 * @category XML
 * @package  XML_XRD
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/XML_XRD
 */
class Loader
{
    /**
     * Create new instance
     *
     * @param Document $xrd Document object to load
     */
    public function __construct(Document $xrd)
    {
        $this->xrd = $xrd;
    }

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
     * @throws LoaderException When the file is invalid or cannot be loaded
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
     * @throws LoaderException When the string is invalid or cannot be loaded
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
     * @return Loader
     */
    protected function getLoader($type)
    {
        $class = 'XRD\\Loader\\' . strtoupper($type);
        if (class_exists($class)) {
            return new $class($this->xrd);
        }

        throw new LoaderException(
            'No loader for XRD type "' . $type . '"',
            LoaderException::NO_LOADER
        );
    }

    /**
     * Tries to detect the file type (xml or json) from the file content
     *
     * @param string $file File name to check
     *
     * @return string File type ('xml' or 'json')
     *
     * @throws LoaderException When opening the file fails.
     */
    public function detectTypeFromFile($file)
    {
        if (!file_exists($file)) {
            throw new LoaderException(
                'Error loading XRD file: File does not exist',
                LoaderException::OPEN_FILE
            );
        }
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new LoaderException(
                'Cannot open file to determine type',
                LoaderException::OPEN_FILE
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
     * @throws LoaderException When the type cannot be detected
     */
    public function detectTypeFromString($str)
    {
        if (substr($str, 0, 1) == '{') {
            return 'json';
        } else if (substr($str, 0, 5) == '<?xml') {
            return 'xml';
        }

        throw new LoaderException(
            'Detecting file type failed',
            LoaderException::DETECT_TYPE
        );
    }


}

?>
