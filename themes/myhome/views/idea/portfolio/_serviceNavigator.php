<div class="portfolio_head dotted">
        <div class="menu_level2 <?php echo Yii::app()->user->id == $user->id ? 'authorized'  : ''; ?>">
                <ul>
			<?php if (Yii::app()->user->id == $user->id) 
				$serviceList = $user->serviceList;
			else
				$serviceList = $user->usedServiceList;
				
			?>
                        <?php foreach($serviceList as $item) : ?>
                        
                        <li <?php echo ($currentServiceId == $item['service_id']) ? 'class="current"' : ''; ?>>
                                <a href="<?php echo $this->createUrl("/users/{$user->login}/portfolio/service/{$item['service_id']}"); ?>"><?php echo $item['service_name']; ?></a>
                                <span><?php echo $item['project_qt']; ?></span>
                        </li>
                        <?php endforeach; ?>
                             
                        <?php if(Yii::app()->user->id == $user->id) : ?>
                                <li <?php echo $currentServiceId == 'draft' ? 'class="current"' : ''; ?>>
                                        <?php echo CHtml::link('Черновики', $this->createUrl("/users/{$user->login}/portfolio/draft"), array('class'=>'saved')); ?>
                                        <span><?php echo $user->data->draft_qt; ?></span>
                                </li>
                        <?php endif; ?>
                </ul>
        </div>
        <?php if(Yii::app()->user->id == $user->id) : ?>
                <div class="btn_conteiner btn_project">
                        <span class="btn_grey service_choice <?php if (empty($user->serviceList)) echo 'empty_service_list';?>">Добавить новый проект <i></i></span>
                        <div class="servise_choice_list hide">
                                <ul>
                                        <?php foreach($user->serviceList as $item) : ?>
                                                <li><a href="<?php echo $this->createUrl("/users/{$user->login}/portfolio/create/service/{$item['service_id']}");?>"><?php echo $item['service_name']; ?></a></li>
                                        <?php endforeach; ?>
                                </ul>
                        </div>
                </div>

		<div class="-hidden">
			<div class="popup popup-empty-service" id="popup-message-guest">
				<div class="popup-header">
					<div class="popup-header-wrapper">
						Создание нового проекта
					</div>
				</div>
				<div class="popup-body">
					Чтобы создать новый проект, выберите хотя бы одну <a href="/users/<?php echo $user->login;?>/services">услугу</a>.
				</div>
			</div>
		</div>
        <?php endif; ?>
        <div class="clear"></div>
</div>
