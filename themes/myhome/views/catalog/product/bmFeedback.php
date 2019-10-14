<?php $this->pageTitle = 'Отзывы — ' . $model->name . ' — ТВК «Большая Медведица» — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
                cat.ComentPartToggler();
        });
', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-content">
<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
	'pageName' => $model->name,
	'mallCatalogClass' => true,
	'afterH1'  => '', //$this->renderPartial('//widget/bmLogo', array(), true),
)); ?>


<div class="product_card">

        <?php $this->renderPartial('_bmMenuBlock', array('model'=>$model)); ?>


        <div class="buyers_opinion_header">

        <?php $this->widget('application.components.widgets.WStar', array(
                'selectedStar' => $model->average_rating,
                'addSpanClass' => 'rating-b',
                'votesQt'=>Feedback::model()->count('product_id=:pid', array(':pid'=>$model->id)),
        ));?>

        <span class="add_comment"><i></i><a href="#product_comment_form">Оставьте свой отзыв</a></span>
</div>

<div class="page_settings new">
        <?php $this->widget('catalog.components.widgets.CatFilterSort', array(
                'cookieName'=>'product_feedback_sort',
                'formSelector'=>'#update-form',
                'items'=>array(
                        array('name'=>'date', 'text'=>'дате'),
                        array('name'=>'mark', 'text'=>'оценке'),
                ),
        )); ?>


        <div class="pages">
            <?php $this->widget('application.components.widgets.CustomPager2', array(
                    'pages' => $feedbacks->pagination,
                    'maxButtonCount' => 5,
                )); ?>
        </div>

        <div class="elements_on_page drop_down">
                На странице <span class="exp_current"><?php echo $pagesize; ?><i></i></span>
                <ul class="need_submit">
                        <?php foreach(Config::$productFeedbackPageSizes as $key=>$item) : ?>
                        <?php echo CHtml::tag('li', array('data-value'=>$key), $item); ?>
                        <?php endforeach; ?>
                </ul>
        </div>

        <div class="clear"></div>
</div>
<div class="buyers_opinion comment_page">
        <div class="product_comments ">

                <?php $this->widget('zii.widgets.CListView', array(
                        'dataProvider'=>$feedbacks,
                        'itemView'=>'_feedbackItem',
                        'template'=>'{items}',
                        'emptyText'=>'Пока нет ни одного отзыва. Вы можете стать первым!',
                ));?>

        </div>
</div>

<div class="page_settings bottom new">

        <div class="pages">
            <?php $this->widget('application.components.widgets.CustomPager2', array(
                    'pages' => $feedbacks->pagination,
                    'maxButtonCount' => 5,
                )); ?>
        </div>

        <div class="elements_on_page drop_down">
                На странице <span class="exp_current"><?php echo $pagesize; ?><i></i></span>
                <ul>
                        <?php foreach(Config::$productFeedbackPageSizes as $key=>$item) : ?>
                                <?php echo CHtml::tag('li', array('data-value'=>$key), $item); ?>
                        <?php endforeach; ?>
                </ul>
        </div>


        <div class="clear"></div>
</div>

<div class="spacer-18"></div>

        <div class="comments" id="product_comment_form" >

                <?php if(!Yii::app()->user->isGuest && !$model->checkFeedback) : ?>

                        <?php $form=$this->beginWidget('CActiveForm', array(
                                'htmlOptions'=>array('class'=>'shadow_block white padding-18', 'id'=>'form-feedback')
                        )); ?>

                        <div class="form_top"></div>
                        <p>
                                <?php echo $form->label($feedback, 'merits'); ?>
                                <?php echo $form->textArea($feedback, 'merits', array('id'=>'lc-merits', 'class'=>'textInput')); ?>
                        </p>
                        <p>
                                <?php echo $form->label($feedback, 'limitations'); ?>
                                <?php echo $form->textArea($feedback, 'limitations', array('id'=>'lc-merits', 'class'=>'textInput')); ?>
                        </p>
                        <p>
                                <?php echo $form->label($feedback, 'message'); ?>
                                <?php echo $form->textArea($feedback, 'message', array('id'=>'lc-merits', 'class'=>'textInput')); ?>
                        </p>

                        <p>
                                <?php echo $form->label($feedback, 'mark'); ?>
                                <span class="rating-leave"><i></i><i></i><i></i><i></i><i></i>
                                        <?php echo $form->hiddenField($feedback, 'mark', array('id'=>'idea-rating')); ?>
                                </span>
                        </p>

                        <p class="submit">
                                <?php echo CHtml::button('Опубликовать', array('id'=>'post-feedback', 'class'=>'btn_grey')); ?>
                        </p>

                        <p class="error-title" style="display: none;"></p>

                        <?php $this->endWidget(); ?>

                <?php endif; ?>


                <?php if(Yii::app()->user->isGuest) : ?>
                        <form class="shadow_block white padding-18">
                                <p class="lc-not">Чтобы оставить отзыв о товаре, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
                        </form>
                <?php endif; ?>

                <?php if($model->checkFeedback) $showAlreadyFeedback = 'block'; else $showAlreadyFeedback = 'none'?>

                <form class="shadow_block white padding-18" style="display: <?php echo $showAlreadyFeedback; ?>;" id="alreadyFeedback">
                        <p class="lc-not">Вы оставили отзыв для этого товара</p>
                </form>


        </div>



<div class="spacer-30"></div>
<div class="clear"></div>

<form method="get" action="<?php echo $this->createUrl('/product', array('id'=>$model->id, 'action'=>'feedback')); ?>" id="update-form">

</form>

<?php Yii::app()->clientScript->registerScript('feedback-form', '
        $("#post-feedback").click(function(){
                $.post("'.$this->createUrl("createFeedback", array("id"=>$model->id)).'", $("#form-feedback").serialize(), function(response){

                        response = $.parseJSON(response);

                        if(response.success) {
                                $("#form-feedback").hide();
                                $("#alreadyFeedback").show();
                                $("#update-form").submit();
                        } else {
                                var errors = "";
                                $(".error-title").show();
                                $.each(response.errors, function(index, value) {
                                        errors = errors + value + "<br>";
                                })
                                $(".error-title").html(errors);
                        }
                });
        });

        $(".drop_down ul li").click(function(){
                CCommon.setCookie("product_feedback_pagesize", $(this).attr("data-value"), {expires:31*24*60*60, path:"/"});
                $("#update-form").submit();
        });

', CClientScript::POS_END);?>
</div>