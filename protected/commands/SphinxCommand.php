<?php

/**
 * Description of SphinxCommand
 * Обработка RT очереди
 *
 * @author alexsh
 */
class SphinxCommand extends CConsoleCommand
{
    public function init()
    {
        Yii::import('application.modules.tenders.models.Tender');
    }

    public function actionWorker()
    {
        $worker = Yii::app()->gearman->worker();

        $worker->addFunction('sphinx:user_login', array($this, 'updateUserLogin'));
        $worker->addFunction('sphinx:interiorpublic', array($this, 'updateInteriorPublic'));
        $worker->addFunction('sphinx:architecture', array($this, 'updateArchitecture'));
        $worker->addFunction('sphinx:interior_content', array($this, 'updateInteriorContent'));
        $worker->addFunction('sphinx:tender', array($this, 'updateTender'));
        $worker->addFunction('sphinx:product', array($this, 'updateProduct'));
        $worker->addFunction('sphinx:product2', array($this, 'updateProduct2'));
        $worker->addFunction('sphinx:store', array($this, 'updateStore'));
        $worker->addFunction('sphinx:store2', array($this, 'updateStore2'));
        $worker->addFunction('sphinx:contractor', array($this, 'updateContractor'));

        $worker->addFunction('sphinx:idea', array($this, 'updateIdea'));
        $worker->addFunction('sphinx:media', array($this, 'updateMedia'));

        $worker->addFunction('sphinx:user_message', array($this, 'updateUserMessage'));
        $worker->addFunction('sphinx:storeDelete', array($this, 'deleteStore'));
        $worker->addFunction('sphinx:storeDelete2', array($this, 'deleteStore2'));

        while ($worker->work()) {
            if (GEARMAN_SUCCESS != $worker->returnCode()) {
                echo "Worker failed: " . $worker->error() . "\n";
            }
            echo "\n";
        }
    }

    public function updateContractor(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' Contractor ';
        $workload = $job->workload();
        try {
            $contractorId = unserialize($workload);

            $sql = 'SELECT t.id, t.name, t.status, t.create_time, t.update_time, t.worker_id '
                . 'FROM cat_contractor as t '
                . 'WHERE t.id=' . $contractorId;

            $object = Yii::app()->db->createCommand($sql)->queryRow();
            if (!$object)
                throw new Exception('Invalid ID : ' . $contractorId);


            $sphinxQl = 'REPLACE INTO {{contractor}} (`id`, `name`, `worker_id`, `status`, `create_time`, `update_time`) VALUES '
                . '(' . $object['id'] . ',\'' . addslashes($object['name']) . '\',' . intval($object['worker_id']) . ',' . intval($object['status']) . ','
                . $object['create_time'] . ',' . $object['update_time'] . ')';
            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

            echo 'ID: ' . $contractorId . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }
    }

