<div id="left_side" class="new_template">
	<div class="red_menu">
		<div class="help_section">
			<div class="selector">
				<ul>
					<li><i></i></li>
					<?php
					foreach (Help::$baseNames as $key => $item) {
						$options = array('data-value'=>$key);
						if ($key == $baseId) {
							$options['class'] = 'active';
							echo CHtml::tag('li', $options, CHtml::tag('span', array(), $item) );
						} else {
							$link = CHtml::link( $item, Yii::app()->getController()->createUrl( '/help/help/index', array('baseId'=>$key) ) );
							echo CHtml::tag('li', $options, $link );
						}
					}
					?>
					<!--<li data-value="2"><span>Магазинам</span></li>-->
				</ul>
			</div>
		</div>
		<div class="search_inp">
			<?php echo CHtml::form( Yii::app()->getController()->createUrl('/help/search/index', array('baseId'=>$baseId)), 'get' ); ?>
			<input class="textInput textInput-" name="query" value="<?php echo $query; ?>" data-placeholder="поиск"/>
			<input type="submit" value=" " />
			<?php echo CHtml::endForm(); ?>
		</div>

		<?php
		$cnt = 0;
		foreach ($sections as $section) : ?>
		<div class="red_menu_block">
			<?php
			if ($cnt > 0) {
				echo CHtml::tag('p', array(), $section->name);
			}
			$cnt++;
			?>
			<?php
			$articles = HelpArticle::model()->findAllByAttributes(array('section_id'=>$section->id, 'status'=>HelpArticle::STATUS_OPEN), array('order'=>'position ASC'));
			echo CHtml::openTag('ul');
			foreach ($articles as $article) {
				$options = array();
				if ($article->id == $articleId)
					$options['class'] = 'current';
				echo CHtml::tag('li', $options, CHtml::link($article->name, $article->getFrontLink() ));
			}
			echo CHtml::closeTag('li');
			?>
		</div>

		<?php endforeach; ?>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			help.initSelector();
			help.drawTriangle();
		});
	</script>
</div>