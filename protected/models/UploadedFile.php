<?php

/**
 * @brief This is the model class for table "uploaded_file".
 *
 * @details The followings are the available columns in table 'uploaded_file':
 * @param integer $id
 * @param integer $author_id
 * @param string $path
 * @param string $name
 * @param string $ext
 * @param string $original_name
 * @param string $desc
 * @param string $keywords
 * @param integer $type
 * @param integer $update_time
 * @param integer $create_time
 *
 * The followings are the available model relations:
 * @param SolutionContent[] $solutionContents
 */
class UploadedFile extends EActiveRecord
{
    const IMAGE_TYPE = 1;
    const FILE_TYPE = 2;
    const DOCUMENT_TYPE = 3; // Images + docs
    const BANNER_TYPE = 4;

    // upload dir name
    const UPLOAD_PATH = 'uploads/protected';
    // prefix for upload dir name
    const PUBLIC_PREFIX = 'uploads/public';
    // range of uid in folder (0..999) => 1 etc
    const PATH_SIZE = 10000;

    // default image preview list
    private static $_preview = array(
        'user' => array( // для пользователей используются ключи Config::$preview
            'default' => 'img/default/nophoto.png',
            '23x23' => 'img/default/nophoto-minimini.png',
            '45x45' => 'img/default/nophoto-mini.png',
            '190x190' => 'img/default/nophoto.png',
            '180x180' => 'img/default/nophoto.png',
        ),
        'default' => array( // Превью по умолчанию для разных размеров
            'default' => 'images/nophoto.svg',
        ),
    );

    // Загруженный файл
    public $file;
    public $image;
    public $uploadfile;

    public function init()
    {
        parent::init();
        $this->onBeforeSave = array($this, 'setDate');
        //$this->onAfterDelete = array($this, 'removeFiles');
    }

    /**
     * Update create_time and update_time in object
     */
    public function setDate()
    {
        if ($this->isNewRecord)
            $this->create_time = $this->update_time = time();
        else
            $this->update_time = time();
    }

    /**
     * Returns the static model of the specified AR class.
     * @return UploadedFile the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'uploaded_file';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('author_id', 'required', 'except' => 'document'), // Исключение для document
            array('type, size, update_time, create_time', 'numerical', 'integerOnly' => true),
            array('path, name, original_name', 'length', 'max' => 255),
            array('desc, keywords', 'length', 'max' => 1000),
            array('ext', 'length', 'max' => 7),
            array('uploadfile', 'file', 'types' => 'jpg, gif, png', 'allowEmpty' => true),

            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, author_id, path, name, ext, size, type, desc, keywords, update_time, create_time', 'safe', 'on' => 'search'),

            array('file', 'file', 'types' => 'jpg, bmp, png, jpeg', 'maxFiles' => 1, 'maxSize' => 104857600000, 'allowEmpty' => true,
                'on' => 'loadImage'),
            array('file', 'file', 'types' => 'zip, rar, doc, xls, pdf', 'maxFiles' => 1, 'maxSize' => 104857600000, 'allowEmpty' => true,
                'on' => 'loadFile'),
            array('file', 'file', 'types' => 'jpg, bmp, png, jpeg, 7z, zip, rar, doc, docx, rtf, xls, xlsx, pdf', 'maxFiles' => 1, 'maxSize' => 104857600000, 'allowEmpty' => true,
                'on' => 'document'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            //'solutionContents' => array(self::MANY_MANY, 'SolutionContent', 'solution_content_uploaded_file(uploaded_file_id, solution_content_id)'),
            'msgBodys' => array(self::MANY_MANY, 'MsgBody', 'msg_file(uploaded_file_id, msg_body_id)'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'path' => 'Path',
            'name' => 'Name',
            'ext' => 'Ext',
            'original_name' => 'Оригинальное имя',
            'type' => 'Type',
            'desc' => 'Описание',
            'keywords' => 'Ключевые слова',
            'update_time' => 'Update Time',
            'create_time' => 'Create Time',
        );
    }

    /**
     * Check the user access to file
     */
    public function checkAccess()
    {
        return $this->author_id === Yii::app()->user->id;
    }

