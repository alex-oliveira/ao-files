<?php

if (!function_exists('AoFiles')) {

    /**
     * @return \AoFiles\Utils\Tools
     */
    function AoFiles()
    {
        return app('AoFiles');
    }

}