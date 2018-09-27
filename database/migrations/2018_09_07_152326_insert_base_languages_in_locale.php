<?php

use App\Locale;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertBaseLanguagesInLocale extends Migration
{
    public function __construct()
    {
        $this->languages = [
            'bg'    => true,
            'en'    => true,
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Locale::all()->count()) {
            foreach ($this->languages as $language => $active) {
                Locale::create([
                    'locale'    => $language,
                    'active'    => $active,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->languages as $language) {
            if (!Locale::where('locale', $language)->count()) {
                Locale::where(['locale' => $language['name']])->delete();
            }
        }
    }
}