    /**
     * Возвращает размеры текущего объекта-изображения (в px)
     *
     * @type string $type Обозначение типа возвращаемого значения.
     *                    Доступные значения: string, array
     *
     * @return string
     */
    public function getOriginalImageSize($type = 'string')
    {
        $filename = Yii::getPathOfAlias('webroot')
            . DIRECTORY_SEPARATOR . self::UPLOAD_PATH
            . DIRECTORY_SEPARATOR . $this->path
            . DIRECTORY_SEPARATOR . $this->name . '.' . $this->ext;

        if (file_exists($filename)) {

            $imagesize = getimagesize($filename);

            switch ($type) {
                case 'array':
                    return array(
                        'width' => ($imagesize) ? $imagesize[0] : 0,
                        'height' => ($imagesize) ? $imagesize[1] : 1
                    );
                    break;

                case 'string':
                    return ($imagesize)
                        ? $imagesize[0] . ' X ' . $imagesize[1]
                        : '';
                    break;

                default:
                    return '';
                    break;
            }
        }
        return '';
    }


    /**
     * @static
     * Загрузчик изображений, имя временное
     * @param $model CActiveRecord
     * @return UploadedFile | false
     */
    public static function loadImage($model, $fileName, $desc = '', $isAdmin = false, $customFile = null, $returnWithErrors = false, $minSizes = null, $checkNewRecord = true, $isFakeUpload = false)
    {
        if (is_null($model) || empty($fileName))
            return false;

        if ($checkNewRecord && $model->getIsNewRecord())
            return false;

        if (!$model instanceof IUploadImage)
            throw new Exception('Invalid model type');

        if (!$model->checkAccess() && !$isAdmin)
            return false;

        $uFile = new UploadedFile('loadImage');
        $uFile->file = CUploadedFile::getInstance($model, $fileName);

        if (!$uFile->file) {
            if (!is_null($customFile)) {
                $uFile->file = $customFile;
            } else {
                return false;
            }
        }

        // валидация габаритов изображения
        if ($minSizes && is_array($minSizes) && isset($minSizes['width']) && isset($minSizes['height'])) {
            $imageSize = getimagesize($uFile->file->tempName);
            if ($minSizes['width'] > $imageSize[0] || $minSizes['height'] > $imageSize[1])
                $uFile->addError('file', 'Изображение не удовлетворяет габаритом');
        }


        $authorId = $model->getAuthorId();

        $uFile->author_id = $authorId;
        $uFile->path = $model->getImagePath();
        $uFile->name = $model->getImageName();
        $uFile->ext = $uFile->file->extensionName;
        $uFile->size = $uFile->file->size;
        $uFile->type = self::IMAGE_TYPE;
        $uFile->desc = CHtml::encode($desc); // TODO: use purifier(CSafeBehavior)

        $config = $model->imageConfig();

        $model->flushImageType();


        $folder = self::UPLOAD_PATH . '/' . $uFile->path;

        if (!file_exists($folder))
            mkdir($folder, 0700, true);

        if (!$uFile->getErrors() && $uFile->save()) {

            if ($isFakeUpload && $isAdmin)
                $moveResult = rename($uFile->file->tempName, $folder . '/' . $uFile->name . '.' . $uFile->ext);
            else
                $moveResult = $uFile->file->saveAs($folder . '/' . $uFile->name . '.' . $uFile->ext);

            if (!$moveResult)
                return false;

            if (!empty($config['realtime'])) {
                foreach ($config['realtime'] as $imgConfig) {
                    $uFile->generatePreview($imgConfig);
                }
            }
            if (!empty($config['background'])) {
                $imgInfo = array(
                    'path' => $uFile->path,
                    'name' => $uFile->name,
                    'ext' => $uFile->ext,
                );
                Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config' => $config['background']));
            }
            return $uFile;
        } else {
            $model->addErrors($uFile->getErrors());

            if ($returnWithErrors)
                return $uFile;
            else
                return false;
        }
    }

    public static function loadImage2(IUploadImage $model, $tempFile, array $filePathInfo)
    {
        if ($model->getIsNewRecord())
            return false;

        $uFile = new UploadedFile('loadImage');
        $uFile->author_id = $model->user_id;
        $uFile->path = $model->getImagePath();
        $uFile->name = $model->getImageName();
        $uFile->ext = $filePathInfo['extension'];
        $uFile->size = filesize($tempFile);
        $uFile->type = self::IMAGE_TYPE;
        $config = $model->imageConfig();
        $model->flushImageType();
        $folder = Yii::app()->basePath . '/../' . self::UPLOAD_PATH . '/' . $uFile->path;

        if (!file_exists($folder))
            mkdir($folder, 0700, true);

        if (!$uFile->getErrors() && $uFile->save()) {

            copy($tempFile, $folder . '/' . $uFile->name . '.' . $uFile->ext);
            unlink($tempFile);

            if (!empty($config['realtime'])) {
                foreach ($config['realtime'] as $imgConfig) {
                    try {
                        $uFile->generatePreview($imgConfig, null, true);
                    } catch (Exception $e) {
                        return null;
                    }
                }
            }
            if (!empty($config['background'])) {
                $imgInfo = array(
                    'path' => $uFile->path,
                    'name' => $uFile->name,
                    'ext' => $uFile->ext,
                );
                Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config' => $config['background']));
            }
            return $uFile;
        } else {
            return null;
        }
    }

