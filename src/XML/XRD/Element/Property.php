<?php

class XML_XRD_Element_Property
{
    public $value;
    public $type;

    public function __construct(SimpleXMLElement $x)
    {
        if (isset($x['type'])) {
            $this->type = (string)$x['type'];
        }
        $s = (string)$x;
        if ($s != '') {
            $this->value = $s;
        }
    }
}

?>