<?php foreach($headServices as $hs) : ?>

        <?php $services = Service::model()->findAllByAttributes(array('parent_id'=>$hs->id), array('order' => 'position desc, id asc')); ?>
        <?php if(count($services) < 5) $mClass = 'all_showed'; else $mClass = ''; ?>

        <div class="service_item <?php echo $mClass; ?>">

            <h2 class="block_head"><?php echo $hs->name;?></h2>

            <?php
                if(mb_strlen(trim($hs->name), 'utf-8') > 26) {
                        $expanderCount = count($services) - 3;
                        $class = 'short';
                } else {
                        $expanderCount = count($services) - 4;
                        $class = 'long';
                }
            ?>
            <ul class="<?php echo $class; ?>">
                    <?php foreach($services as $service) : ?>
                        <li>
                                <?php if($city) : ?>
                                        <?php echo CHtml::link($service->name, $this->createUrl('/specialist/' . $service->url . '/' . $city->eng_name)); ?>
                                <?php else : ?>
                                        <?php echo CHtml::link($service->name, $this->createUrl('/specialist/' . $service->url)); ?>
                                <?php endif; ?>
                            <span><?php echo isset($usersQt[$service->id]) ? $usersQt[$service->id] : ''; ?></span>
                        </li>
                    <?php endforeach; ?>
            </ul>

            <?php if($expanderCount > 0) : ?>
                <?php $expanderText = 'еще ' . CFormatterEx::formatNumeral($expanderCount, array('услуга', 'услуги', 'услуг'))?>
                <p>
                        <i></i>
                        <span data-text="<?php echo $expanderText; ?>" class="service_toggler"><?php echo $expanderText; ?></span>
                </p>
            <?php endif; ?>

        </div>
<?php endforeach; ?>