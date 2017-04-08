<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 * @subpackage Conditionals
 *
 * Gather all conditionals and optionally wrap them in a "lambda".
 */

namespace Dbmover\Mysql\Conditionals;

use Dbmover\Conditionals;

class Plugin extends Conditionals\Plugin
{
    protected function wrap(string $sql) : string
    {
        $tmp = 'tmp_'.md5(microtime(true));
        return <<<EOT
DROP PROCEDURE IF EXISTS $tmp;
CREATE PROCEDURE $tmp()
BEGIN
    $sql
END;
CALL $tmp();
DROP PROCEDURE $tmp;

EOT;
    }
}

