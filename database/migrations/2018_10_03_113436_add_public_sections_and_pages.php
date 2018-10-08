<?php

use App\Page;
use App\User;
use App\Section;
use App\Translation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\Api\ThemeController;

class AddPublicSectionsAndPages extends Migration
{
    public $sections = [];

    public function __construct()
    {
        $this->sections = [
            [
                'name'     => ['bg' => 'Визуализации', 'en' => 'Visualisations'],
                'ordering' => 1,
                'active'   => 1,
                'theme'    => ThemeController::THEME_ORANGE,
                'page'     => [
                    'title'      => ['bg' => 'Визуализации', 'en' => 'Visualisations'],
                    'section_id' => '',
                    'body'       => [
                        'bg' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Adipiscing commodo elit at imperdiet. Nec nam aliquam sem et tortor consequat. Placerat duis ultricies lacus sed turpis tincidunt id. Eu mi bibendum neque egestas congue quisque egestas diam. Hac habitasse platea dictumst quisque sagittis purus sit. Tristique senectus et netus et malesuada fames. Maecenas sed enim ut sem viverra aliquet eget sit. Scelerisque viverra mauris in aliquam sem fringilla. Nullam ac tortor vitae purus faucibus ornare suspendisse. Adipiscing elit duis tristique sollicitudin. Ornare arcu dui vivamus arcu felis bibendum ut. Sit amet risus nullam eget felis eget nunc. Lobortis elementum nibh tellus molestie nunc non blandit massa enim. Augue eget arcu dictum varius duis. Viverra accumsan in nisl nisi scelerisque. Etiam erat velit scelerisque in dictum non. Pulvinar mattis nunc sed blandit.'
                    ],
                    'active'     => 1,
                    'type'       => Page::TYPE_PAGE,
                ],
            ],
            [
                'name'     => ['bg' => 'Контакти', 'en' => 'Contacts'],
                'ordering' => 2,
                'active'   => 1,
                'theme'    => ThemeController::THEME_PURPLE,
                'page'     => [
                    'title'      => ['bg' => 'Контакти', 'en' => 'Contacts'],
                    'section_id' => '',
                    'body'       => [
                        'bg' => 'Споделете какви данни бихте желали да видите, публикувани на държавния портал за отворени данни.
                        Бихте могли да споделите необходимостта да виждате данни, които не намирате никъде, но които смятате, че трябва да бъдат публични.

                        Обърнете внимание, че това е проучване, което ще се използва с информативна цел, за да се постави приоритет на публикуването на определени набори от данни и не дава гаранция, че ще е възможно заявеният набор от данни да бъде публикуван.

                        За контакт

                        Нуша Иванова
                        дирекция „Модернизация на администрацията“
                        в Администрацията на Министерски съвет

                        тел. 02/940 2445
                        e-mail: ivanova@bgpost.org'
                    ],
                    'active'     => 1,
                    'type'       => Page::TYPE_PAGE,
                ],
            ],
            [
                'name'     => ['bg' => 'Условия за ползване', 'en' => 'Terms & conditions'],
                'ordering' => 1,
                'active'   => 1,
                'theme'    => ThemeController::THEME_LIGHT_BLUE,
                'page'     => [
                    'title'      => ['bg' => 'Условия за ползване', 'en' => 'Terms & conditions'],
                    'section_id' => '',
                    'body'       => [
                        'bg' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Adipiscing commodo elit at imperdiet. Nec nam aliquam sem et tortor consequat. Placerat duis ultricies lacus sed turpis tincidunt id. Eu mi bibendum neque egestas congue quisque egestas diam. Hac habitasse platea dictumst quisque sagittis purus sit. Tristique senectus et netus et malesuada fames. Maecenas sed enim ut sem viverra aliquet eget sit. Scelerisque viverra mauris in aliquam sem fringilla. Nullam ac tortor vitae purus faucibus ornare suspendisse. Adipiscing elit duis tristique sollicitudin. Ornare arcu dui vivamus arcu felis bibendum ut. Sit amet risus nullam eget felis eget nunc. Lobortis elementum nibh tellus molestie nunc non blandit massa enim. Augue eget arcu dictum varius duis. Viverra accumsan in nisl nisi scelerisque. Etiam erat velit scelerisque in dictum non. Pulvinar mattis nunc sed blandit.

                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Adipiscing commodo elit at imperdiet. Nec nam aliquam sem et tortor consequat. Placerat duis ultricies lacus sed turpis tincidunt id. Eu mi bibendum neque egestas congue quisque egestas diam. Hac habitasse platea dictumst quisque sagittis purus sit. Tristique senectus et netus et malesuada fames. Maecenas sed enim ut sem viverra aliquet eget sit. Scelerisque viverra mauris in aliquam sem fringilla. Nullam ac tortor vitae purus faucibus ornare suspendisse. Adipiscing elit duis tristique sollicitudin. Ornare arcu dui vivamus arcu felis bibendum ut. Sit amet risus nullam eget felis eget nunc. Lobortis elementum nibh tellus molestie nunc non blandit massa enim. Augue eget arcu dictum varius duis. Viverra accumsan in nisl nisi scelerisque. Etiam erat velit scelerisque in dictum non. Pulvinar mattis nunc sed blandit.

                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Adipiscing commodo elit at imperdiet. Nec nam aliquam sem et tortor consequat. Placerat duis ultricies lacus sed turpis tincidunt id. Eu mi bibendum neque egestas congue quisque egestas diam. Hac habitasse platea dictumst quisque sagittis purus sit. Tristique senectus et netus et malesuada fames. Maecenas sed enim ut sem viverra aliquet eget sit. Scelerisque viverra mauris in aliquam sem fringilla. Nullam ac tortor vitae purus faucibus ornare suspendisse. Adipiscing elit duis tristique sollicitudin. Ornare arcu dui vivamus arcu felis bibendum ut. Sit amet risus nullam eget felis eget nunc. Lobortis elementum nibh tellus molestie nunc non blandit massa enim. Augue eget arcu dictum varius duis. Viverra accumsan in nisl nisi scelerisque. Etiam erat velit scelerisque in dictum non. Pulvinar mattis nunc sed blandit.

                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Adipiscing commodo elit at imperdiet. Nec nam aliquam sem et tortor consequat. Placerat duis ultricies lacus sed turpis tincidunt id. Eu mi bibendum neque egestas congue quisque egestas diam. Hac habitasse platea dictumst quisque sagittis purus sit. Tristique senectus et netus et malesuada fames. Maecenas sed enim ut sem viverra aliquet eget sit. Scelerisque viverra mauris in aliquam sem fringilla. Nullam ac tortor vitae purus faucibus ornare suspendisse. Adipiscing elit duis tristique sollicitudin. Ornare arcu dui vivamus arcu felis bibendum ut. Sit amet risus nullam eget felis eget nunc. Lobortis elementum nibh tellus molestie nunc non blandit massa enim. Augue eget arcu dictum varius duis. Viverra accumsan in nisl nisi scelerisque. Etiam erat velit scelerisque in dictum non. Pulvinar mattis nunc sed blandit.'
                    ],
                    'active'     => 1,
                    'type'       => Page::TYPE_PAGE,
                ],
            ],
            [
                'name'     => ['bg' => 'Достъпност', 'en' => 'Accessibility'],
                'ordering' => 2,
                'active'   => 1,
                'theme'    => ThemeController::THEME_RED,
                'page'     => [
                    'title'      => ['bg' => 'Достъпност', 'en' => 'Accessibility'],
                    'section_id' => '',
                    'body'       => [
                        'bg' => 'Администрацията на Министерския съвет се ангажира да осигури достъпност на този портал за максимално широка група потребители независимо от използваните технологии и умения. Ние активно работим за подобряване на достъпността и използваемостта на този портал и в тази си дейност се придържаме към наличните стандарти и добри практики.

                        За да отговорим на поставените изисквания и критерии за ниво на достъпност, поставени от Европейската комисия относно интернет страниците на публичната администрация, и същевременно да посрещнем изискванията на текущите интернет технологии, ще се придържаме към стандартите на WCAG 2.0.

                        Инициатива за уеб достъпност (Web Accessibility Initiative)

                        Този портал ще се опитва да покрие ниво на достъпност според последните стандарти на World Wide Web Consortium - Web Content Accessibility Guidelines 2.0 (WCAG 2.0), като използва най-добрите практики и техники.

                        WCAG 2.0 обяснява как съдържанието в интернет страниците да бъде направено по-достъпно за хора с увреждания. Изпълнението на насоките ще помогне на повече хора да се чувстват удобно и да се възползват от предимствата на интернет.

                        W3C Стандарти

                        Този портал е изграден с код съвместим с W3C стандартите за XHTML и CSS. Той се възпроизвежда коректно в наличните към момента интернет браузъри и използването му гарантира съвместимост и с бъдещите нови или подобрени браузъри.

                        Изключения

                        Макар да се стремим да осигурим съвместимост с наличните стандарти за достъпност, това не винаги е възможно във всеки един аспект. Браузърите и техниките за разработка и достъп до интернет се развиват много бързо и понякога някои от стандартните изисквания за достъпност стават излишни.

                        В тази светлина ние непрекъснато развиваме технологично този портал с оглед постигането на високи стандарти на достъпност и използваемост на съдържанието в него.'
                    ],
                    'active'     => 1,
                    'type'       => Page::TYPE_PAGE,
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
            $userId = User::where('username', 'system')->value('id');

            foreach ($this->sections as $key => $section) {
                DB::beginTransaction();

                try {
                    $newSection = new Section;

                    $newSection->name = $section['name'];
                    $newSection->ordering = $section['ordering'];
                    $newSection->active = $section['active'];
                    $newSection->theme = $section['theme'];
                    $newSection->created_by = $userId;

                    $newSection->save();

                    $newPage = new Page;

                    $newPage->title = $section['page']['title'];
                    $newPage->section_id = $newSection->id;
                    $newPage->body = $section['page']['body'];
                    $newPage->active = $section['page']['active'];
                    $newPage->type = $section['page']['type'];
                    $newPage->created_by = $userId;

                    $newPage->save();

                    DB::commit();
                } catch (QueryException $e) {
                    DB::rollback();
                    Log::error($e->getMessage());
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
            $userId = User::where('username', 'system')->value('id');

            foreach ($this->sections as $key => $section) {
                $pageTrans = DB::table('translations')
                    ->where('label', $section['page']['title']['bg'])
                    ->where('locale', 'bg')
                    ->where('created_by', $userId)
                    ->get()
                    ->last();

                $pageGroupId = !is_null($pageTrans) ? $pageTrans->group_id : null;

                DB::table('translations')
                    ->where('group_id', $pageGroupId)
                    ->delete();

                $secTrans = DB::table('translations')
                    ->where('label', $section['name']['bg'])
                    ->where('locale', 'bg')
                    ->where('created_by', $userId)
                    ->get()
                    ->last();

                $secGroupId = !is_null($secTrans) ? $secTrans->group_id : null;

                DB::table('translations')
                    ->where('group_id', $secGroupId)
                    ->delete();

                Page::where('title', $pageGroupId)
                    ->where('created_by', $userId)
                    ->delete();

                Section::where('name', $secGroupId)
                    ->where('created_by', $userId)
                    ->delete();
            }
        }
    }
}
