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
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
                'is_default'    => true,
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyright.',
                ],
                'descript'      => [
                    'bg'    => 'https://www.nhif.bg/get_file?uuid=dc42b865-ec11-4475-b3ca-3e543c64f8bb',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Споделяне на споделеното.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Share of shared.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, включително с търговска цел, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Когато променяте произведението и създавате производни и сборни произведения, можете да ги разпространявате само при същите условия, при които сте получили оригиналното произведение за повторно използване.", ако се прилагат условията по ал. 1, т. 1.',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без производни и сборни произведения.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Without derivatives and pooled works.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате и разпространявате произведението, включително с търговска цел, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Нямате право да разпространявате производни и сборни произведения.", ако се прилагат условията по ал. 1, т. 2.',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Не можете да използвате произведението за търговска цел.", ако се прилагат условията по ал. 1, т. 3.',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели. Споделяне на споделеното.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use. Share of shared.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате, разпространявате и променяте произведението и да създавате производни и сборни произведения, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Не можете да използвате произведението за търговска цел. Когато променяте произведението и създавате производни и сборни произведения, можете да ги разпространявате само при същите условия, при които сте получили оригиналното произведение за повторно използване.", ако се прилагат условията по ал. 1, т. 4.',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Без употреба за търговски цели. Без производни и сборни произведения.',
                    'en'    => 'Terms of use of a work for re-use. Recognition of copyrights. Not for commercial use. Without derivatives and pooled works.',
                ],
                'descript'      => [
                    'bg'    => 'Можете да използвате, възпроизвеждате и разпространявате произведението, без да е нужно съгласието на трето лице, но при условие че посочите името, псевдонима или друг идентифициращ автора знак при всяко използване. Нямате право да използвате произведението за търговска цел. Нямате право да разпространявате производни и сборни произведения.", ако се прилагат условията по ал. 1, т. 5.',
                    'en'    => 'http://opendefinition.org/licenses/',
                ],
            ],
            [
                'name'          => [
                    'bg'    => 'Ограничения по отношение на отделни части на информацията, съдържащи производствена или търговска тайна.',
                    'en'    => 'Restrictions on individual parts of information containing a manufacturing or commercial secret.',
                ],
                'descript'      => [
                    'bg'    => 'https://www.nhif.bg/get_file?uuid=dc42b865-ec11-4475-b3ca-3e543c64f8bb',
                    'en'    => 'http://opendefinition.org/licenses/',
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->termsOfUse as $termData) {
            $deleteTerm = DB::table('terms_of_use')->select('terms_of_use.id as id')
                ->join('translations', 'translations.group_id', '=', 'terms_of_use.name')
                ->where('translations.label', $termData['name'])
                ->first();

            if ($term = TermsOfUse::find($deleteTerm->id)) {
                $term->delete();
            }
        }
    }
}
