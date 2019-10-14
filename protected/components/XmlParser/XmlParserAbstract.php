<?php

abstract class XmlParserAbstract
{
    protected $_filePath;
    protected $_object;
    protected $_categoryMapping;

    public function initCategoryMapper($file)
    {
        $this->_categoryMapping = json_decode(file_get_contents(__DIR__ . "/data/$file"), true);
    }

    public function getMappedCategory($store_cid)
    {
        foreach ($this->_categoryMapping as $mapper) {
            if ($mapper['store_cid'] == $store_cid) {
                return $mapper['mh_cid'];
            }
        }
        return null;
    }

    public function setFile($file)
    {
        if (file_exists($file)) {
            $this->_filePath = $file;
        } else {
            throw new CException('Invalid file path ' . $file);
        }
    }

    public function load()
    {
        try {
            $this->_object = simplexml_load_file($this->_filePath);
        } catch (Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function cleanup()
    {
        unset($this->_object);
        unlink($this->_filePath);
    }

    public function getProductByStoreInnerId($storeInnerId, $storeId)
    {
        return Product::model()->findByAttributes([
            'store_inner_id' => $storeInnerId,
            'store_id' => $storeId,
        ]);
    }

    public function getVendor($offer, $countryId = null)
    {
        $vendor = Vendor::model()->findByAttributes(['name' => (string) $offer->vendor]);
        if ($vendor && !$vendor->country_id && $countryId) {
            $vendor->country_id = (int) $countryId;
            $vendor->save();
        }
        if (!$vendor) {
            $vendor = new Vendor();
            $vendor->name = (string) $offer->vendor;
            $vendor->user_id = 0;
            $vendor->country_id = (int) $countryId;
            $vendor->save();
        }
        return $vendor;
    }

    public function imageUrlUpload(Product $product, $url)
    {
        /**
         * Создание темпового файла
         */
        $tempFile = tempnam(sys_get_temp_dir(), 'php');

        /**
         * Загрузка файла в темповый
         */
        $fp = fopen($tempFile, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $result = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if ( !$result )
            return false;

        /**
         * Определение имени файла
         */
        $urlInfo = parse_url($url);
        $filePathInfo = pathinfo($urlInfo['path']);

        $product->setImageType('product');
        try {
            $file = UploadedFile::loadImage2($product, $tempFile, $filePathInfo);
        } catch (Exception $e) {
            $file = null;
            echo $e->getMessage() . "\n";
        }

        if (!$file) {
            return false;
        }

        if ( !$product->image_id ) {
            $product->image_id = $file->id;
            $product->save(false);

        } else {
            Yii::app()->dbcatalog2->createCommand()
                ->insert('cat_product_image', array('product_id'=>$product->id, 'file_id'=>$file->id));
        }
        return true;
    }
}