    public static function loadImages($model, $fileName, $desc = '', $isAdmin = false, $customFile = null, $returnWithErrors = false, $minSizes = null, $checkNewRecord = true, $isFakeUpload = false)
    {
        if (is_null($model) || empty($fileName))
            return false;

        if ($checkNewRecord && $model->getIsNewRecord())
            return false;

        if (!$model instanceof IUploadImage)
            throw new Exception('Invalid model type');

        if (!$model->checkAccess() && !$isAdmin)
            return false;

        $filesArray = array();
        $resultArray = array();
        $filesArray = CUploadedFile::getInstances($model, $fileName);

        foreach ($filesArray as $file) {

            $uFile = new UploadedFile('loadImage');
            $uFile->file = $file;

            // валидация габаритов изображения
            if ($minSizes && is_array($minSizes) && isset($minSizes['width']) && isset($minSizes['height'])) {
                $imageSize = getimagesize($uFile->file->tempName);
                if ($minSizes['width'] > $imageSize[0] || $minSizes['height'] > $imageSize[1])
                    $uFile->addError('file', 'Изображение не удовлетворяет габаритом');
            }

            if (!$uFile->file) {
                if (!is_null($customFile)) {
                    $uFile->file = $customFile;
                } else {
                    return false;
                }
            }
            $authorId = $model->getAuthorId();
            $uFile->author_id = $authorId;
            $uFile->path = $model->getImagePath();
            $uFile->name = $model->getImageName();
            $uFile->ext = $uFile->file->extensionName;
            $uFile->size = $uFile->file->size;
            $uFile->type = self::IMAGE_TYPE;
            $uFile->desc = CHtml::encode($desc); // TODO: use purifier(CSafeBehavior)

            $config = $model->imageConfig();


            $folder = self::UPLOAD_PATH . '/' . $uFile->path;

            if (!file_exists($folder))
                mkdir($folder, 0700, true);

            if (!$uFile->getErrors() && $uFile->save()) {

                if ($isFakeUpload && $isAdmin)
                    $moveResult = rename($uFile->file->tempName, $folder . '/' . $uFile->name . '.' . $uFile->ext);
                else
                    $moveResult = $uFile->file->saveAs($folder . '/' . $uFile->name . '.' . $uFile->ext);

                if (!$moveResult)
                    return false;

                if (!empty($config['realtime'])) {
                    foreach ($config['realtime'] as $imgConfig) {
                        $uFile->generatePreview($imgConfig);
                    }
                }
                if (!empty($config['background'])) {
                    $imgInfo = array(
                        'path' => $uFile->path,
                        'name' => $uFile->name,
                        'ext' => $uFile->ext,
                    );
                    Yii::app()->gearman->appendJob('preview_generator', array('imgInfo' => $imgInfo, 'config' => $config['background']));
                }
                $resultArray[] = $uFile;
            } else {
                $model->addErrors($uFile->getErrors());

                if ($returnWithErrors)
                    $resultArray[] = $uFile;
                else
                    return false;
            }

        }
        $model->flushImageType();

        return $resultArray;

    }


    /**
     * Generate preview
     * @param array $config
     */
    public function generatePreview($config, $uploadedFile = null, $useAbsolutePath = false)
    {
        if (is_null($uploadedFile))
            $uf = $this;
        else
            $uf = $uploadedFile;

        $fileName = Yii::app()->basePath . '/../' . self::UPLOAD_PATH . '/' . $uf->path . '/' . $uf->name . '.' . $uf->ext;
        if (!file_exists($fileName)) {
            throw new Exception('Origin image not found', 500);
        }

        $name = $config[0] . 'x' . $config[1] . $config[2] . '_' . $uf->name;
        if (!$useAbsolutePath) {
            $previewName = self::PUBLIC_PREFIX . '/' . $uf->path . '/' . $name . '.jpg';
            $folder = self::PUBLIC_PREFIX . '/' . $uf->path;
        } else {
            $previewName = Yii::app()->basePath . '/../' . self::PUBLIC_PREFIX . '/' . $uf->path . '/' . $name . '.jpg';
            $folder = Yii::app()->basePath . '/../' . self::PUBLIC_PREFIX . '/' . $uf->path;
        }

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
        // image processing
        $imageHandler = new imageHandler($fileName, imageHandler::FORMAT_JPEG);
        $imageHandler->updateColorspace();

        $bestFit = isset($config[4]) ? $config[4] : true;
        $border = isset($config['border']) ? $config['border'] : false;

        if ($config[2] == 'crop') {
            $imageHandler->cropImage($config[0], $config[1], $config[3]);
        } else {

            if (isset($config['decrease']) && $config['decrease'] == true) {
                // Если фотка будет меньше указанного размера, то ресайзиться не будет
                $resizeType = imageHandler::RESIZE_DECREASE;
            } else {
                // Ресайзим
                $resizeType = imageHandler::RESIZE_BOTH;
            }
            $imageHandler->resizeImage($config[0], $config[1], $config[3], $resizeType, $bestFit);

            // Нужен ли водяной знак
            if (isset($config['watermark']) && $config['watermark'] == true) {
                $imageHandler->watemark('img/logo-wm.png');
            }

            // Добавляем обрамление для фотографии
            if ($border)
                $imageHandler->addTransparentBorder($config[0], $config[1]);
        }
        $imageHandler->saveImage($previewName);
    }

