<?php

use App\Locale;
use App\Organisation;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

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

            $dbData = [
                'type'              => $type,
                'name'              => $this->faker->name,
                'descript'          => $this->faker->text(intval(8000)),
                'uri'               => $this->faker->uuid(),
                'logo_file_name'    => $i != 1 ? $this->faker->imageUrl() : null,
                'logo_mime_type'    => $i != 1 ? $this->faker->mimeType() : null,
                'logo_data'         => $i != 1 ? $this->faker->text(intval(8000)) : null,
                'parent_org_id'     => is_null($parentId) ? null : $parentId,
                'active'            => $this->faker->boolean(),
                'approved'          => $this->faker->boolean(),
            ];

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
