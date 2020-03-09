<?php


namespace App\AuthModule;


use Framework\Database\Query;
use Framework\Database\QueryResult;
use Framework\Database\Table;

class TotpBackupTable extends Table
{

    protected $table = "totp_backup";

    protected $entity = TotpBackup::class;

    /**
     * @param string $field
     * @param string $value
     * @return \Framework\Database\QueryResult
     */
    public function findAllBy(string $field, string $value): QueryResult
    {
        return $this->makeQuery()->where("$field = :field")->params(["field" => $value])->fetchAll();
    }

}
