<?php

use App\Locale;
use App\Organisation;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Http\Controllers\ApiController;

class OrganisationSeeder extends Seeder
{
    const ORGANISATION_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $types = array_keys(Organisation::getTypes());

        // Test creation
        foreach (range(1, self::ORGANISATION_RECORDS) as $i) {
            $type = $this->faker->randomElement($types);
            $locale = $this->faker->randomElement($locales)['locale'];
            \LaravelLocalization::setLocale($locale);

            $parentId = empty($parentId) && empty($record) || $type == Organisation::TYPE_GROUP ? null : $record->id;

            $logo = $this->faker->imageUrl();

            try {
                $img = \Image::make($logo);
            } catch (NotReadableException $ex) {}

            $dbData = [
                'type'              => $type,
                'name'              => ApiController::trans($locale, $this->faker->name),
                'descript'          => ApiController::trans($locale, $this->faker->text(intval(8000))),
                'uri'               => $this->faker->uuid(),
                'parent_org_id'     => is_null($parentId) ? null : $parentId,
                'active'            => $this->faker->boolean(),
                'approved'          => $this->faker->boolean(),
            ];

            if (!empty($img)) {
                $logoData['logo_file_name'] = basename($logo);
                $logoData['logo_mime_type'] = $img->mime();

                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                $img->save($path);
                $logoData['logo_data'] = file_get_contents($path);

                fclose($temp);

                $dbData = array_merge($dbData, $logoData);
            }

            if ($i != 1) {
                $dbData = array_merge($dbData, [
                    'activity_info'     => $this->faker->text(intval(8000)),
                    'contacts'          => $this->faker->text(intval(100)),
                ]);
            }

            Organisation::create($dbData)->searchable();
        }
    }
}
