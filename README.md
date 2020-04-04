# Simple library to interact with photo-druck.de

This library allows to prepare data for photo-druck.de.


## Usage

```php
$pd = new PhotoDruck("YOUR_OWN_ID", [
    'firstname' => 'firstname',
    'lastname' => 'lastname',
    'additional' => 'additional',
    'company' => 'company',
    'street_nr' => 'street_nr',
    'country' => 'country',
    'postcode' => 'postcode',
    'city' => 'city',
    'telephone' => 'telephone',
    'fax' => 'fax',
    'email' => 'email'
]);

// add one print in the format G1_9x13
$pd->addPrint("G1_9x13", "./images/1.png");

// add two prints in the format M1_9x13
$pd->addPrint("M1_9x13", "./images/2.png", 2);

// save folder structure
$pd->out('/tmp/export');

// or output zip files
$pd->outZips('/tmp/export');
```

## Tests

`composer test`