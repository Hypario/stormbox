<?php

namespace Hypario\Database;

// L'absence de résultat n'est pas une exception
// Surtout quand tu as la convenience method exists() sur Table.php
class NoRecordException extends \Exception
{

}
