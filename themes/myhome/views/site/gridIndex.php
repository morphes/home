<?php
Yii::import('application.modules.idea.models.Idea');
Yii::import('application.modules.idea.models.Interior');
Yii::import('application.modules.media.models.MediaKnowledge');
Yii::import('application.modules.media.models.MediaNew');
Yii::import('application.modules.admin.models.IndexIdeaLink');
Yii::import('application.modules.admin.models.IndexIdeaPhoto');
Yii::import('application.modules.admin.models.IndexSpecPhoto');
Yii::import('application.modules.admin.models.IndexSpecBlock');
Yii::import('application.modules.member.models.LikeItem');

/** @var $cs CustomClientScript */
$cs = Yii::app()->getClientScript();

$cs->registerCssFile('/css-new/generated/styles.css');
$cs->registerCssFile('/css-new/generated/index.css');

$cs->registerScriptFile('/js-new/jQuery.BlackAndWhite.min.js');
$cs->registerScriptFile('/js-new/jquery.popup.carousel.js');
?>

<?php $this->pageTitle = 'MyHome.ru — портал для поиска товаров, идей и специалистов в области ремонта и благоустройства дома' ?>
<?php $this->description = 'Ваш интернет-помощник на всех этапах создания домашнего уюта — от формирования образа дома до воплощения задуманного'; ?>
<?php $this->keywords = ''; ?>

<!-- spec promo -->
<div class="-col-12 spec-promo">
    <h1 style="margin-top: 30px">
        <?php
        // Заголовок блока ДИЗАЙНЕРОВ
        $this->widget('application.components.widgets.WIncludes', array('key' => 'main_peoples'));

        //количество дизайнеров с учетом склонения
        $countDesigner = 0;
        $countDesigner = (int)User::getSpecialistsQuantity(null, false, false);
        //получаем склонение с учетом числа
        $designerWordDeclension = CFormatterEx::formatNumeral($countDesigner, array('человек', 'человека', 'человек'), true);
        ?>

        <div class="-drop-right"><a
                    href="/specialist"><?php echo number_format($countDesigner, 0, '.', ' '); ?><?php echo $designerWordDeclension; ?> </a><span></span>
        </div>
    </h1>
    <div class="-grid">

        <?php // Блок специалистов
        $spec = IndexSpecBlock::getSpecBlocks();
        ?>

        <?php if (
            count($spec) == 3
            &&
            (count($spec[0]['photos']) + count($spec[1]['photos']) + count($spec[2]['photos'])) >= 12
        ) { ?>

            <?php
            $htmlOut = '';
            foreach ($spec as $s) {
                $htmlOut .= '<div class="-col-3">';
                $htmlOut .= '<a class="-block -huge -strong -em-bottom" href="' . $s['block_url'] . '">' . $s['block_name'] . '</a>';

                for ($i = 0; $i < 4; $i++) {
                    if (!isset($s['photos'][$i])) {
                        continue;
                    }
                    $item = $s['photos'][$i];
                    $htmlOut .= '<a class="spec-thumb" href="' . $item['user_url'] . '"><img src="http://www.myhome.ru' . $item['user_img'] . '"></a>';
                }
                $htmlOut .= '</div>';
            }

            echo $htmlOut;
            ?>

        <?php } else { ?>

            <!-- col 1 -->
            <div class="-col-3">
                <a class="-block -huge -strong -em-bottom" href="/specialist/interior-design">Дизайнеры интерьера</a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/mashyny_m"><img
                            src="/img-new/tmp/index/spec/1.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/Melnich_vlad"><img
                            src="/img-new/tmp/index/spec/2.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/elachin"><img
                            src="/img-new/tmp/index/spec/3.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/dizart"><img src="/img-new/tmp/index/spec/4.jpg"></a>
            </div>
            <!-- eof col 1 -->
            <!-- col 2 -->
            <div class="-col-3">
                <a class="-block -huge -strong -em-bottom" href="/specialist/architectural-design">Архитекторы</a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/georgboyko"><img
                            src="/img-new/tmp/index/spec/5.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/Melexx"><img src="/img-new/tmp/index/spec/6.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/id"><img src="/img-new/tmp/index/spec/7.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/AlexZA"><img src="/img-new/tmp/index/spec/8.jpg"></a>
            </div>
            <!-- eof col 2 -->
            <!-- col 3 -->
            <div class="-col-3">
                <a class="-block -huge -strong -em-bottom" href="/specialist">Прорабы и мастера</a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/remcom"><img src="/img-new/tmp/index/spec/9.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/Santey"><img
                            src="/img-new/tmp/index/spec/10.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/rso2000"><img
                            src="/img-new/tmp/index/spec/11.jpg"></a>
                <a class="spec-thumb" href="http://www.myhome.ru/users/dekor75"><img
                            src="/img-new/tmp/index/spec/12.jpg"></a>
            </div>
            <!-- eof col 3 -->

        <?php } ?>

        <!-- col 4 -->
        <div class="-col-3">
            <a class="-block -huge -strong -red -pointer-right -em-bottom" href="/specialist">и многие другие</a>
            <div>
                <span class="-block -gutter-bottom-hf">Вы можете самостоятельно</span>
                <a class="-button -button-expanded -button-skyblue" href="/specialist">Найти специалиста</a>
                <span class="-block -gutter-bottom-hf -gutter-top-hf">или</span>
                <a class="-button -button-expanded -button-skyblue" href="/tenders/list">Разместить заказ</a>
                <span class="-block -gutter-top-hf">чтобы специалист нашел вас</span>
            </div>
        </div>
        <!-- eof col 4 -->
    </div>
