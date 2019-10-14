<div class="left">
    	<h2>Знания</h2>
	<span class="all_elements_link">
		<a href="<?php echo MediaKnowledge::getSectionLink().'?f_theme='.$idTheme;?>">Все<span class="text_block"> знания по теме <?php echo $theme->name;?></span></a><span>&rarr;</span>
	</span>

	<div class="knowledge_items elements">
		<?php
		for ($i = 0; $i <= 1; $i++) {
			if (!isset($knowledges[$i]))
				continue;

			$item = $knowledges[$i];
			?>
			<div class="item" id="<?php echo $item->id;?>">
				<div class="item_image">
				    	<i title="<?php echo MediaKnowledge::$genreNames[$item->genre];?>" class="genre <?php echo MediaKnowledge::$genreCssClass[$item->genre];?>"></i>
				    	<a href="<?php echo $item->getElementLink();?>"><img src="/<?php echo $item->preview->getPreviewName($item::$preview['crop_280x200']);?>" alt="<?php echo $item->title;?>" width=280 height=200/></a>
				</div>
				<div class="descript">
					<h2><a class="item_head" href="<?php echo $item->getElementLink();?>"><?php echo $item->title;?></a></h2>

					<p><?php echo $item->lead;?></p>

					<div class="item_info">
						<div class="block_item_info">
							<span><?php echo CFormatterEx::formatDateToday($item->public_time);?></span>
							<span class="-icon-eye-s -small -gray -gutter-left-hf"><?php echo number_format($item->count_view, 0, '', ' '); ?></span>
							<span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($item),$item->id); ?></span>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<?php
		}
		?>
		<div class="clear"></div>
	</div>

    	<ul class="short_articles">
	    	<?php
		for ($i = 2; $i <= 4; $i++) {
			if (isset($knowledges[$i]))
				$item = $knowledges[$i];
			else
				break;

			?>
			<li>
				<a href="<?php echo $item->getElementLink();?>"><?php echo $item->title;?></a>
				<span>
					<?php echo CFormatterEx::formatDateToday($item->public_time);?>
					<span class="-icon-eye-s -small -gray -gutter-left-hf"><?php echo number_format($item->count_view, 0, '', ' '); ?></span>
					<span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($item),$item->id); ?></span>
				</span>

			</li>
			<?php
		}
		?>
    	</ul>
    	<ul class="short_articles last">
		<?php
		for ($i = 5; $i <= 7; $i++) {
		    	if (isset($knowledges[$i]))
			    $item = $knowledges[$i];
		    	else
			    break;

		    	?>
			<li>
			    	<a href="<?php echo $item->getElementLink();?>"><?php echo $item->title;?></a>
			    	<span>
					    <?php echo CFormatterEx::formatDateToday($item->public_time);?>
					    <span class="-icon-eye-s -small -gray -gutter-left-hf"><?php echo number_format($item->count_view, 0, '', ' '); ?></span>
					    <span class="-icon-thumb-up-xs -small -gray -gutter-left-hf"><?php echo LikeItem::model()->countLikes(get_class($item),$item->id); ?></span>
				</span>
			</li>
		    <?php
		}
		?>
    	</ul>
</div>
<div class="right">
	<?php if ( ! empty($news)) { ?>
		<div class="tab_block">
			<h2>Новости</h2>
			<span class="all_elements_link">
				<a href="<?php echo MediaNew::getSectionLink().'?f_theme='.$idTheme;?>">Все</a><span>&rarr;</span>
			</span>
			<ul class="short_articles">
				<?php
				$html = '';
				foreach ($news as $new) {
					$html .= CHtml::openTag('li');
					$html .= CHtml::link($new->title, $new->getElementLink());
					$html .= CHtml::tag('span', array(), CFormatterEx::formatDateToday($new->public_time), true);
					$html .= CHtml::closeTag('li');
				}
				echo $html;
				?>
			</ul>
			<div class="clear"></div>
		</div>
	<?php } ?>

	<?php /* // Временно коментим блоки, пока не утвердили
    	<div class="tab_block">
        	<h2>События</h2>
		<span class="all_elements_link">
			<a href="#">Все</a><span>&rarr;</span>
		</span>

        	<div class="action_item">
            		<a href="#"><img src="/img/tmp/knowledge/action2.jpg"/></a>

            		<div class="action_item_desc">
                		<a class="item_head" href="#">Фестиваль архитектуры в Питере</a>
                		<span>сегодня</span>
            		</div>
            		<div class="clear"></div>
        	</div>
    	</div>

    	<div class="tab_block">
        	<h2>Спецпредложения</h2>
		<span class="all_elements_link">
			<a href="#">Все</a><span>&rarr;</span>
		</span>

        	<div class="action_item">
            		<a href="#"><img src="/img/tmp/knowledge/action2.jpg"/></a>

            		<div class="action_item_desc">
                		<a class="item_head" href="#">4 по цене 18! Только в октябре и июне!</a>
                		<span>Вчера в 12:30</span>
            		</div>
        	</div>
        	<div class="clear"></div>
    	</div>
 	*/ ?>
</div>

<div class="clear"></div>