    /**
     * Remove all preview for current origin image
     */
    /*public function removeAllPreview()
    {
            $folder = self::PUBLIC_PREFIX .'/'. $this->path . '/';

            $previewConfig = Config::$preview;
            foreach ($previewConfig as $config) {
                    $name = $config[0].'x'.$config[1].$config[2].'_'.$this->name . '.jpg';
                    $previewName = $folder . $name;

                    if (file_exists($previewName)) {
                            unlink($previewName);
                    }
            }
    }*/

    /**
     * Get full filename for preview
     * @param array $config width, height, method
     * @param string $type
     * @return mixed|string
     */
    public function getPreviewName($config, $type = 'default')
    {
        $key = 'UF:PREVIEW:' . $this->id . ':' . serialize($config) . ':' . $type;
        $cachedValue = Yii::app()->cache->get($key);
        if ($cachedValue) {
            return $cachedValue;
        }
        $name = $config[0] . 'x' . $config[1] . $config[2] . '_' . $this->name;
        $previewName = self::PUBLIC_PREFIX . '/' . $this->path . '/' . $name . '.jpg';
        if (!file_exists($previewName)) {
            try {
                // Если оригинал существует, то генерим превью...
                $this->generatePreview($config, null);
            } catch (Exception $e) {
                //...иначе возвращаем дефолтную пикчу
                $name = $config[0] . 'x' . $config[1];
                $previewName = self::getDefaultImage($type, $name);
            }
        }
        Yii::app()->cache->set($key, $previewName);
        return $previewName;
    }

    /**
     * Получение размеров изображения
     * @static
     * @param $fileName путь в файловой системе до файла
     * @param null $param параметр для получение строго одной величины
     * @return array|int
     * @throws CException в случае некорректных параметров
     */
    public static function getImageSize($fileName, $param = null)
    {
        $data = @getimagesize($fileName);

        if (empty($data)) {
            if (is_null($param))
                return array('width' => 0, 'height' => 0);
            else
                return 0;
        }

        if (is_null($param)) {
            return array('width' => $data[0], 'height' => $data[1]);
        } elseif ($param === 'width') {
            return $data[0];
        } elseif ($param === 'height') {
            return $data[1];
        } elseif ($param === 'str') {
            return $data[3];
        }
        throw new CException('Invalid parametr');
    }

    /**
     * Возвращает путь до дефолтной картинки
     * @param string $group (ex. user см. $_preview)
     * @param string $size (ex. 210x210 )
     */
    public static function getDefaultImage($group, $size)
    {
        if (!isset(self::$_preview[$group]))
            $group = 'default';
        if (!isset(self::$_preview[$group][$size]))
            $size = 'default';
        return self::$_preview[$group][$size];
    }

    /**
     * Get filename for original image
     * @return string
     */
    public function getFullname()
    {
        $path = self::UPLOAD_PATH . '/' . $this->path . '/' . $this->name . '.' . $this->ext;


        if (!file_exists($path))
            $path = 'images/interior.jpg';

        return $path;
    }

    /**
     * Возвращает размер файла в Мб
     * @static
     * @param $uf
     * @return float|int
     */
    static public function getFileSize($uf)
    {
        $file = self::model()->findByPk((int)$uf);

        if ($file)
            return round($file->size / 1024 / 1024, 3);
        else
            return 0;

    }

    /**
     * Удаление загруженного файла (Изначально для тендеров)
     */
    public function removeOriginFile()
    {
        $fileName = $this->path . '/' . $this->name . '.' . $this->ext;
        if (file_exists($fileName) && !empty($this->path))
            unlink($fileName);
    }
}