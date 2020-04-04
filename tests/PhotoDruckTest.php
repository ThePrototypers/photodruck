<?php

namespace Prototypers\PhotoDruck\Tests;

use PHPUnit\Framework\TestCase;
use Prototypers\PhotoDruck\PhotoDruck;

class PhotoDruckTest extends TestCase
{
    public function test_photo_exists()
    {
        $pd = new PhotoDruck(1, []);
        $pd->addPrint("G1_9x13", "./tests/test.png");

        $this->assertEquals(1, count($pd->photos));
    }

    public function test_photos_are_added_in_the_right_amount()
    {
        $pd = new PhotoDruck("123", []);
        $pd->addPrint("G1_9x13", "./tests/test.png");
        $pd->addPrint("G1_9x13", "./tests/test.png");
        $pd->addPrint("M1_9x13", "./tests/test.png");

        $this->assertEquals(2, count($pd->photos));
        $this->assertArrayHasKey('G1_9x13', $pd->photos);
        $this->assertArrayHasKey('M1_9x13', $pd->photos);

        $this->assertEquals(2, count($pd->photos['G1_9x13']));
        $this->assertEquals(1, count($pd->photos['M1_9x13']));
    }

    public function test_photos_are_copied_correctly()
    {
        $id = "123";

        $pd = new PhotoDruck($id, []);
        $pd->addPrint("G1_9x13", "./tests/test.png");
        $pd->addPrint("M1_9x13", "./tests/test.png", 2);

        $tmp_dir = PhotoDruck::temp_folder();

        $pd->out($tmp_dir);

        $this->assertFileExists(
            PhotoDruck::join_paths($tmp_dir, $id, "adresse.csv")
        );

        $this->assertFileExists(
            PhotoDruck::join_paths($tmp_dir, $id, "G1_9x13", "1x", "test.png")
        );

        $this->assertFileExists(
            PhotoDruck::join_paths($tmp_dir, $id, "M1_9x13", "2x", "test.png")
        );
    }

    public function test_address_is_correct()
    {
        $id = "123";

        $pd = new PhotoDruck($id, [
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
        $tmp_dir = PhotoDruck::temp_folder();
        $pd->out($tmp_dir);

        $addr_file = PhotoDruck::join_paths($tmp_dir, $id, "adresse.csv");

        $this->assertFileExists($addr_file);
        $this->assertEquals(
            'firstname;lastname;company;additional;street_nr;country;postcode;city;telephone;fax;email',
            trim(file_get_contents($addr_file))
        );
    }

    public function test_zip_output()
    {
        $id = "123";

        $pd = new PhotoDruck($id, []);
        $tmp_dir = PhotoDruck::temp_folder();
        $pd->outZips($tmp_dir);

        $this->assertFileExists(
            PhotoDruck::join_paths($tmp_dir, $id . '.zip')
        );
    }
}
