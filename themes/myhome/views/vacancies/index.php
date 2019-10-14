<?php $this->pageTitle = 'Вакансии — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<div class="vacancies-page">

	<div class="pathBar">
		<?php
		$this->widget('application.components.widgets.EBreadcrumbs', array(
			'links' => array(),
		));
		?>
		<h1>Наши вакансии</h1>

		<div class="spacer"></div>
	</div>


	<div class="article">
		<?php echo $staticText; ?>
		
		
		<ul class="vacancies-list">
			<?php
			if ($vacancies) {
				foreach($vacancies as $item) {
					?>
					<li class="item" id="vacancy-<?php echo $item->key;?>">
						<h2><a href="#<?php echo $item->key;?>"><?php echo $item->name;?></a></h2>
						<?php
						if ( ! empty($item->wage))
							echo CHtml::tag('strong', array('class' => 'price'), $item->wage, true);
						else
							echo CHtml::tag('span', array('class' => 'price'), '&nbsp;', true);
						?>
						
						<p class="announ"><?php echo $item->anons;?></p>
						<div class="info">
							<?php echo $item->text;?>
						</div>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</div>
	<div class="side side_right">
		<?php echo $staticTextSide; ?>
	</div>

	

	<div class="spacer"></div>
</div>