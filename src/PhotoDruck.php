<?php

namespace Prototypers\PhotoDruck;

use Prototypers\PhotoDruck\Exceptions\InvalidPhoto;

class PhotoDruck
{
    public $photos = array();
    public $id = 'unknown';
    public $address = [];

    public function __construct($id, $address)
    {
        $this->id = $id;
        $this->address = $address;
    }

    public function addPrint($format, $path, $count = 1)
    {
        if (!file_exists($path)) {
            throw InvalidPhoto::fileNotFound($path);
        }

        if (isset($this->photos[$format])) {
            $this->photos[$format][] = [$count, $path];
        } else {
            $this->photos[$format] = array(
                [$count, $path]
            );
        }
    }

    public function echo()
    {
        foreach ($this->photos as $format => $photos) {
            echo '# '. $format;
            foreach ($photos as $p) {
                echo ' - ' . $p[0] . 'x '. $p[1];
            }
        }
    }

    public function out($folder)
    {
        $path = self::join_paths(
            $folder,
            $this->id
        );

        if (file_exists($path)) {
            throw new \Exception('this path already exists');
        }
        mkdir($path, 0755, true);

        $this->write_address(
            self::join_paths($path, 'adresse.csv')
        );

        foreach ($this->photos as $format => $photos) {
            $formatPath = self::join_paths($path, $format);

            foreach ($photos as $p) {
                $countPath = self::join_paths($formatPath, $p[0] . 'x');

                if (!file_exists($countPath)) {
                    mkdir($countPath, 0755, true);
                }

                copy($p[1], self::join_paths($countPath, basename($p[1])));
            }
        }
    }

    public function outZips($folder)
    {
        $tmp_dir = self::temp_folder();
        $this->out($tmp_dir);

        $zip = new \ZipArchive;
        $zip->open(
            self::join_paths($folder, $this->id . '.zip'),
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE
        );

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tmp_dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr(
                    $filePath,
                    strlen(
                        (new \SplFileInfo($tmp_dir))->getRealPath()
                    ) + 1
                );

                $zip->addFile(
                    $filePath,
                    self::join_paths($this->id, $relativePath)
                );
            }
        }

        $zip->close();
    }

    protected function write_address($path)
    {
        // <Vorname>;<Name>;<Firma>;<Zusatz>;<Strasse Hausnr>;<Land>;<P>;<Ort>;<Tel>;<Fax>;<email>
        $data = array(
            $this->address['firstname'] ?? '',
            $this->address['lastname'] ?? '',
            $this->address['company'] ?? '',
            $this->address['additional'] ?? '',
            $this->address['street_nr'] ?? '',
            $this->address['country'] ?? '',
            $this->address['postcode'] ?? '',
            $this->address['city'] ?? '',
            $this->address['telephone'] ?? '',
            $this->address['fax'] ?? '',
            $this->address['email'] ?? '',
        );

        $fp = fopen($path, 'w');
        fputcsv($fp, $data, ';');
        fclose($fp);
    }

    public static function join_paths()
    {
        $paths = array();

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }

    // https://stackoverflow.com/a/30010928
    public static function temp_folder($dir = null, $prefix = 'tmp_', $mode = 0700, $maxAttempts = 1000)
    {
        /* Use the system temp dir by default. */
        if (is_null($dir)) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            !mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );

        return $path;
    }
}
