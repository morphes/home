<div class="page_settings" <?php if (isset($bottom)) echo 'style="height:auto;"'?>>

        <?php if(isset($hideSortOptions) && !$hideSortOptions) : ?>
                <div class="sort_elements drop_down">
                        Сортировать по
                        <?php
                        if (!isset($sortList[$sortType])) {
                                $sortType = @reset(array_keys($sortList));
                        }
                        ?>
                        <span class="exp_current"><?php echo $sortList[$sortType]; ?><i></i></span>
                        <ul>
                                <?php

                                foreach ($sortList as $key => $value) {
                                        if (!$search && $key == Config::IDEA_SORT_RELEVANCE) {
                                                continue;
                                        }
                                        if ($key == $sortType)
                                                echo CHtml::tag('li', array('data-value' => $key, 'class' => 'active'), $value);
                                        else
                                                echo CHtml::tag('li', array('data-value' => $key), $value);
                                }
                                ?>
                        </ul>
                </div>
        <?php endif; ?>

        <?php if(isset($hideSortOptions) && !$hideSortOptions) : ?>
                <div class="elements_on_page drop_down">
                        На странице <span class="exp_current"><?php echo $availablePageSizes[$pageSize]; ?><i></i></span>
                        <ul>
                        <?php
                        foreach ($availablePageSizes as $key => $value) {
                                echo CHtml::tag('li', array('data-value' => $key), $value);
                        }
                        ?>
                        </ul>
                </div>
        <?php endif; ?>

    <?php if (isset($bottom)) : ?>
        <div class="pagination" style="float:right; display: block; padding-top: 5px;">
            <?php $this->widget('application.components.widgets.CustomListPager', array(
                'pages'          => $dataProvider->pagination,
                'htmlOptions'    => array('class' => '-menu-inline -pager'),
                'maxButtonCount' => 5,
            )); ?>
        </div>
    <?php else : ?>
        <div class="pages">
            <?php
            $this->widget('application.components.widgets.CustomPager2', array(
                'pages' => $dataProvider->getPagination(),
                'maxButtonCount' => 5,
            ));
            ?>
        </div>
    <?php endif; ?>

	<div class="clear"></div>
</div>
<?php  ?>