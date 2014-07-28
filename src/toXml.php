<?php
namespace oam\ImportTools;
use \DOMDocument;

/**
 * Class toXml
 *
 * Helps mundane tasks for creating a DOM XML document from an array
 * allowing the programmer to focus on handling special cases via callbacks;
 *
 * @package oam
 */
class toXml {

    protected $root_name;
    protected $item_name;

    protected $root;
    protected $handlers = [];

    public $dom;

    public function __construct($root_name, $item_name, $version = "1.0") {

        $this->root_name = $root_name;
        $this->item_name = $item_name;

        // output xml
        $this->dom = new DOMDocument($version);
        $this->dom->formatOutput = true;

        // create root element
        $this->root = $this->dom->createElement($this->root_name);
        $this->dom->appendChild($this->root);
    }

    /**
     * setHandler
     *
     * Allow a custom function to handle converting an item to DOM.
     * $callable must be a function with the following signature:
     *
     * ($name, $attribute, \DOMDocument $dom)
     *
     * it should return a DomNode to insert into $dom
     *
     * @param $field
     * @param $callable
     */
    public function setHandler($field, $callable) {
        $this->handlers[$field] = $callable;
    }

    /**
     * saveXML
     *
     * returns content as XML
     * @return string
     */
    public function saveXML() {
        return $this->dom->saveXML();
    }

    /**
     * Convert an array of data to xml nodes.
     *
     * Assumes that each item in the array is either a class (in which case it
     * iterates over the public properties of the class. Or that the item is
     * an associative array.
     *
     * Keys and class properties become tags, values become text nodes.
     *
     * @param $data
     * @throws \InvalidArgumentException
     */
    public function convert($data) {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Array Expected');
        }

        foreach ($data as $row) {
            $item = $this->dom->createElement($this->item_name);
            $this->root->appendChild($item);

            // add item properties
            if (!is_array($row)) {
                $row = (array) $row;
            }

            foreach ($row as $name => $attribute) {
                if (0 !== $attribute && empty($attribute)) {
                    continue;
                }

                if (isset($this->handlers[$name])) {
                    $elt = call_user_func($this->handlers[$name], $name, $attribute, $this->dom);
                } else {
                    $elt = $this->dom->createElement($name);

                    if (is_array($attribute)) {
                        foreach ($attribute as $year) {
                            $elt2 = $this->dom->createElement('value');
                            $elt2->appendChild($this->dom->createTextNode($year));
                            $elt->appendChild($elt2);
                        }
                    } else {
                        $elt->appendChild($this->dom->createTextNode($attribute));
                    }
                }
                $item->appendChild($elt);
            }
        }
    }
}