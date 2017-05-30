<?php

namespace AoFiles\Utils\Tools;

use AoScrud\Utils\Traits\BuildTrait;
use Illuminate\Support\Facades\Schema as LaraSchema;
use Illuminate\Database\Schema\Blueprint;

class Schema
{
    use BuildTrait;

    protected $prefix = 'ao_files_x_';

    public function table($table)
    {
        return $this->prefix . '' . $table;
    }

    public function create($table, $fk = null, $type = 'integer')
    {
        if (is_null($fk))
            $fk = str_singular($table) . '_id';

        LaraSchema::create($this->table($table), function (Blueprint $t) use ($table, $fk, $type) {
            $t->$type($fk)->unsigned();
            $t->foreign($fk, 'fk_' . $table . '_x_ao_files')->references('id')->on($table);

            $t->bigInteger('file_id')->unsigned();
            $t->foreign('file_id', 'fk_ao_files_x_' . $table)->references('id')->on('ao_files_files');

            $t->primary([$fk, 'file_id'], 'pk_ao_files_x_' . $table);
        });
    }

    public function drop($table)
    {
        LaraSchema::dropIfExists($this->table($table));
    }

}