<?php
require __DIR__ . '/../src/readCsv.php';
require __DIR__ . '/../src/toXml.php';

use oam\importtools\readCsv;
use oam\importtools\toXml;

// read in our Sample CSV file
// and clean up incoming data with sampleParser
$csv = new readCsv(__DIR__ . '/sample.csv');
$csv->setKey('id');
$items = $csv->getArray('ProfileParser');

// output XML
$xml = new toXml("profiles", "profile");
$xml->setHandler("tags", "tagHandler");
$xml->convert($items);
echo $xml->saveXML();

function profileParser($item) {
    // ignore items that are empty
    $item = array_filter((array) $item);

    if (empty($item['email'])) {
        // skip profiles without an email
        return false;
    }

    // create a first+last item
    $item['last_first'] = $item['last_name'] . ', ' . $item['first_name'];

    // cleanup & split the tags column into an array
    if (isset($item['tags'])) {
        $tags = explode(',', $item['tags']);
        $tags = array_filter($tags);
        $tags = array_map('trim', $tags);

        $item['tags'] = $tags;
    }

    // clean date_joined format
    $date = new DateTime($item['date_joined']);
    $item['date_joined_clean'] = $date->format('Y-m-d');

    return $item;
}

// we'll handle tags field our self
function tagHandler($name, $attribute, $dom) {
    
    // create our root tag
    $tags = $dom->createElement('roles');

    foreach ($attribute as $row) {
        $tag = $dom->createElement('role');
        $tag->appendChild($dom->createTextNode($row));
        $tags->appendChild($tag);
    }

    return $tags;
}