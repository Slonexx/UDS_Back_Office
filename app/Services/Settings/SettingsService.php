<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\File;

class SettingsService
{
    public function getSettings()
    {
        //Проверка есть ли необходимые найстройки

        $directory = public_path().'/Config/data';

        $filesInFolder = File::files($directory);

        $usersSettings = [];

        foreach($filesInFolder as $file) {
            //$file = pathinfo($path);
            if(str_ends_with($file->getFilename(),'.json')){

                $data = file_get_contents($directory.'/'.$file->getFilename());
                $unser = json_encode( unserialize($data) );
                $setting = json_decode($unser);
                $usersSettings[] = $setting;
            }
        }

        return $usersSettings;
    }
}
