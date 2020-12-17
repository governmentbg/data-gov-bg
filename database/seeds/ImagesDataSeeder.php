<?php

use App\Image;
use Illuminate\Database\Seeder;

class ImagesDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $folder = storage_path('images');
        $files = scandir($folder);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $image = Image::find($file);

            if (!empty($image)) {
                try {
                    $image->data = file_get_contents($folder .'/'. $file);
                    $image->save();
                } catch (Exception $ex) {
                    Log::error($ex->getMessage());

                    continue;
                }
            }

            unlink($folder .'/'. $file);
        }
    }
}
