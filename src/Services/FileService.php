<?php

namespace AoFiles\Services;

use AoFiles\Models\File;
use AoFiles\Utils\FileHelper;
use AoScrud\Core\ScrudService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File as LaraFile;

class FileService extends ScrudService
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    //------------------------------------------------------------------------------------------------------------------
    // DYNAMIC
    //------------------------------------------------------------------------------------------------------------------

    protected $dynamicClass;

    protected $dynamicTable;

    protected $dynamicForeign;

    public function setDynamicClass($dynamicClass)
    {
        $parts = explode('.', app()->make($dynamicClass)->files()->getQualifiedForeignKeyName());

        $this->dynamicClass = $dynamicClass;
        $this->dynamicTable = $parts[0];
        $this->dynamicForeign = $parts[1];

        return $this;
    }

    protected function applyDynamicFilter($config)
    {
        $model = $config->model();
        $model->dynamicClass = $this->dynamicClass;
        $model->dynamicTable = $this->dynamicTable;
        $model->dynamicForeign = $this->dynamicForeign;

        $id = $config->data()->get($this->dynamicForeign);

        if (!app()->make($this->dynamicClass)->find($id))
            abort(404);

        $config->model($model->whereHas('dynamicWith', function ($query) use ($id) {
            $query->where('id', $id);
        }));
    }

    //------------------------------------------------------------------------------------------------------------------
    // OWNER
    //------------------------------------------------------------------------------------------------------------------

    private $owner;

    protected function setOwner($config)
    {
        $this->owner = app()->make($this->dynamicClass)->find($config->data()->get($this->dynamicForeign));
        if (!$this->owner)
            abort(404);
    }

    //------------------------------------------------------------------------------------------------------------------
    // CONFIGS
    //------------------------------------------------------------------------------------------------------------------

    protected $maxSize;

    protected $fileTypes;

    protected $rootPath;

    protected $subFolders;

    public function setFileConfig($maxSize, $fileTypes, $rootPath, $subFolders)
    {
        $this->maxSize = $maxSize;
        $this->fileTypes = $fileTypes;
        $this->rootPath = $rootPath;
        $this->subFolders = $subFolders;
        return $this;
    }

    //------------------------------------------------------------------------------------------------------------------
    // CONSTRUCTOR
    //------------------------------------------------------------------------------------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->filesystem = new FileSystem();

        // SEARCH //----------------------------------------------------------------------------------------------------

        $this->search
            ->model(File::class)
            ->columns(['id', 'label', 'extension'])
            ->otherColumns(['folder', 'name', 'description', 'created_at', 'updated_at', 'deleted_at'])
            ->setAllOrders()
            ->rules([
                'id' => '=',
                'extension' => '=',
                [
                    'name' => '%like%|get:search',
                    'label' => '%like%|get:search',
                    'extension' => '%like%|get:search',
                    'description' => '%like%|get:search',
                    'file' => '%like%|get:search',
                ]
            ])
            ->onPrepare(function ($config) {
                $this->applyDynamicFilter($config);
            });

        // READ //------------------------------------------------------------------------------------------------------

        $this->read
            ->model(File::class)
            ->columns($this->search->columns()->all())
            ->with($this->search->with()->all())
            ->otherColumns($this->search->otherColumns()->all())
            ->onPrepare(function ($config) {
                $this->applyDynamicFilter($config);
            });

        // CREATE //----------------------------------------------------------------------------------------------------

        $this->create
            ->model(File::class)
            ->columns(['folder', 'name', 'extension', 'label', 'description'])
            ->rules(function () {
                return [
                    'file' => 'required|max:' . $this->maxSize . '|mimes:' . $this->fileTypes,
                    'folder' => 'required|max:255',
                    'label' => 'sometimes|nullable|max:255',
                ];
            })
            ->onPrepare(function ($config) {
                $this->setOwner($config);

                $file = $config->data()->get('file');
                if (empty($file))
                    return;

                if (empty($config->data()->get('label')))
                    $config->data()->put('label', FileHelper::makeLabel($file->getClientOriginalName()));

                $config->data()->put('extension', $file->getClientOriginalExtension());
                $config->data()->put('name', FileHelper::makeName($config->data()->get('label'), $config->data()->get('extension')));
                $config->data()->put('folder', FileHelper::makeFolder($this->subFolders, $config->data()->toArray()));
            })
            ->onExecuteEnd(function ($config, $result) {
                $result->name = FileHelper::makePrefix($result->id, $result->name);
                $result->save();

                $this->upload($config->data()->get('file'), $result);

                $this->owner = app()->make($this->dynamicClass)->find($config->data()->get($this->dynamicForeign));
                $this->owner->files()->attach($result->id);
            });

        // UPDATE //----------------------------------------------------------------------------------------------------

        $this->update
            ->model(File::class)
            ->columns($this->create->columns()->except(['folder'])->all())
            ->rules(function () {
                return [
                    'file' => 'sometimes|nullable|max:' . $this->maxSize . '|mimes:' . $this->fileTypes,
                    'extension' => 'sometimes|nullable|max:10',
                    'label' => 'sometimes|nullable|max:255',
                ];
            })
            ->onPrepare(function ($config) {
                $this->applyDynamicFilter($config);

                $file = $config->data()->get('file');
                if (empty($file)) {
                    $config->data()->forget('extension');
                    return;
                }

                if (empty($config->data()->get('label')))
                    $config->data()->put('label', FileHelper::makeLabel($file->getClientOriginalName()));

                $config->data()->put('extension', $file->getClientOriginalExtension());
                $config->data()->put('name', FileHelper::makeName($config->data()->get('label'), $config->data()->get('extension')));
                $config->data()->put('name', FileHelper::makePrefix($config->data()->get('id'), $config->data()->get('name')));
            })
            ->onExecuteEnd(function ($config, $result) {
                $file = $config->data()->get('file');
                if (empty($file))
                    return;

                $this->upload($file, $config->obj());
            });;


        // DESTROY //---------------------------------------------------------------------------------------------------

        $this->destroy
            ->model(File::class)
            ->onPrepare(function ($config) {
                $this->applyDynamicFilter($config);
            })
            ->onExecute(function ($config) {
                $this->setOwner($config);
                //$this->owner->files()->detach($config->data()->get('id'));
            });

        // RESTORE //---------------------------------------------------------------------------------------------------

        $this->restore
            ->model(File::class)
            ->onPrepare(function ($config) {
                $this->applyDynamicFilter($config);
            });
    }

    //------------------------------------------------------------------------------------------------------------------
    // ACTIONS
    //------------------------------------------------------------------------------------------------------------------

    public function download(array $data)
    {
        $this->read->columns(['folder', 'name', 'label', 'extension']);
        $file = $this->read($data);

        $data['path'] = $this->getRootPath() . $this->rootPath . $file->folder . '/' . $file->name;
        $data['name'] = $file->label . '.' . $file->extension;

        return $data;
    }

    //------------------------------------------------------------------------------------------------------------------
    // METHODS
    //------------------------------------------------------------------------------------------------------------------

    protected function getRootPath()
    {
        switch (Storage::getDefaultDriver()) {
            case 'local':
                return substr(Storage::getDriver()->getAdapter()->getPathPrefix(), 0, -1);
        }
    }

    protected function upload($file, $result)
    {
        try {
            Storage::put($this->rootPath . $result->folder . '/' . $result->name, LaraFile::get($file));
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    protected function deleteFile($folder, $name)
    {
        //try {
        //    $this->filesystem->delete($folder . '/' . $name);
        //} catch (\Exception $e) {
        //    abort(500, $e->getMessage());
        //}
    }

}