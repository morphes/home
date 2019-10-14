<?php

/**
 * Для автокомплитов
 */
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');

$city = City::model()->findByPk($model->city_id);
?>

<div class="row">

        <?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
                'action'=>Yii::app()->createUrl($this->route),
                'method'=>'get',
        )); ?>

                <?php echo $form->textFieldRow($model,'id'); ?>

		<?php echo $form->dropDownListRow($model, 'tariff_id', array(0=>'') + Store::$tariffs); ?>

		<?php echo $form->dropDownListRow($model, 'type', array(0=>'Все') + Store::$types); ?>

		<?php echo $form->textFieldRow($model,'name'); ?>

		<div class="search-<?php echo Store::TYPE_ONLINE?>">
                	<?php echo $form->textFieldRow($model,'site'); ?>
		</div>

		<div class="search-<?php echo Store::TYPE_OFFLINE?>">
                	<?php echo $form->dropDownListRow($model, 'mall_build_id', array(0=>'') + CHtml::listData(MallBuild::model()->findAll(), 'id', 'name')); ?>
		</div>

		<?php /* // Автокомплит по авторам заменили на селект
                <div class="clearfix">
                        <?php echo CHtml::label($model->getAttributeLabel('user_id'), 'User_id'); ?>
                        <div class="input">
                                <?php
                                $user = User::model()->findByPk($model->user_id);
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'name'=>'User_id',
                                        'value'=> !is_null($user) ? $user->name : '',
                                        'sourceUrl'=>'/utility/autocompleteuser',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                                'select'=>'js:function(event, ui) {$("#Store_user_id").val(ui.item.id).keyup();}',
                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Store_user_id").val("");}}',
                                        ),
                                ));
                                ?>
                                <?php echo CHtml::activeHiddenField($model, 'user_id'); ?>
                        </div>
                </div>
 		*/?>

		<?php // Получаем список всех авторов, которые создавали магазины
		$authors = Yii::app()->dbcatalog2->createCommand("SELECT DISTINCT myhome.user.id as id, CONCAT(user.lastname, ' ', user.firstname) as name FROM myhome.user"
			."  INNER JOIN cat_store ON cat_store.user_id = myhome.user.id ORDER BY myhome.user.lastname ASC"
		)->setFetchMode(PDO::FETCH_KEY_PAIR)->queryAll();
		?>
		<?php echo $form->dropDownlistRow($model, 'user_id', array('' => '')+$authors); ?>


                <div class="clearfix">
                        <?php echo CHtml::label($model->getAttributeLabel('city_id'), 'City_id'); ?>
                        <div class="input">
				<?php

				$this->widget('application.components.widgets.EAutoComplete', array(
					'valueName'	=> !is_null($city) ? $city->name : '',
					'sourceUrl'	=> '/utility/autocompletecity',
					'value'		=> $model->city_id,
					'options'	=> array(
						'showAnim'	=>'fold',
						'open' => 'js:function(){}'
					),
					'htmlOptions'	=> array('id'=>'city_id', 'name'=>'Store[city_id]', 'class' => ''),
					'cssFile' => null,
				));
				?>
                        </div>
                </div>

		<div class="search-<?php echo Store::TYPE_OFFLINE?>">
			<?php echo $form->textFieldRow($model, 'address'); ?>
		</div>

		<div class="clearfix">
			<label><?php echo $model->getAttributeLabel('contractor_id'); ?></label>
			<div class="input">
				<?php
				$contractor = Contractor::model()->findByPk($model->contractor_id);

				$this->widget('application.components.widgets.EAutoComplete', array(
					'valueName'	=> is_null($contractor) ? '' : $contractor->name.' ('.$contractor->id.')',
					'sourceUrl'	=> '/admin/utility/accontractor',
					'value'		=> $model->contractor_id,
					'options'	=> array(
						'showAnim'	=>'fold',
						'open' => 'js:function(){}'
					),
					'htmlOptions'	=> array('id'=>'contractor_id', 'name'=>'Store[contractor_id]', 'class' => ''),
					'cssFile' => null,
				));
				?>
			</div>
		</div>

                <div class="clearfix">
                        <?php echo CHtml::label('Добавлен от', 'date_from')?>
                        <div class="input">
                                <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'name'=>'date_from',
                                'value'	=> $date_from,
                                'language'	=> 'ru',
                                'options'=>array('dateFormat'=>'dd.mm.yy'),
                                'htmlOptions'=>array(
                                        'style'=>'width:150px;'
                                ),
                        ));?>
                        </div>
                </div>
                <div class="clearfix">
                        <?php echo CHtml::label('Добавлен до', 'date_to')?>
                        <div class="input">
                                <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'name'=>'date_to',
                                'value'=> $date_to,
                                'language'	=> 'ru',
                                'options'=>array('dateFormat'=>'dd.mm.yy'),
                                'htmlOptions'=>array(
                                        'style'=>'width:150px;'
                                ),
                        ));?>
                        </div>
                </div>

                <div class="clearfix">
                    <?php echo CHtml::label("Категория", 'category_id'); ?>
                    <div class="input">
                        <?php
                       $category = Yii::app()->dbcatalog2->createCommand()->select('name')->from('cat_category')->where('id=:id', array(':id'=>(int)$category_id))->limit(1)->queryScalar();
                        $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                            'name'=>'Category',
                            'value'=>$category,
                            'sourceUrl'=>'/catalog2/admin/category/acCategory',
                            'options'=>array(
                                'minLength'=>'2',
                                'showAnim'=>'fold',
                                'select'=>'js:function(event, ui) {$("#Store_category").val(ui.item.id).keyup();}',
                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Store_category").val("");}}',
                            ),
                        ));
                        ?>
                        <?php echo CHtml::hiddenField("Store[category]", $category_id, array('id' => "Store_category")); ?>
                    </div>
                </div>


                <div class="actions">
                        <?php echo CHtml::submitButton('Найти', array('class' => 'btn')); ?>
                </div>

        <?php $this->endWidget(); ?>

</div>

<?php Yii::app()->getClientScript()->registerScript('search-switcher', '

	var refresh = function(){
		$(".search-' . Store::TYPE_OFFLINE . '").hide();
		$(".search-' . Store::TYPE_ONLINE . '").hide();
		$(".search-"+$("#Store_type").val()).show();
	}

	var clear = function(){
		$(".search-' . Store::TYPE_OFFLINE . '").find("input").val("");
		$(".search-' . Store::TYPE_ONLINE . '").find("input").val("");
	}

	refresh();


	$("#Store_type").change(function(){
		refresh(); clear();
	});
', CClientScript::POS_READY)?>