</div>
<!-- eof spec promo -->

<!--ideas promo -->
<div class="-col-12 ideas-promo">
    <h1 class="-gutter-null">
        <?php // Заголовок блока ИДЕЙ
        $this->widget('application.components.widgets.WIncludes', array('key' => 'main_ideas')); ?>

        <div class="-drop-left"><a href="/idea"><?php echo Idea::getIdeasPhotoQuantity(false, true); ?>
                фото</a><span></span></div>
    </h1>
    <!-- ideas-menu (ajax) -->

    <?php $links = IndexIdeaLink::getLinks(); ?>
    <ul class="-menu-inline -tab-menu -justified-menu -huge ideas-menu">
        <?php
        if (!empty($links) && count($links) >= 6) {
            $htmlOut = '';

            for ($i = 0, $ci = count($links); $i < $ci; $i++) {

                $link = $links[$i];

                if ($i < $ci - 1) {
                    $htmlOut .= '<li>' . CHtml::link($link['name'], $link['url']) . '</li>';
                    // Разделяем теги пробелом, чтобы растянуть на всю ширину.
                    $htmlOut .= ' ';
                } else {
                    $htmlOut .= '<li>' . CHtml::link($link['name'], $link['url'])
                        . '&nbsp;&nbsp;и&nbsp;&nbsp;'
                        . '<a href="/idea" class="-pointer-right -gutter-right-null -red">многое другое</a></li>';
                }

            }
            echo $htmlOut;
        } else {
            ?>
            <li><a href="/idea/interior/kitchen">Кухни</a></li>
            <li><a href="/idea/interior/bedroom">Спальни</a></li>
            <li><a href="/idea/interior/livingroom">Гостиные</a></li>
            <li><a href="/idea/interior/nursery">Детские</a></li>
            <li><a href="/idea/interior/bathroom">Ванные</a></li>
            <li><a href="/idea/catalog/index?ideatype=2&object_type=118">Дома, коттеджи</a>&nbsp;&nbsp;и&nbsp;&nbsp;<a
                        href="/idea" class="-pointer-right -gutter-right-null -red">многое другое</a></li>
            <?php
        }
        ?>

    </ul>
    <!-- eof ideas-menu -->    <!-- ideas thumbnails -->

    <?php /** @var $ideas IndexIdeaPhoto[] */
    $ideas = IndexSpecPhoto::getPhotos();
    ?>
    <div class="-grid ideas-thimbnails">

        <?php if (!empty($ideas) && count($ideas) == 6) { ?>

            <?php foreach ($ideas as $idea) { ?>
                <div class="-col-4 idea-thumb">
                    <a href="<?php echo $idea['idea_url']; ?>"><img src="http://www.myhome.ru/<?= $idea['img_src']; ?>"
                                                                    class="-rect-giant"></a>
                    <div>
                        <a class="-huge -semibold"
                           href="<?php echo $idea['idea_url']; ?>"><?php echo $idea['idea_name']; ?></a>

                        <?php if (array_key_exists($idea['author_role'], Config::$rolesAdmin)) { ?>

                            <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>

                        <?php } else { ?>

                            <span class="-small -gutter-top-hf">
								<a class="-gray" href="<?php echo $idea['author_url']; ?>"><img
                                            src="<?php echo $idea['author_img']; ?>"
                                            class="-quad-25 -push-left -gutter-right-hf"><?php echo $idea['author_name']; ?></a>
							</span>

                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

        <?php } else { ?>

            <!-- idea-thumb -->
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/10066"><img src="/img-new/tmp/index/1.jpg"
                                                                        class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/10066">Пентхаус в Лондоне</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>
            <!-- eof idea-thumb -->
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/9835"><img src="/img-new/tmp/index/2.jpg"
                                                                       class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/9835">Единое целое</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/9732"><img src="/img-new/tmp/index/3.jpg"
                                                                       class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/9732">Лофт для творческой
                        личности</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/10075"><img src="/img-new/tmp/index/4.jpg"
                                                                        class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/10075">Прекрасный дуплекс</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/10084"><img src="/img-new/tmp/index/5.jpg"
                                                                        class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/10084">Женский подход</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>
            <div class="-col-4 idea-thumb">
                <a href="http://www.myhome.ru/idea/interior/8966"><img src="/img-new/tmp/index/6.jpg"
                                                                       class="-rect-giant"></a>
                <div>
                    <a class="-huge -semibold" href="http://www.myhome.ru/idea/interior/8966">Элитная квартира</a>
                    <span class="-small -gutter-top-hf"><span class="-gray">Редакция MyHome</span></span>
                </div>
            </div>

        <?php } ?>


    </div>
    <!-- eof ideas thumbnails -->
</div>
<!-- eof ideas promo -->

<!-- media promo -->
<div class="-col-12 media-promo">
    <h1>
        <?php // Заголовок блока НОВОСТИ
        $this->widget('application.components.widgets.WIncludes', array('key' => 'main_news')); ?>
        <div class="-drop-left"><a href="/journal">Журнал myhome</a><span></span></div>
    </h1>
    <div class="-grid">
        <!-- title article -->
        <div class="-col-6">
            <div class="-relative">
                <?php // Выводим одну последнюю СТАТЬЮ
                /** @var $bigKnow MediaKnowledge */
                $bigKnow = MediaKnowledge::model()->published()->find();
                ?>
                <a href="<?php echo $bigKnow->getElementLink(); ?>"><img
                            src="http://www.myhome.ru/<?= $bigKnow->preview->getPreviewName(MediaKnowledge::$preview['crop_460x340']); ?>"
                            class="-rect-promo-medium"></a>
                <div class="thumb-overlay"><?php echo CHtml::link(
                        $bigKnow->title,
                        $bigKnow->getElementLink(),
                        array('class' => '-huge -semibold')
                    ); ?>
                    <span class="-block -small -gray -gutter-top-hf">

						<a class="-gutter-left-qr"
                           href="<?php echo $bigKnow->getSectionLink() . '?f_genre=' . $bigKnow->genre; ?>"><?php echo MediaKnowledge::$genreNames[$bigKnow->genre]; ?></a>
						<span class="-icon-eye-s -small -gray -gutter-left"><?php echo number_format($bigKnow->count_view, 0, '', ' '); ?></span>
						<span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($bigKnow), $bigKnow->id); ?></span>
					</span>
                </div>
            </div>
        </div>
        <!-- eof title article -->

        <!-- last articles list -->
        <div class="-col-3">
            <ul class="-menu-block">
                <?php // Выводим список последних НОВОСТЕЙ
                /** @var $news MediaNew */
                $news = MediaNew::model()->published()->findAll(array('limit' => 5));
                if (!empty($news)) {
                    /** @var $new MediaNew */
                    foreach ($news as $new) {
                        ?>
                        <li>
                            <a class="-relative" href="<?php echo $new->getElementLink(); ?>"><img
                                        src="<?php echo $new->preview->getPreviewName(MediaNew::$preview['crop_60']); ?>"
                                        class="-absolute -quad-60"><?php echo Amputate::getLimb($new->title, 30); ?></a>
                            <span class="-block -small -gray -inset-top-hf">

								<span class="-icon-eye-s -small -gray -gutter-left-hf"><?php echo number_format($new->count_view, 0, '', ' '); ?></span>
								<span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($new), $new->id); ?></span>
							</span>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <!-- eof last articles list -->

        <!-- banner -->
        <div class="-col-3 -relative">
            <?php $this->widget('application.components.widgets.banner.BannerWidget', array(
                'controller' => $this,
                'type' => 2
            )); ?>
        </div>
        <!-- eof banner -->
    </div>
</div>
<!-- eof media promo -->
<!-- expert promo -->
<div class="-col-12">
    <div class="-grid">
        <div class="-col-9">
            <div class="-gutter-right-hf -inset-all expert-promo">
                <h1 class="-gutter-bottom-hf -gutter-top-hf">
                    <?php // Заголовок блока ФОРУМ
                    $this->widget('application.components.widgets.WIncludes', array('key' => 'main_forum')); ?>
                </h1>
                <div class="-grid -gutter-null">
                    <div class="-col-wrap -gutter-right push-left">
                        <p class="-gray -gutter-bottom">
                            <?php // Вводная для блока ФОРУМ
                            $this->widget('application.components.widgets.WIncludes', array('key' => 'main_forum_lead')); ?>
                        </p>
                        <ul class="-menu-block -large">
                            <?php // --- ТЕМЫ ФОРУМА ---
                            Yii::import('application.modules.social.models.ForumTopic');
                            $lastTopics = ForumTopic::model()->findAll(array(
                                'condition' => 'status = :st',
                                'params' => array(':st' => ForumTopic::STATUS_PUBLIC),
                                'order' => 'create_time DESC',
                                'limit' => 4
                            ));
                            if ($lastTopics) {
                                foreach ($lastTopics as $topic) {
                                    echo '<li><a href="' . $topic->getElementLink() . '">' . $topic->name . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                        <a class="-button -button-skyblue -gutter-top" href="/forum/create">Задайте свой вопрос</a>
                    </div>
                    <div class="-col-wrap -gutter-left push-right">
                        <?php // --- ЭКСПЕРТЫ ---
                        $topExperts = User::model()->findAll(
                            array(
                                'select' => 'id',
                                'limit' => 100,
                                'condition' => 'status = :st AND (expert_type = :t1 OR expert_type = :t2)',
                                'params' => array(
                                    ':st' => User::STATUS_ACTIVE,
                                    ':t1' => User::EXPERT_TOP,
                                    ':t2' => User::EXPERT
                                )
                            )
                        );
                        shuffle($topExperts);
                        for ($i = 0; $i < 4; $i++) {
                            if (!isset($topExperts[$i])) {
                                break;
                            }
                            $exp = User::model()->findByPk($topExperts[$i]['id']);

                            echo '<a class="spec-thumb" href="' . Yii::app()->createUrl('users', array('login' => $exp->login)) . '">'
                                . '<img src="' . $exp->getPreview(User::$preview['crop_120']) . '">'
                                . '</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="-col-3">
            <div class="-gutter-bottom">
                <script type="text/javascript" src="//vk.com/js/api/openapi.js?87"></script>

                <!-- VK Widget -->
                <div id="vk_groups"></div>
                <script type="text/javascript">
                    VK.Widgets.Group("vk_groups", {mode: 1, width: "220", height: "153"}, 32251753);
                </script>
            </div>
            <div class="-gutter-bottom">
                <!--280648101946177-->

                <iframe src="//www.facebook.com/plugins/facepile.php?href=http%3A%2F%2Ffacebook.com%2Fmyhome.ru&amp;action&amp;size=small&amp;max_rows=2&amp;show_count=true&amp;width=220&amp;colorscheme=light"
                        scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:220px;"
                        allowTransparency="true"></iframe>


            </div>
        </div>
    </div>
</div>
<!-- eof expert promo -->