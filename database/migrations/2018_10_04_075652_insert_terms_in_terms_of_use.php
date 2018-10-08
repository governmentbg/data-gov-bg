<?php

use App\TermsOfUse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertTermsInTermsOfUse extends Migration
{
    public function __construct()
    {
        $this->termsOfUse = [
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на информация без защитени авторски права.',
                    'en'    => 'Terms of use of information without copyright protection.',
                ],
                'descript'      => [
                    'bg'    => 'Настоящата информация, включително всички нейни части, се предоставя за повторно използване, без да е необходимо заплащането на допълнително възнаграждение или обезщетение, в степента, в която принадлежи на предоставящата я организация от обществения сектор. Организацията от обществения сектор предоставя публично информацията при настоящите условия, като знае за своите права върху нея, както и значението и правните последици от това предоставяне за повторно използване.',
                    'en'    => 'This information, including all its parts, is provided to be reused without necessity of  additional remuneration or compensation to the extent that its belongs to the public organisations which provides it. The public organisation provides public information under these terms, as well as the significance and legal implications of this re-use.',
                ],
                'is_default'    => true,
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyright.',
                ],
                'descript'      => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyright.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Споделяне на споделеното.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Share of shared.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, включително с търговска цел, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Когато променяте произведението и създавате производни и сборни произведения, можете да ги разпространявате само при същите условия, при които сте получили оригиналното произведение за повторно използване.',
                    'en'    => 'You can use, reproduce, distribute and modify the given work and also create derived and assembly works, including for commercial purposes, without the consent of third party, but under condition you designate the name, alias, or other identifying sign, under each use.When you modify a work and create derivative works and works of the same kind, you may only distribute the work under the same conditions as when you received the original work for re-use.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без производни и сборни произведения.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Without derivatives and pooled works.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате и разпространявате произведението, включително с търговска цел, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Нямате право да разпространявате производни и сборни произведения.',
                    'en'    => 'You may use, reproduce and distribute the work, including for commercial purposes, without the consent of third party, but under condition you designate the name, alias, or other identifying sign, under each use. You have no rights to distribute derivative works and assembly works.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Не можете да използвате произведението за търговска цел.',
                    'en'    => 'You may use, reproduce, distribute and modify the work and create derivative works and assembly works without the consent of a third party, but under condition you designate the name, alias, or other identifying sign, under each use. You can not use the work for commercial purposes.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели. Споделяне на споделеното.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use. Share of shared.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Не можете да използвате произведението за търговска цел. Когато променяте произведението и създавате производни и сборни произведения, можете да ги разпространявате само при същите условия, при които сте получили оригиналното произведение за повторно използване.',
                    'en'    => 'You may use, reproduce, distribute and modify the work and create derivative works and assembly works without the consent of a third party, but under condition you designate the name, alias, or other identifying sign, under each use. You can not use the work for commercial purposes. When you change the work and create derivative works and assembly works, you can distribute them only under the same conditions as when you received the original work for re-use.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели. Без производни и сборни произведения.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use. Without derivatives and pooled works.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате и разпространявате произведението, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Нямате право да използвате произведението за търговска цел. Нямате право да разпространявате производни и сборни произведения.',
                    'en'    => 'You may use, reproduce and distribute the work without the consent of a third party, but under condition you designate the name, alias, or other identifying sign, under each use. You may not use the work for commercial purposes. You may not distribute derivative works and assembly works.',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Ограничения по отношение на отделни части на информацията, съдържащи производствена или търговска тайна.',
                    'en'    => 'Restrictions on individual parts of information containing a manufacturing or commercial secret.',
                ],
                'descript'      => [
                    'bg'    => 'Ограничения по отношение на отделни части на информацията, съдържащи производствена или търговска тайна.',
                    'en'    => 'Restrictions on individual parts of information containing a manufacturing or commercial secret.',
                ],
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            foreach ($this->termsOfUse as $i => $termData) {
                $termData['active'] = true;
                $termData['is_default'] = isset($termData['is_default']) ? $termData['is_default'] : false;
                $termData['ordering'] = $i + 1;

                $alreadySaved = DB::table('translations')
                    ->where('label', $termData['name'])
                    ->join('terms_of_use', 'group_id', '=', 'terms_of_use.name')
                    ->first();

                if (!TermsOfUse::where($termData)->count() && !$alreadySaved) {
                    TermsOfUse::create($termData);
                }
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
        if (!env('IS_TOOL')) {
            foreach ($this->termsOfUse as $termData) {
                $deleteTerm = DB::table('terms_of_use')
                    ->select('terms_of_use.id as id')
                    ->join('translations', 'translations.group_id', '=', 'terms_of_use.name')
                    ->where('translations.label', $termData['name'])
                    ->first();

                if ($term = TermsOfUse::find($deleteTerm->id)) {
                    $term->delete();
                }
            }
        }
    }
}
