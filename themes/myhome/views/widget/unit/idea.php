<h2 class="main_page_head"><a href="/idea">Идеи</a></h2>
<span class="headline_counter">##idea_quantity##</span>
<div class="item_list ideas">
	<div class="ideas_left">
		<div class="alias">
			Коллекция идей интерьеров и архитектуры поможет вам сформировать идеальный образ вашего дома. Смелые и необычные решения или классические варианты — в каталоге идей есть все
			<p>
				<a href="/idea/interior">Дизайн интерьера</a>
				<a href="/idea/architecture">Архитектура</a>
			</p>

		</div>
		<div class="item">
			<a href="/idea/catalog/index?filter=0&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=131%2C+132%2C+133%2C+136%2C+140%2C+">
				<img width="160" src="/img/tmp/new_main/idea1.jpg"/>
			</a>
			<div class="item_desc">
				<a class="item_head" href="/idea/catalog/index?filter=0&ideatype=2&sortby=1&pagesize=24&page=1&object_type=118&style=131%2C+132%2C+133%2C+136%2C+140%2C+">Современные дома</a>
			</div>
		</div>

		<?php foreach($data as $key=>$item):?>
			<?php
			$image = UploadedFile::model()->findByPk($item['image_id']);
			$idea = Interior::model()->findByPk($item['id']);
			?>
			<div class="big item">
				<a href="<?php echo Yii::app()->createUrl('/idea/',array('interior'=>$item['id'])); ?>">
					<?php echo CHtml::image('/'.$image->getPreviewName(array('416', '344', 'crop', '90')), $item['name'], array('align'=>'left', 'width'=>416, 'height'=>344));?>
				</a>
				<div class="item_desc">
					<?php echo CHtml::link($idea->name, Yii::app()->createUrl('/idea/',array('interior'=>$item['id'])), array('class' => 'item_head')); ?><br/>

					<?php if (in_array($idea->author->role, array(User::ROLE_ADMIN, User::ROLE_MODERATOR, User::ROLE_JUNIORMODERATOR, User::ROLE_POWERADMIN, User::ROLE_SALEMANAGER, User::ROLE_SENIORMODERATOR))) : ?>
						<span>Редакция Myhome</span>
					<?php else: ?>
						<?php echo CHtml::link($idea->author->name, Yii::app()->createUrl("/users/{$idea->author->login}")); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>


	</div>
	<div class="ideas_right">
		<div class="item">
			<a href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&room=mansard%2C+">
				<img width="160" src="/img/tmp/new_main/idea2.jpg"/>
			</a>
			<div class="item_desc">
				<a class="item_head" href="/idea/catalog/index?filter=1&ideatype=2&sortby=0&pagesize=24&page=1&object_type=118&room=mansard%2C+">Дома с мансардой</a>
			</div>
		</div>
		<div class="item">
			<a href="/idea/interior/kitchen">
				<img width="160" src="/img/tmp/new_main/idea3.png"/>
			</a>
			<div class="item_desc">
				<a class="item_head" href="/idea/interior/kitchen">Кухни</a>
			</div>
		</div>
		<div class="item">
			<a href="/idea/interior/bedroom">
				<img width="160" src="/img/tmp/new_main/idea4.jpg"/>
			</a>
			<div class="item_desc">
				<a class="item_head" href="/idea/interior/bedroom">Спальни</a>
			</div>
		</div>
	</div>
</div>