    public function updateStore(GearmanJob $job, $indexName = 'store', $conn = null)
    {
        echo date('[Y-m-d H:i:s]') . ' Store ';
        $workload = $job->workload();

        if (!$conn) {
            $conn = Yii::app()->db;
        }

        try {
            $storeId = (int)unserialize($workload);

            $sql = 'SELECT tmp.*, cs.chain_id, tmp2.product_qt FROM'
                . '(SELECT'
                . ' s.id, s.name, s.address, s.type, '
                . ' s.create_time, s.update_time,'
                . ' s.user_id, s.tariff_id, s.status, '
                . ' GROUP_CONCAT(DISTINCT p.category_id SEPARATOR ",") as category_ids'
                . ' FROM cat_store as s'
                . ' INNER JOIN cat_store_price sp ON sp.store_id = s.id'
                . ' INNER JOIN cat_product p ON p.id = sp.product_id'
                . ' WHERE s.status=1 AND s.id = ' . $storeId
                . ' GROUP BY s.id) as tmp'
                . ' LEFT JOIN cat_chain_store cs ON cs.store_id = tmp.id '
                . ' LEFT JOIN (SELECT store_id, COUNT(product_id) as product_qt'
                . ' FROM cat_store_price as csp'
                . ' INNER JOIN cat_product as p ON p.id=csp.product_id AND p.status=2'
                . ' WHERE by_vendor=0 AND store_id = ' . $storeId . ' GROUP BY store_id'
                . ' ) as tmp2 ON tmp2.store_id = tmp.id';


            $object = $conn->createCommand($sql)->queryRow();
            if (!$object) {
                Yii::app()->sphinx->createCommand("DELETE FROM {{$indexName}} WHERE id = " . $storeId)->execute();
                throw new Exception('Invalid ID : ' . $storeId);
            }


            $sphinxQl = 'REPLACE INTO {{'.$indexName.'}} '
                . ' (`id`, `status`'
                . ', `category_ids`, `first_letter`'
                . ', `str_id`, `name`, `address`'
                . ', `type`, `user_id`, `chain_id`, `is_chain`'
                . ', `tariff_id`'
                . ', `product_qt`'
                . ', `create_time`, `update_time`)'
                . ' VALUES ';
            $cnt = 0;

            if ($cnt > 0) {
                $sphinxQl .= ',';
            } else {
                $cnt++;
            }

            // Получаем код первого символа
            $intFirstLetter = crc32(
                mb_strtoupper(
                    mb_substr($object['name'], 0, 1, 'UTF-8'),
                    'UTF-8'
                )
            );

            /* -----------------------------------------------------------
             * Вводим два дополнительных параметра
             * chain_id - хранит ID сети магазина, к которой относится магазин
             * is_chain - флаг принадлежности магазина к сети магазинов
             *
             * Если магазин не принадлежит сети, то вместо
             * id сети пишем его идентификатор в отрицаельном
             * значении (сфинкс его по кругу превратит в большое
             * положительное) и ставим флаг is_chain в ноль.
             *
             * chain_id нужен для группировки при полнотекстовом поиске.
             * -----------------------------------------------------------
             */
            $chainId = intval($object['chain_id']);
            if ($chainId > 0) {
                $isChain = 1;
            } else {
                $isChain = 0;
                $chainId = -1 * $object['id'];
            }


            $sphinxQl .= '('
                . $object['id']
                . ',' . intval($object['status'])
                . ',(' . $object['category_ids'] . ')'
                . ',' . $intFirstLetter
                . ',\'' . $object['id'] . '\''
                . ',\'' . addslashes($object['name']) . '\''
                . ',\'' . addslashes($object['address']) . '\''
                . ',' . intval($object['type'])
                . ',' . intval($object['user_id'])
                . ',' . $chainId
                . ',' . $isChain
                . ',' . intval($object['tariff_id'])
                . ',' . intval($object['product_qt'])
                . ',' . $object['create_time']
                . ',' . $object['update_time']
                . ')';

            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

            echo 'ID: ' . $storeId . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }
    }

