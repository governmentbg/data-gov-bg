@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 p-lg">
        <div>
            <p class="request-data">
                Споделете какви данни бихте желали да видите, публикувани на държавния портал за отворени данни - https://opendata.government.bg/
                Бихте могли да дадете както конкретен пример за специфична информация, която съществува, но може би я няма в
                структуриран, машинно-четим и удобен за автоматизирана обработка вид, така и да споделите необходимостта да виждате
                данни, които не намирате никъде, но които смятате, че трябва да бъдат публични.<br>
                Можете да попълните формуляра повече от веднъж, ако искате да заявите повече от един набор данни.
                Обърнете внимание, че това е проучване, което ще се използва с информативна цел, за да се постави приоритет на
                публикуването на определени набори от данни и не дава гаранция, че ще е възможно заявеният набор от данни да бъде публикувам.
            </p>
        </div>
        <form>
            <div class="m-t-lg">
                <span class="required">Задължително *</span>
                <h4>Кратко описание на данните&nbsp;<span class="required">*</span></h4>
                <span class="info">Опишете с няколко думи информацията, която бихте искаи да е публикувана в портала.</span>
                <textarea class="input-border-r-12 input-long" name="description"></textarea>
            </div>
            <div class="m-t-lg">
                <h4>Интернет адрес, където са публикувани данните<span class="info">(по желание)</span></h4>
                <span class="info">Ако данните ги има публикувани някъде, какъв е интернет адресът им.</span>
                <input type="text"  class="input-border-r-12 input-long" name="url">
            </div>
            <div class="m-t-lg">
                <h4>Вашето име или името на организацията, която представлявате<span class="info">(по желание)</span></h4>
                <span class="info">
                    Това проучване е анонимно. Въпреки това,
                    бихте могли да оставите данни за връзка,
                    за да може екипът ни да се свърже с Вас в
                    случай на нужда от допълнителни въпроси.
                </span>
                <input type="text"  class="input-border-r-12 input-long" name="name">
            </div>
            <div class="m-t-lg">
                <h4>Имейл адрес за връзка<span class="info">(по желание)</span></h4>
                <span class="info">
                    Това проучване е анонимно. Въпреки това,
                    бихте могли да оставите данни за връзка,
                    за да може екипът ни да се свърже с Вас в
                    случай на нужда от допълнителни въпроси.
                </span>
                <input type="text"  class="input-border-r-12 input-long" name="user-email">
            </div>
            <div class="m-t-lg">
                <h4>Бележки.<span class="info">(по желание)</span></h4>
                <span class="info">
                    Можете да оставите други бележки, коментари или
                    препоръки в свободен текст в това поле.
                </span>
                <input class="input-border-r-12 input-long" name="notes">
            </div>
            <div class="m-t-lg">
                <h4>E-mail на организацията приемаща заявката</h4>
                <input type="text" class="input-border-r-12 input-long" name="org-email">
            </div>
            <div class="m-t-lg text-right">
                <button type=submit" class="btn badge badge-pill">Изпрати</button>
            </div>
        </form>
    </div>
</div>
@endsection
