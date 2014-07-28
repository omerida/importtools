<?php
namespace oam\ImportTools;

use SplFileObject;

class readCsv {

    protected $file;
    protected $key = null;
    protected $outputArray = [];

    public function __construct($filename) {
        $this->setFile($filename);
    }

    /**
     * setKey
     *
     * Optionally specify the name of a column to use as a key for the
     * final array.
     *
     * @param $key
     */
    public function setKey($key) {
        $this->key = $key;
    }

    /**
     * setArray
     *
     * Optionally specify the starting state of the array to import items into. Useful
     * if you are combining multiple files into one data structure.
     *
     * @param $array
     */
    public function setArray($array) {
        $this->outputArray = $array;
    }

    /**
     * @param $filename
     * @return $this
     */
    public function setFile($filename) {
        try {
            $this->file = new SplFileObject($filename, 'r');
            $this->file->setFlags(SplFileObject::READ_CSV);
        } catch (RuntimeException $e) {
            printf("Error opening csv: %s\n", $e->getMessage());
        }

        return $this;
    }


    /**
     * Simplifies reading a CSV file and using a callback to format the row
     * to a StdClass object. Assumes the first row is a header and changes those labels
     * to array keys.
     *
     * @param callable $parser
     * @return mixed
     */

    function getArray(Callable $parser = null) {
        $header = $this->file->current();

        $i = 0;
        foreach ($this->file as $row) {

            if (1 == count($row) && null == $row[0]) {
                continue;
            }

            $i++;
            if (1 == $i) {
                continue;
            }
            // create a class
            $item = new \StdClass;
            foreach ($row as $index => $value) {
                $field = str_replace(' ', '_', $header[$index]);
                $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
                $field = strtolower($field);
                $item->$field = trim($value);
            }

            // parser function does the actual work
            if (empty($parser)) {
                $item = $this->simpleParser($item, $this->key);
            } else if (is_callable($parser)) {
                $item = $parser($item, $this->key);
            } else {
                throw new \BadMethodCallException("Callable Expected");
            }

            if (false != $item) {
                if (is_array($item)) {
                    $item = (object) $item;
                }
                $this->outputArray[] = $item;
            }
        }

        return $this->outputArray;
    }

    /**
     * Basic parser and starting point for writing your own parser to fix data
     *
     * @param $collection
     * @param $key
     * @param $item
     * @return mixed
     */
    public function simpleParser($item, $key) {

        // drop empty fields
        $item = array_filter((array) $item);

        if (empty($item)) {
            return false;
        }

        // add item to our collection
        return (object) $item;
    }
}