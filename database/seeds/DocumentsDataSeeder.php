<?php

use App\Document;
use Illuminate\Database\Seeder;

class DocumentsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $folder = storage_path('docs');
        $files = scandir($folder);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $doc = Document::find($file);

            if (!empty($doc)) {
                try {
                    $doc->data = file_get_contents($folder .'/'. $file);
                    $doc->save();
                } catch (Exception $ex) {
                    Log::error($ex->getMessage());

                    continue;
                }
            }

            unlink($folder .'/'. $file);
        }
    }
}
