<?php

/**
 * @package Dbmover
 * @subpackage Mysql
 * @subpackage Conditionals
 *
 * Gather all conditionals and optionally wrap them in a "lambda".
 */

namespace Dbmover\Mysql\Conditionals;

use Dbmover\Core;

class Plugin extends Core\Plugin
{
    private $wrapped = [];

    public function __invoke(string $sql) : string
    {
        if (preg_match_all('@^IF.*?^END IF;$@ms', $sql, $ifs, PREG_SET_ORDER)) {
            foreach ($ifs as $if) {
                $tmp = 'tmp_'.md5(microtime(true));
                $code = <<<EOT
DROP PROCEDURE IF EXISTS $tmp;
CREATE PROCEDURE $tmp()
BEGIN
    {$if[0]}
END;
CALL $tmp();
DROP PROCEDURE $tmp;

EOT;
                $this->wrapped[] = [$code, $if[0]];
                $this->loader->addOperation($code, $if[0]);
                $sql = str_replace($if[0], '', $sql);
            }
        }
        return $sql;
    }

    public function __destruct()
    {
        foreach ($this->wrapped as $code) {
            $this->loader->addOperation(...$code);
        }
    }
}

