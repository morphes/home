<div class="portfolio_head messages_page">
    <div class="menu_level2">
        <ul>
            <li <?php echo ($current == 'inbox') ? 'class="current"' : ''; ?>><?php echo CHtml::link('Входящие', $this->createUrl($this->id . '/inbox')); ?></li>
            <li <?php echo ($current == 'outbox') ? 'class="current"' : ''; ?>><?php echo CHtml::link('Исходящие', $this->createUrl($this->id . '/outbox')); ?></li>
        </ul>
    </div>
    <form class="message_search" action="<?php echo $this->createUrl('search'); ?>">
        <input type="text" class="textInput textInput-placeholder"
               value="<?php echo Yii::app()->request->getParam('qsearch'); ?>"
               placeholder="Поиск по сообщениям"
               name="qsearch">
        <input type="image" src="/img/search.png"/>
    </form>
    <div class="btn_conteiner">
        <a id="new_message" class="btn_grey" href="#"><i class="mail"></i>Написать сообщение</a>
    </div>

    <div class="clear"></div>
</div>

<?php if (Yii::app()->user->hasFlash('msg_success')): ?>
    <div class="good-title">
        <?php echo Yii::app()->user->getFlash('msg_success'); ?>
    </div>
<?php endif; ?>

<?php if (Yii::app()->user->hasFlash('msg_fail')): ?>
    <div class="error-title" style="display: block;">
        <?php echo Yii::app()->user->getFlash('msg_fail'); ?>
    </div>
<?php endif; ?>