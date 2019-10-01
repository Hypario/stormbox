<?php

namespace App\ApiModule\Tables;

use App\ApiModule\Entity\File;
use Framework\Database\Table;

class FilesTable extends Table
{

    protected $table = 'files';

    protected $entity = File::class;

}
