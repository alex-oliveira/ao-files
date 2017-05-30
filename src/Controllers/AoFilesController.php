<?php

namespace AoFiles\Controllers;

use AoFiles\Services\FileService;
use AoScrud\Core\ScrudController;
use Symfony\Component\HttpFoundation\File\File;

class AoFilesController extends ScrudController
{

    //------------------------------------------------------------------------------------------------------------------
    // DYNAMIC
    //------------------------------------------------------------------------------------------------------------------

    protected $dynamicClass;

    public function getDynamicClass()
    {
        return $this->dynamicClass;
    }

    //------------------------------------------------------------------------------------------------------------------
    // ATTRIBUTES
    //------------------------------------------------------------------------------------------------------------------

    protected $maxSize = 20000;

    protected $fileTypes = 'jpeg,jpg,bmp,png,gif,doc,docx,xlsx,xls,pdf,txt,csv,ppt,pptx,zip';

    protected $rootPath = 'AO_FILES_ROOT_PATH';

    protected $subFolders;

    //------------------------------------------------------------------------------------------------------------------
    // CONSTRUCTOR
    //------------------------------------------------------------------------------------------------------------------

    public function __construct(FileService $service)
    {
        $this->rootPath = env($this->rootPath, '');
        $this->service = $service->setDynamicClass($this->getDynamicClass())
            ->setFileConfig($this->maxSize, $this->fileTypes, $this->rootPath, $this->subFolders);
    }

    //------------------------------------------------------------------------------------------------------------------
    // ACTIONS
    //------------------------------------------------------------------------------------------------------------------

    public function download()
    {
        $data = $this->service->download(AoScrud()->params()->all());
        return response()->download($data['path'], $data['name']);
    }

}