<?php


namespace Hypario;

/**
 * Entity that correspond to a file
 * Class File
 * @package Hypario
 */
class File
{

    public $id;

    public $path;

    public $uuid;

    public function setUuid($uuid)
    {
        $this->uuid =  $this->binToUuid($uuid);
    }

    /**
     * transform a binary to uuid when received from DB
     * @param string $bin
     * @return string
     */
    private function binToUuid(string $bin): string
    {
        return join("-", unpack("H8time_low/H4time_mid/H4time_hi/H4clock_seq_hi/H12clock_seq_low", $bin));
    }

}
