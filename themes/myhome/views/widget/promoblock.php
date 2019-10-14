<?php
if ($this->getRoute() != 'site/index')
	return;
?>

<div class="border_block gradient">
        <div class="tabs_block">
                <a class="current" href="#" id="show_1">Представить</a>.&nbsp;
                <a href="#" id="show_2">Выбрать</a>. &nbsp;
                <a href="#" id="show_3">Воплотить</a>.&nbsp;
        </div>
        <div class="tabs " id="tab_1">
                <img src="img/tab1.png"/>
                <div class="tab_head">Представьте новый образ своего дома</div>
                <div class="tab_text">Более <a href="/idea/interior/catalog">6000 идей</a> по дизайну интерьера с удобным поиском и большими фотографиями</div>
        </div>
        <div class="tabs hide" id="tab_2">
                <img src="img/tab2.png"/>
                <div class="tab_head">Выберите дизайнера вашей мечты</div>
                <div class="tab_text">Более <a href="/specialist/2">2000 лучших дизайнеров</a> интерьеров с портфолио и подробным резюме</div>
        </div>
        <div class="tabs hide" id="tab_3">
                <img src="img/tab3.png"/>
                <div class="tab_head">Воплотите свою идею с лучшими специалистами по ремонту</div>
                <div class="tab_text">Более <a href="/specialist">10 000 профессиональных прорабов и специалистов</a> по ремонту с подробным резюме и отзывами</div>
        </div>
</div>
<div class="spacer-18"></div>
<div class="profit border_block">
        <ul>
                <li class="first"><i></i>Презентация проекта</li>
                <li><a href="/files/myhome-dlya-vladeltsev-kvartir.pdf" target="_blank">Владельцам квартир</a></li>
                <li><a href="/files/myhome-dlya-proizvoditeley.pdf" target="_blank">Производителям</a></li>
                <li><a href="/files/myhome-dlya-prorabov.pdf" target="_blank">Прорабам и мастерам</a></li>
                <li><a href="/files/myhome-dlya-dizajnerov.pdf" target="_blank">Дизайнерам</a></li>
                <li><a href="/files/myhome-dlya-arhitektorov.pdf" target="_blank">Архитекторам</a></li>
                <li><a href="/files/myhome-dlya-smi.pdf" target="_blank">СМИ</a></li>
        </ul>
        <a class="button-green" href="/site/registration" onclick="_gaq.push(['_trackEvent','Registration','Gbutton']); yaCounter11382007.reachGoal('ygb'); return true;">
                Зарегистрироваться
                <b class="l"></b>
                <b class="r"></b>
        </a>
        <div class="clear"></div>
</div>
<div class="spacer-18"></div>
