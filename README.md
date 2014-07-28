# Import Tools

Tools to transform CSV files to cleaner XML files. I've used these for
across migration projects to automate very repetitive aspects. This
helps focus on cleaning up and transforming the data as needed by letting
the classes take care of reading, writing each format.

## readCsv

The **readCsv** class reads in a CSV file and transforms it into an array. Heading row
names are turned into field names. Empty lines are skipped, empty fields are ignored.

~~~~
use oam\importtools\readCsv;

// read in our Sample CSV file
// and clean up incoming data with sampleParser
$csv = new readCsv(__DIR__ . '/sample.csv');
$csv->setKey('id');
$items = $csv->getArray();
~~~~

Alternatively, you can pass getArray a function to parse and cleanup incoming data.

~~~~
$items = $csv->getArray(function($item) {
    // skip items without an email field
    if (empty($item->email)) {
        // return false will skip this item
        return false;
    }

    // other code as needed
    return $item;
});
~~~~

## toXml

The **toXml** class takes an array of associate arrays or classes and output it as an XML file.

~~~~
// output XML
// specifcy our root and per-item tags
$xml = new toXml("profiles", "profile");
$xml->convert($items);
echo $xml->saveXML();
~~~~

If your data structure is very complicated, or you need to output it in a specific way, you can
set a