    public function deleteStore(GearmanJob $job, $indexName = 'store')
    {
        echo date('[Y-m-d H:i:s]') . ' Store ';
        $workload = $job->workload();
        try {
            $storeId = unserialize($workload);
            $storeId = (int)$storeId;
            $sphinxQl = 'DELETE FROM {{'.$indexName.'}} WHERE id=' . $storeId;
            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
            echo 'ID: ' . $storeId . ' delete AFFECTED: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }

    }

    public function deleteStore2(GearmanJob $job)
    {
        $this->deleteStore($job, 'store2');
    }

    public function updateTender(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' Tender ';
        $workload = $job->workload();
        try {
            $tenderId = unserialize($workload);

            $sql = 'SELECT t.id, t.author_id, t.name, t.desc, t.status, t.cost, t.city_id, '
                . 't.response_count, GROUP_CONCAT(DISTINCT ts.service_id SEPARATOR ",") as services, t.create_time, t.update_time, '
                . 'GROUP_CONCAT(DISTINCT tr.author_id SEPARATOR ",") as users, GROUP_CONCAT(DISTINCT s.name SEPARATOR ", ") as services_name, c.name as city_name '
                . 'FROM tender as t '
                . 'LEFT JOIN tender_service as ts ON ts.tender_id=t.id '
                . 'LEFT JOIN service s ON s.id = ts.service_id '
                . 'LEFT JOIN tender_response as tr ON tr.tender_id=t.id '
                . 'LEFT JOIN city c ON c.id = t.city_id '
                . 'WHERE t.id=' . $tenderId . ' '
                . 'GROUP BY t.id ';

            $object = Yii::app()->db->createCommand($sql)->queryRow();
            //print_r($object);

            if (!$object)
                throw new Exception('Invalid ID : ' . $tenderId);

            $isOpen = (int)in_array($object['status'], array(Tender::STATUS_OPEN, Tender::STATUS_CHANGED));

            $sphinxQl = 'REPLACE INTO {{tender}} (`id`, `author_id`, `name`, `desc`, `services_name`, `city_name`, `status`, `is_open`, `city_id`, `response`, `cost`, `services`, `users`, `create_time`, `update_time`) VALUES '
                . '(' . $object['id'] . ',' . intval($object['author_id']) . ',\'' . addslashes($object['name']) . '\',\'' . addslashes($object['desc']) . '\','
                . '\'' . addslashes($object['services_name']) . '\', \'' . addslashes($object['city_name']) . '\','
                . $object['status'] . ',' . $isOpen . ',' . intval($object['city_id']) . ',' . $object['response_count'] . ','
                . $object['cost'] . ',(' . $object['services'] . '),(' . $object['users'] . '),' . $object['create_time'] . ',' . $object['update_time'] . ')';

            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

            echo 'ID: ' . $tenderId . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }
    }

    public function updateIdea(GearmanJob $job)
    {
        Yii::import('application.commands.models.IdeaReindex');

        echo date('[Y-m-d H:i:s]') . ' Idea ';
        $workload = $job->workload();
        try {
            $data = unserialize($workload);

            if (!isset($data['typeId']) || !isset($data['id']) || !in_array($data['typeId'], array(Config::INTERIOR, Config::INTERIOR_PUBLIC, Config::ARCHITECTURE)))
                throw new Exception('Invalid options');

            $typeId = $data['typeId'];
            $id = $data['id'];

            $reindex = new IdeaReindex();

            $result = $reindex->index($typeId, $id);

            echo 'TypeID: ' . $typeId . ' ID: ' . $id . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Переиндексация записи из Журнала
     *
     * @param GearmanJob $job
     * @throws Exception
     */
    public function updateMedia(GearmanJob $job)
    {
        Yii::import('application.modules.media.models.Media');
        Yii::import('application.commands.models.MediaReindex');

        echo date('[Y-m-d H:i:s]') . ' Media ';
        $workload = $job->workload();
        try {
            $data = unserialize($workload);

            if (!isset($data['type']) || !isset($data['id']) || !in_array($data['type'], array(Media::TYPE_KNOWLEDGE, Media::TYPE_EVENT, Media::TYPE_NEWS)))
                throw new Exception('Invalid options');

            $type = $data['type'];
            $id = $data['id'];

            $reindex = new MediaReindex();

            $resultCount = $reindex->indexOne($type, $id);

            echo 'TypeID: ' . $type . ' ID: ' . $id . ' result: ' . $resultCount;
        } catch (Exception $e) {
            echo 'Error data: ' . $workload . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление  architecture
     */
    public function updateArchitecture(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' Architecture ';
        $data = $job->workload();
        try {
            /** Architecture ID */
            $aId = unserialize($data);

            $sql = 'SELECT ar.id, ar.object_id as object, ar.building_type_id as build, ar.style_id as style, ar.material_id as material, '
                . 'ar.floor_id as floor, ar.create_time, ar.update_time, ar.average_rating, ar.status, '
                . 'CONCAT_WS(" ", '
                . 'CASE WHEN ar.room_mansard = 1 THEN "mansard" ELSE "" END, '
                . 'CASE WHEN ar.room_garage = 1 THEN "garage" ELSE "" END, '
                . 'CASE WHEN ar.room_ground = 1 THEN "ground" ELSE "" END, '
                . 'CASE WHEN ar.room_basement = 1 THEN "basement" ELSE "" END '
                . ') as room, ar.name as name, CONCAT_WS(" ", ih_color.option_value, GROUP_CONCAT(ihc.option_value SEPARATOR " " )) as color '
                . 'FROM architecture as ar '
                . 'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ar.color_id '
                . 'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = ' . Config::ARCHITECTURE . ') as iac ON iac.item_id = ar.id '
                . 'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
                . 'WHERE ar.id=' . intval($aId) . ' '
                . 'GROUP BY ar.id';

            $object = Yii::app()->db->createCommand($sql)->queryRow();
            if (!$object)
                throw new Exception(' Invalid ID : ' . $aId);

            $sphinxQl = 'REPLACE INTO {{architecture}} (`id`, `name`, `color`, `room`, `status`, `object`, `build`, `material`, `floor`, `style`, `average_rating`, `create_time`, `update_time`) VALUES '
                . '(' . $object['id'] . ',\'' . addslashes($object['name']) . '\',\'' . addslashes($object['color']) . '\',\'' . addslashes($object['room']) . '\','
                . $object['status'] . ',' . intval($object['object']) . ',' . intval($object['build']) . ',' . intval($object['material']) . ',' . intval($object['floor']) . ','
                . intval($object['style']) . ',' . $object['average_rating'] . ',' . $object['create_time'] . ',' . $object['update_time'] . ')';

            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

            echo 'ID: ' . $aId . ' result: ' . $result;

            Yii::app()->gearman->appendJob('sphinx:idea', array('typeId' => Config::ARCHITECTURE, 'id' => $aId));
        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление  interiorpublic
     */
    public function updateInteriorPublic(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' InteriorPublic ';
        $data = $job->workload();
        try {
            /**
             * ID общественного интерьера
             */
            $ipId = unserialize($data);

            $sql = 'SELECT ip.id, ip.object_id as object, ip.status, ip.building_type_id as build, ip.style_id as style, '
                . 'ip.create_time, ip.update_time, ip.name as name, '
                . 'CONCAT_WS(" ", ih_color.option_value, GROUP_CONCAT(ihc.option_value SEPARATOR " " )) as color, ip.average_rating '
                . 'FROM interiorpublic as ip '
                . 'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ip.color_id '
                . 'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = ' . Config::INTERIOR_PUBLIC . ') as iac ON iac.item_id = ip.id '
                . 'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
                . 'WHERE ip.id=' . intval($ipId) . ' '
                . 'GROUP BY ip.id ';

            $object = Yii::app()->db->createCommand($sql)->queryRow();
            if (!$object)
                throw new Exception('Invalid ID : ' . $ipId);

            $sphinxQl = 'REPLACE INTO {{interiorpublic}} (`id`, `name`, `color`, `status`, `object`, `build`, `style`, `average_rating`, `create_time`, `update_time`) VALUES '
                . '(' . $object['id'] . ',\'' . addslashes($object['name']) . '\',\'' . addslashes($object['color']) . '\','
                . $object['status'] . ',' . intval($object['object']) . ',' . intval($object['build']) . ',' . intval($object['style']) . ','
                . $object['average_rating'] . ',' . $object['create_time'] . ',' . $object['update_time'] . ')';

            $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

            echo 'ID: ' . $ipId . ' result: ' . $result;
            Yii::app()->gearman->appendJob('sphinx:idea', array('typeId' => Config::INTERIOR_PUBLIC, 'id' => $ipId));
        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление user_login
     * @return boolean
     */
    public function updateUserLogin(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' UserLogin ';
        $data = $job->workload();
        try {
            /**
             * ID пользователя
             */
            $uid = unserialize($data);
            $sql = 'SELECT user.id, user.login, CONCAT_WS(" ",user.firstname,user.lastname,user.secondname) as name, user.status as status, '
                . '(SELECT COUNT(interior.id) FROM interior WHERE interior.author_id = user.id AND (status=2 OR status=3 OR status=4)) as count_interior, '
                . 'user.role as role, user.create_time, user.update_time '
                . 'FROM user '
                . 'WHERE user.id=' . intval($uid);
            $result = Yii::app()->db->createCommand($sql)->queryRow();
            if (!$result)
                throw new Exception('Invalid ID : ' . $uid);

            $sql = 'REPLACE INTO {{user_login}} (`id`, `login`, `name`, `status`, `count_interior`, `role`, `create_time`, `update_time`) '
                . "VALUES ({$result['id']}, '" . addslashes($result['login']) . "', '" . addslashes($result['name'])
                . "', {$result['status']}, {$result['count_interior']}, {$result['role']},{$result['create_time']}, {$result['update_time']})";

            $result = Yii::app()->sphinx->createCommand($sql)->execute();
            echo 'ID: ' . $uid . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление user_message
     * @return boolean
     */
    public function updateUserMessage(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' UserMessage ';
        $data = $job->workload();
        try {
            /**
             * ID сообщения
             */
            $msgId = unserialize($data);
            $sql = 'SELECT mb.id, mb.author_id, mb.recipient_id, mb.author_status, mb.recipient_status, mb.create_time, mb.message '
                . ', CONCAT_WS(" ", u1.login, u1.firstname, u1.lastname) as author_name_login'
                . ', CONCAT_WS(" ", u2.login, u2.firstname, u2.lastname) as recipient_name_login'
                . ' FROM msg_body mb'
                . ' LEFT JOIN user u1 ON u1.id = mb.author_id'
                . ' LEFT JOIN user u2 ON u2.id = mb.recipient_id'
                . ' WHERE mb.id = ' . intval($msgId);
            $result = Yii::app()->db->createCommand($sql)->queryRow();
            if (!$result) {
                throw new Exception('Invalid ID: ' . $msgId);
            }

            $msgPlus = addslashes(
                $result['author_name_login']
                . ' ' . $result['recipient_name_login']
                . ' ' . $result['message']
            );
            $sql = 'REPLACE INTO {{user_message}} (id, author_id, recipient_id, author_status, recipient_status, create_time, message) '
                . "VALUES ({$result['id']}, '{$result['author_id']}', '{$result['recipient_id']}', '{$result['author_status']}', '{$result['recipient_status']}', {$result['create_time']}, '" . $msgPlus . "')";

            $result = Yii::app()->sphinx->createCommand($sql)->execute();
            echo 'ID: ' . $msgId . ' result: ' . $result;
        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление interior_content
     * @param array $options array(
     *    'action'=>'', // delete or update row in index
     *    'interior_id', // Interior id, used in update action
     *    'iContentId, // interior content id, used in delete action
     * )
     * @return int|boolean
     */
    public function updateInteriorContent(GearmanJob $job)
    {
        echo date('[Y-m-d H:i:s]') . ' InteriorContent ';
        $data = $job->workload();
        try {
            $options = unserialize($data);

            if (!isset($options['action']))
                throw new Exception('Invalid options: ' . $data);

            if ($options['action'] == 'delete') {
                if (isset($options['iContentId'])) {
                    $sql = 'DELETE FROM {{interior_content}} WHERE id = ' . intval($options['iContentId']);
                    $result = Yii::app()->sphinx->createCommand($sql)->execute();

                    echo 'Delete InteriorContentId: ' . $options['iContentId'] . ' result: ' . $result;
                    return $result;
                }

                if (!isset($options['interior_id']))
                    throw new Exception('Invalid options');

                $interiorId = intval($options['interior_id']);

                $sql = 'SELECT id FROM {{interior_content}} WHERE interior_id = ' . $interiorId;
                $interiorContents = Yii::app()->sphinx->createCommand($sql)->queryAll();

                if (!empty($interiorContents)) {
                    $sql = 'DELETE FROM {{interior_content}} WHERE id IN (';
                    $cnt = 0;
                    foreach ($interiorContents as $content) {
                        if ($cnt > 0)
                            $sql .= ',';
                        $sql .= $content['id'];
                        $cnt++;
                    }
                    $sql .= ')';
                }
                $result = Yii::app()->sphinx->createCommand($sql)->execute(); // Always 0 for sphinx 2.0.4
                echo 'Delete InteriorId: ' . $options['interior_id'] . ' result: ' . $result;
                Yii::app()->gearman->appendJob('sphinx:idea', array('typeId' => Config::INTERIOR, 'id' => $interiorId));
                return;

            } else if ($options['action'] == 'update' && isset($options['interior_id'])) {
                $interiorId = intval($options['interior_id']);
                $sql = 'SELECT id FROM interior_content WHERE interior_id = ' . $interiorId;
                $interiorContents = Yii::app()->db->createCommand($sql)->queryAll();
                if (empty($interiorContents))
                    throw new Exception('Empty data');

                $queryPart = '(';
                $cnt = 0;
                foreach ($interiorContents as $content) {
                    if ($cnt > 0)
                        $queryPart .= ',';
                    $queryPart .= $content['id'];
                    $cnt++;
                }
                $queryPart .= ')';

                $sql = 'SELECT ic.id, i.id AS interior_id, i.name AS name, i.average_rating, i.desc as `desc`, i.object_id, '
                    . 'ih_room.option_value AS rooms, ih_room.desc AS room_desc, ih_style.option_value AS styles, '
                    . 'ih_style.desc AS style_desc, CONCAT_WS(" ", ih_color.option_value, addcolors.addcolors) AS colors, '
                    . 'ih_color.desc AS color_desc, ic.tag AS tags, i.create_time, i.update_time, i.author_id AS interior_authorid, '
                    . 'i.status AS status '
                    . 'FROM interior_content as ic '
                    . 'LEFT JOIN interior as i ON i.id = ic.interior_id '
                    . 'LEFT JOIN idea_heap AS ih_obj ON ih_obj.id = i.object_id '
                    . 'LEFT JOIN idea_heap AS ih_style ON ih_style.id = ic.style_id '
                    . 'LEFT JOIN idea_heap AS ih_room ON ih_room.id = ic.room_id '
                    . 'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ic.color_id '
                    . 'LEFT JOIN ( '
                    . 'SELECT  item_id , GROUP_CONCAT(idea_heap.option_value SEPARATOR " ")  as addcolors '
                    . 'FROM idea_additional_color as ic '
                    . 'INNER JOIN idea_heap ON idea_heap.id = ic.color_id '
                    . 'WHERE idea_heap.idea_type_id = 1 AND item_id IN ' . $queryPart . ' '
                    . 'group by item_id '
                    . ') as addcolors ON addcolors.item_id = ic.id '
                    //.'WHERE (i.status = 3 or i.status = 7) AND ic.id IN '.$queryPart;
                    . 'WHERE ic.id IN ' . $queryPart;


                $objects = Yii::app()->db->createCommand($sql)->queryAll();

                if (!$objects) {
                    //Yii::log(__METHOD__.' Data not found. icID: '.$icId, CLogger::LEVEL_INFO, 'sphinx');
                    return false;
                }

                $sphinxQl = 'REPLACE INTO {{interior_content}} (id, `name`, `desc`, `rooms`, `room_desc`, `styles`, `style_desc`, `tags`, `status`, `interior_id`, `object_id`, `average_rating`, `create_time`, `update_time`) VALUES ';
                $cnt = 0;
                foreach ($objects as $object) {
                    if ($cnt > 0)
                        $sphinxQl .= ',';
                    $cnt++;

                    $sphinxQl .= '(' . $object['id'] . ', \'' . addslashes($object['name']) . '\', \'' . addslashes($object['desc']) . '\', \''
                        . addslashes($object['rooms']) . '\', \'' . addslashes($object['room_desc']) . '\', \'' . addslashes($object['styles']) . '\', \''
                        . addslashes($object['style_desc']) . '\', \'' . addslashes($object['tags']) . '\', ' . $object['status'] . ', '
                        . $object['interior_id'] . ', ' . $object['object_id'] . ', ' . $object['average_rating'] . ', ' . $object['create_time'] . ', ' . $object['update_time'] . ')';
                }
                $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
                echo 'Update InteriorId: ' . $interiorId . ' result: ' . $result;

                Yii::app()->gearman->appendJob('sphinx:idea', array('typeId' => Config::INTERIOR, 'id' => $interiorId));
                return;
            }

            throw new Exception('Unsupported action: ' . $data);
        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Обновление product_value
     */
    public function updateProduct(GearmanJob $job, $indexName = 'product', $conn = null)
    {
        echo date('[Y-m-d H:i:s]') . ' Product ';
        $data = $job->workload();

        if (!$conn) {
            $conn = Yii::app()->db;
        }

        try {
            $options = unserialize($data);

            if (!isset($options['action']))
                throw new Exception('Invalid options: ' . $data);

            Yii::import('application.modules.catalog.models.*');

            /**
             * Удаление из товара из индекса индекса
             */
            if ($options['action'] == 'delete' || $options['action'] == 'update') {

                if (!isset($options['product_id']))
                    throw new Exception('Invalid options: ' . $data);

                $sql = 'DELETE FROM {{' . $indexName . '}} WHERE id = ' . intval($options['product_id']);
                $result = Yii::app()->sphinx->createCommand($sql)->execute();

                if ($options['action'] == 'delete')
                    echo 'Deleted product_id: ' . $options['product_id'] . ' result: ' . $result;
            }

            /**
             * Индексация товара
             */
            if ($options['action'] == 'update' && isset($options['product_id'])) {

                $product = $conn->createCommand()
                    ->select('p.id, p.average_price, p.name, p.desc, p.category_id, p.vendor_id,
                                        	p.usageplace, p.create_time, p.average_rating, c.params, tmp_store.is_vitrina,
                                        	v.name as vendor_name, c.name as category_name, country.name as country_name,
                                        	tmp_store.avg_price as avg_price, tmp_store.max_price as max_price, tmp_store.store_ids as store_ids,
                                        	tmp_store.mall_ids as mall_ids')
                    ->from('cat_product p')
                    ->leftJoin('cat_category c', 'c.id=p.category_id')
                    ->leftJoin('cat_vendor v', 'v.id=p.vendor_id')
                    ->leftJoin('country country', 'country.id=p.country')
                    ->leftJoin('(SELECT
							csp.product_id, MAX(csp.price) as max_price, AVG(csp.price) as avg_price, SUM( IF(cat_store.tariff_id=:tid AND csp.by_vendor=0, 1, 0) ) as is_vitrina,
							GROUP_CONCAT(DISTINCT cat_store.id) as store_ids,
							GROUP_CONCAT(DISTINCT cat_store.mall_build_id) as mall_ids
						FROM cat_store
						INNER JOIN cat_store_price csp ON csp.store_id = cat_store.id
						WHERE product_id = :pid
					) as tmp_store
					', 'tmp_store.product_id = p.id', array(':pid' => intval($options['product_id']), ':tid' => Store::TARIF_VITRINA))
                    ->where('p.status=:st and p.id=:id', array(':st' => 2, ':id' => intval($options['product_id'])))
                    ->queryRow();

                /**
                 * Получение опций и значений опций для идексируемого товара
                 */
                $values = $conn->createCommand()
                    ->select('v.value as option_value, o.key as option_key, o.type_id, o.id as option_id')
                    ->from('cat_value v')->leftJoin('cat_option o', 'o.id = v.option_id')
                    ->where('v.product_id=:pid', array(':pid' => $product['id']))->queryAll();

                /**
                 * Список дополнительных полей в индексе для хранения некоторых значений опции, по
                 * которым нужно дать возможность сортировки
                 */
                $opt_val_array = array();
                for ($j = 1; $j < Option::MAX_FILTERABLE_OPTION_QT + 1; $j++) {
                    $opt_val_array[] = 'opt_val_' . $j;
                }

                /**
                 * Обработка имен доп. полей для передачи в запрос
                 */
                $fields = array();
                foreach ($opt_val_array as $opt_val) {
                    $fields[] = '`' . $opt_val . '`';
                }

                $options_arr = array();
                $rooms_names = array();
                $colors_names = array();
                $styles_names = array();

                $sphinxQl = 'REPLACE INTO {{' . $indexName . '}} (`sort_default`, `sort_rand`, `mall_ids`, `store_ids`, `city_ids`, `sort_date`, `sort_price`, `id`, `price`, `category_id`, `vendor_id`, `create_time`, `average_rating`, `name`, `desc`, `vendor_name`, `category_name`, `colors`, `rooms`, `styles`, `country_name`, `options`, ' . implode(', ', $fields) . ') VALUES ';

                /**
                 * Подготовка доп. индексируемых значений опций для вставки
                 */
                $opt_vals = array();
                foreach ($opt_val_array as $opt_val) {
                    $opt_vals[$opt_val] = -1;
                }

                $catParams = Value::serializeToArrray($product['params']);

                /**
                 * Индексация опций и значений товара
                 */
                foreach ($values as $value) {

                    /**
                     * Обрабока multiValue опций
                     */
                    if (Option::$typeParams[$value['type_id']]['multiValue']) {
                        $multiValues = Value::serializeToArrray($value['option_value']);
                        foreach ($multiValues as $mval) {
                            $options_arr[] = $value['option_key'] . ':' . $mval;
                        }
                    } else {
                        /**
                         * Обработка singleValue опций
                         */
                        $options_arr[] = $value['option_key'] . ':' . $value['option_value'];
                    }

                    /**
                     * Обработка фильтруемых опций
                     */
                    if (isset($catParams['filterable_' . $value['type_id']])) {
                        foreach ($catParams['filterable_' . $value['type_id']] as $opt_val_key => $opt_val) {
                            if ($opt_val == $value['option_id']) {
                                if (!empty($value['option_value']) && preg_match("/^\d+$/", $value['option_value']) != 0)
                                    $opt_vals[$opt_val_key] = (int)$value['option_value'];
                            }
                        }
                    }

                    if ($value['type_id'] == Option::TYPE_COLOR) {
                        $colors_array = Value::serializeToArrray($value['option_value']);
                        foreach ($colors_array as $col)
                            $colors_names[] = $conn->createCommand()->select('name')->from('cat_color')->where('id=:id', array(':id' => $col))->queryScalar();
                    }
                    if ($value['type_id'] == Option::TYPE_STYLE) {
                        $styles_names[] = $conn->createCommand()->select('name')->from('cat_style')->where('id=:id', array(':id' => $value['option_value']))->queryScalar();
                    }
                }

                $opt_vals = implode(', ', $opt_vals);

                if ($product['usageplace']) {
                    $usageplaces = MainRoom::getAllRooms();
                    $sql = 'SELECT room_id FROM cat_product_room WHERE product_id=:pid';
                    $rooms = $conn->createCommand($sql)->bindParam(':pid', $product['id'])->queryColumn();

                    if (is_array($rooms)) {
                        foreach ($rooms as $room) {
                            $rooms_names[] = isset($usageplaces[$room]) ? $usageplaces[$room] : '';
                            $options_arr[] = 'room' . ':' . intval($room);
                        }
                    }
                }

                $id = (int)$product['id'];
                $name = $product['name'];
                $desc = $product['desc'];
                $price = (float)$product['avg_price'];
                $cid = (int)$product['category_id'];
                $vendor_id = (int)$product['vendor_id'];
                $create_time = (int)$product['create_time'];
                $average_rating = (int)$product['average_rating'];
                $options_query = implode(' ', $options_arr);
                $cat_name = $product['category_name'];
                $vendor_name = $product['vendor_name'];
                $country_name = $product['country_name'];
                $rooms_names = implode(' ', $rooms_names);
                $styles_names = implode(' ', $styles_names);
                $colors_names = implode(' ', $colors_names);
                $mall_ids = $product['mall_ids'];
                $store_ids = $product['store_ids'];

                $city_ids_tmp = StoreGeo::GetStoresCity($store_ids, $conn);
                $city_ids = implode(',', $city_ids_tmp);

                /*
                     * ----- Определяем доп. свойства по сортировке даты и цены -----
                     */
                if ($product['max_price']) {
                    if ($product['max_price'] > 0) { // Если у товара есть цена через магазин
                        $sort_date = 1;
                        $sort_price = 1;
                    } else {             // Если магазин есть, а цена не указана
                        $sort_date = 2;
                        $sort_price = 2;
                    }

                } else {                 // Если у товара нет привязанных магазинов
                    $sort_date = 3;
                    $sort_price = 2;
                }

                /**
                 *  Заполнение дефолтной сортировки
                 * 0 - все товары
                 * 1 - с ценой от бесплатных магазов
                 * 2 - без цены от платных
                 * 3 - с ценой от платных
                 */
                if ($product['is_vitrina']) {
                    if ($product['max_price'] && $product['max_price'] > 0) {
                        $defaultSort = 3;
                    } else {
                        $defaultSort = 2;
                    }
                } else {
                    if ($product['max_price'] && $product['max_price'] > 0) {
                        $defaultSort = 1;
                    } else {
                        $defaultSort = 0;
                    }
                }
                $sortRand = rand(0, 100);

                $sphinxQl .= "({$defaultSort}, {$sortRand}, ({$mall_ids}), ({$store_ids}), ({$city_ids}), {$sort_date}, {$sort_price}, {$id}, {$price}, {$cid}, {$vendor_id}, {$create_time}, {$average_rating}, '" . addslashes($name) . "', '" . addslashes($desc) . "', '"
                    . addslashes($vendor_name) . "', '" . addslashes($cat_name) . "', '" . addslashes($colors_names) . "','" . addslashes($rooms_names) . "','"
                    . addslashes($styles_names) . "', '" . addslashes($country_name) . "','" . addslashes($options_query) . "', {$opt_vals})";

                $result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();

                Product::countAveragePrice($id);
                Category::setMaxPrice($cid);

                echo 'Update product #id (index '.$indexName.'): ' . $options['product_id'] . '. Result: ' . $result;
            }

        } catch (Exception $e) {
            echo 'Error data: ' . $data . ' ' . $e->getMessage() . "\n";
        }
    }


    public function updateProduct2(GearmanJob $job)
    {
        $this->updateProduct($job, 'product2', Yii::app()->dbcatalog2);
    }

    public function updateStore2(GearmanJob $job)
    {
        $this->updateStore($job, 'store2', Yii::app()->dbcatalog2);
    }
}