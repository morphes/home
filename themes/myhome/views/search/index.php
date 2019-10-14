<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>
<?php Yii::app()->clientScript->registerScript('toogler', 'js.serviceToggler()', CClientScript::POS_READY);?>

<div class="pathBar">

        <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
                'links'=>array(),
        ));?>

        <h1>Поиск по сайту: «<?php echo $query; ?>»</h1>
        <div class="spacer"></div>
</div>


<div class="search_page">

    <?php if(!$totalCount) : ?>
            <div class="no_result">
                К сожалению, по запросу «<?php echo $query; ?>» ничего не найдено.<br>
                Убедитесь, что все слова написаны без ошибок, или попробуйте использовать более популярные ключевые слова.
            </div>
    <?php endif; ?>

    <?php if($totalCount && $meansProvider->getTotalItemCount()) : ?>
            <div class="page_settings new">
                <table>
                    <tr>
                        <td>Возможно вы искали</td>
                        <td>
                            <?php
                                $means = array();
                                foreach($meansProvider->getData() as $mData)
                                        $means[] = CHtml::link($mData->name, $mData->url);
                                echo implode(', ', $means);
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
    <?php endif; ?>


    <?php if($prodProvider->getTotalItemCount()) : ?>
            <div class="search_section">
                <div class="section_name">
                    <h2 class="block_head">Товары</h2>
                    <p><?php echo CHtml::link('Найдено ' . $prodProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'product', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                    <?php $cnt = 1; ?>
                    <?php foreach($prodProvider->getData() as $data) : ?>
                            <div class="item">
                                <span class="cnt"><?php echo $cnt; ?>.</span>
                                <a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>">
                                        <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['resize_60']), '', array('width'=>60, 'height'=>60)); ?>
                                </a>
                                <div class="item_desc">
                                    <a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>" class="item_head"><?php echo Amputate::selectQueryInText($data->name, $query); ?></a>

                                        <?php if($data->average_price > 0) $price = number_format($data->average_price, 0, '.', ' ') . ' руб.'; else $price = 'Цена не указана'; ?>

                                        <?php $this->widget('application.components.widgets.WStar', array(
                                                'selectedStar' => $data->average_rating,
                                                'addSpanClass' => 'rating-b',
                                                'innerText' => CHtml::tag('span', array(), $price),
                                        ));?>

                                    <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>

                                    <div class="item_path">
                                        <?php echo CHtml::link($data->category->name, Category::getLink($data->category_id)); ?>
                                        <span>&bull;</span>
                                        <?php
                                            echo isset($data->vendor) ? CHtml::link($data->vendor->name, Vendor::getLink($data->vendor_id)) : '';
                                            echo isset($data->countryObj) ? (', ' . $data->countryObj->name) : '';
                                        ?>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        <?php $cnt++;?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>


    <?php if($ideaProvider->getTotalItemCount()) : ?>
            <div class="search_section">
                <div class="section_name">
                    <h2 class="block_head">Идеи</h2>
                    <p><?php echo CHtml::link('Найдено ' . $ideaProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'idea', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                    <?php $cnt = 1; ?>
                    <?php foreach($ideaProvider->getData() as $data) : ?>
                            <div class="item">
                                <span class="cnt"><?php echo $cnt; ?>.</span>
                                <a href="<?php echo $data->object->getIdeaLink(); ?>">
					<?php if ($data->object instanceof Architecture) { ?>
						<?php echo CHtml::image($data->object->getPreview('crop_60'), '', array('width'=>60, 'height'=>60)); ?>
				    	<?php } else { ?>
						<?php echo CHtml::image('/'.$data->object->getPreview(Config::$preview['crop_60'], 'default', true), '', array('width'=>60, 'height'=>60)); ?>
				    	<?php } ?>
                                </a>
                                <div class="item_desc">
                                    <a href="<?php echo $data->object->getIdeaLink(); ?>" class="item_head">
                                            <?php echo Amputate::selectQueryInText($data->name, $query); ?>
                                    </a>
                                        <?php $this->widget('application.components.widgets.WStar', array(
                                                'selectedStar' => $data->average_rating,
                                                'addSpanClass' => 'rating-b',
                                        ));?>
                                    <div class="item_path">
                                        <?php if(array_key_exists($data->object->author->role, Config::$rolesAdmin)) : ?>
                                            <?php echo CHtml::tag('span', array(), 'Редакция MyHome')?>
                                        <?php else : ?>
                                            <?php echo CHtml::link($data->object->author->name, $data->object->author->getLinkProfile()); ?>
                                        <?php endif; ?>

                                        <span>&rarr;</span>
                                        <?php echo CHtml::link($data->getTypeLabel(), $data->object->getFilterLink()); ?>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php $cnt++;?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>


    <?php if($specProvider->getTotalItemCount()) : ?>
            <div class="search_section">
                <div class="section_name">
                    <h2 class="block_head">Специалисты</h2>
                    <p><?php echo CHtml::link('Найдено ' . $specProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'specialist', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                        <?php $cnt = 1; ?>
                        <?php foreach($specProvider->getData() as $data) : ?>
                                <div class="item">
                                        <span class="cnt"><?php echo $cnt; ?>.</span>
                                        <a href="<?php echo $data->getLinkProfile(); ?>">
                                                <?php echo CHtml::image('/' .$data->getPreview(Config::$preview['crop_60']), '', array('width'=>60, 'height'=>60)); ?>
                                        </a>
                                        <div class="item_desc">
                                                <a href="<?php echo $data->getLinkProfile(); ?>" class="item_head"><?php echo Amputate::selectQueryInText($data->name, $query); ?></a>
                                                <p><?php echo Amputate::getSearchedContext($data->data->about, $query); ?></p>
                                                <div class="item_path">
                                                        <?php $s_links = array(); ?>
                                                        <?php foreach($data->getServiceListLite() as $s) $s_links[] = CHtml::link($s['service_name'], $this->createUrl('/specialist/'.$s['url'])); ?>
                                                        <?php echo implode(', ', $s_links); ?>
                                                </div>
                                                <div class="block_item_info">
                                                        <?php echo ($data->city) ? $data->city->name : ''; ?>
                                                </div>
                                        </div>
                                        <div class="clear"></div>
                                </div>
                                <?php $cnt++;?>
                        <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>

    <?php if($mediaProvider->getTotalItemCount()) : ?>
            <div class="search_section">
                <div class="section_name">
                    <h2 class="block_head">Журнал</h2>
                    <p><?php echo CHtml::link('Найдено ' . $mediaProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'media', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                    <?php $cnt = 1; ?>
                    <?php foreach($mediaProvider->getData() as $data) : ?>
                        <div class="item">
                                <span class="cnt"><?php echo $cnt; ?>.</span>
                                <a href="<?php echo $data->object->getElementLink(); ?>">
                                    <?php echo CHtml::image('/' .$data->object->preview->getPreviewName(Config::$preview['crop_60']), '', array('width'=>60, 'height'=>60)); ?>
                                </a>
                                <div class="item_desc">
                                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->object->getElementLink(), array('class'=>'item_head')); ?>
                                    <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>
                                    <div class="item_path">
                                            <?php
					    $class = get_class($data->object);
					    echo CHtml::link($data->getTypeName(), $class::getSectionLink());
					    ?>
                                        <span>&bull;</span>
                                        <?php echo implode(', ', $data->getThemesLinks())?>
                                    </div>
                                </div>
                                <div class="clear"></div>
                        </div>
                        <?php $cnt++;?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>


    <?php if($storeProvider->getTotalItemCount()) : ?>
	    <div class="search_section">
		    <div class="section_name">
			    <h2 class="block_head">Магазины</h2>
			    <p><?php echo CHtml::link('Найдено ' . $storeProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'store', 'q'=>$query))); ?></p>
		    </div>
		    <div class="search_list">
			    <?php $cnt = 1; ?>
			    <?php foreach($storeProvider->getData() as $data) : ?>
				    <div class="item">
					    <span class="cnt"><?php echo $cnt; ?>.</span>
					    <div class="item_desc">
						    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getLink($data->id), array('class'=>'item_head')); ?>

						    <p><?php echo Amputate::getSearchedContext($data->address, $query); ?></p>
						    <div class="block_item_info">
							    <div class="block_item_counters">
								    <?php echo 'г. ' . $data->city->name; ?>
							    </div>
						    </div>
					    </div>
					    <div class="clear"></div>
				    </div>
				    <?php $cnt++;?>
			    <?php endforeach; ?>
		    </div>
		    <div class="clear"></div>
	    </div>
    <?php endif; ?>


    <?php if($forumProvider->getTotalItemCount()) : ?>
            <div class="search_section">
                <div class="section_name">
                    <h2 class="block_head">Форум</h2>
                    <p><?php echo CHtml::link('Найдено ' . $forumProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'forum', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                    <?php $cnt = 1; ?>
                    <?php foreach($forumProvider->getData() as $data) : ?>
                        <div class="item">
                                <span class="cnt"><?php echo $cnt; ?>.</span>
                                <div class="item_desc">
                                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getElementLink(), array('class'=>'item_head')); ?>
                                    <p><?php echo Amputate::getSearchedContext($data->description, $query); ?></p>
                                    <div class="block_item_info">
                                        <div class="block_item_counters">
                                            <?php if($data->author) : ?>
                                                <?php echo CHtml::link($data->author->name, $data->author->getLinkProfile()); ?>
                                            <?php else : ?>
                                                Гость
                                            <?php endif; ?>
                                            <span>&rarr;</span>
                                            <?php echo CHtml::link($data->section->name, $data->section->getElementLink()); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                        </div>
                        <?php $cnt++;?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>


    <?php if($tenderProvider->getTotalItemCount()) : ?>
            <div class="search_section last">
                <div class="section_name">
                    <h2 class="block_head">Заказы</h2>
                    <p><?php echo CHtml::link('Найдено ' . $tenderProvider->getTotalItemCount(), $this->createUrl('details', array('t'=>'tender', 'q'=>$query))); ?></p>
                </div>
                <div class="search_list">
                    <?php $cnt = 1; ?>
                    <?php foreach($tenderProvider->getData() as $data) : ?>
                            <div class="item">
                                <span class="cnt"><?php echo $cnt; ?>.</span>
                                <div class="item_desc">
                                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getLink(), array('class'=>'item_head')); ?>
                                        <?php if($data->cost) $price = number_format($data->cost, 2, '.', ' ') . ' руб.'; else $price = 'Бюджет не указан'; ?>
                                        <span class="rating rating-b">
                                                <span><?php echo $price; ?></span>
                                        </span>
                                    <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>
                                    <div class="block_item_info">
                                        <div class="block_item_counters">
                                            <?php echo $data->city->name; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php $cnt++;?>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            </div>
    <?php endif; ?>


</div>