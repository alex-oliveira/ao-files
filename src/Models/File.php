<?php

namespace AoFiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{

    use SoftDeletes;

    //------------------------------------------------------------------------------------------------------------------
    // DYNAMIC
    //------------------------------------------------------------------------------------------------------------------

    public $dynamicClass;

    public $dynamicTable;

    public $dynamicForeign;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dynamicWith()
    {
        return $this->belongsToMany($this->dynamicClass, $this->dynamicTable, 'file_id', $this->dynamicForeign);
    }

    //------------------------------------------------------------------------------------------------------------------
    // ATTRIBUTES
    //------------------------------------------------------------------------------------------------------------------

    protected $table = 'ao_files_files';

    protected $fillable = ['folder', 'name', 'extension', 'label', 'description'];

}