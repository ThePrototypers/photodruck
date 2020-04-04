<?php

namespace Prototypers\PhotoDruck\Exceptions;

use Exception;

class InvalidPhoto extends Exception
{
    public static function fileNotFound($path): self
    {
        return new self("no such image: ".$path);
    }
}
