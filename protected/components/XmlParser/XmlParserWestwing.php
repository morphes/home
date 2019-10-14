<?php

class XmlParserWestwing extends XmlParserAbstract implements  XmlParserInterface
{
    const PARSER_ID = 6;

    public function __construct($file)
    {
        Yii::import('application.modules.catalog2.models.*');
        $this->initCategoryMapper('westwing_category_mapping.json');
        $this->setFile($file);
        $this->load();
        $this->validate();
    }

    public function parse($storeId)
    {
        // "блокровка" на время импорта всех ранее загруженных через xml товаров для данного магазина
        Product::model()->updateAll(['status' => Product::STATUS_TEMPORARY], 'store_id=:sid', [':sid' => $storeId]);
        $offersCount = count($this->_object->shop->offers);
        $counter = 0;
        /** @var SimpleXMLElement $offer */
        foreach($this->_object->shop->offers->offer as $offer) {
            //$isAvailable = (bool) (isset($offer->attributes()['available']) ? $offer->attributes()['available'] : false);
            // если нет внутреннего магазинского id товара - пропускаем
            $storeInnerId = (isset($offer->attributes()['id']) ? $offer->attributes()['id'] : null);
            if (!$storeInnerId) {
                continue;
            }
            // если для товара нет замапленной категории - пропускаекм
            $category = Category::model()->findByPk($this->getMappedCategory($offer->categoryId));
            if (!$category) {
                continue;
            }
            $countryId = null;
            // ищем подходящего производителя или создаем нового
            $vendor = $this->getVendor($offer, $countryId);
            // ищем товар в нашей базе по внутреннему id магазина
            $product = $this->getProductByStoreInnerId($storeInnerId, $storeId);
            // если товара не нашли - создаем его
            if (!$product) {
                $product = new Product();
                $product->user_id = 0;
                $product->status = Product::STATUS_ACTIVE;
                $product->category_id = $category->id;
                $product->name = (string) $offer->name;
                $product->vendor_id = $vendor->id;
                $product->store_id = $storeId;
                $product->desc = (isset($offer->description) ? (string) $offer->description : '');
                $product->country = $countryId;
                $product->store_inner_id = $storeInnerId;
                $product->save();
                foreach($offer->picture as $url) {
                    $this->imageUrlUpload($product, (string) $url);
                }
            // иначе - обновляем
            } else {
                $product->country = null; // $this->getCountryId($offer);
                $product->desc = (isset($offer->description) ? (string) $offer->description : '');
                $product->status = Product::STATUS_ACTIVE;
                $product->save();
            }

            // ищем привязку товара к магазину
            $storePrice = StorePrice::model()->findByAttributes([
                'store_id' => $product->store_id,
                'product_id' => $product->id,
            ]);
            // если ее нет - создаем
            if (!$storePrice) {
                $storePrice = new StorePrice();
                $storePrice->store_id = $product->store_id;
                $storePrice->product_id = $product->id;
                $storePrice->price_type = StorePrice::PRICE_TYPE_EQUALLY;
            }
            //if (!$isAvailable) {
            //    $storePrice->status = StorePrice::STATUS_NOT_AVAILABLE;
            //}
            $storePrice->status = StorePrice::STATUS_AVAILABLE;
            $storePrice->url = (string) $offer->url;
            $storePrice->price = (float) $offer->price;
            $storePrice->save();
            $product->updateSphinx();
            $counter++;
            Yii::app()->redis->set("store_{$storeId}_importXml_progress", round($offersCount / 100 * $counter), 60);
            echo "\nImported product #$product->id";
        }

        // удаление товаров, отсутствующих в xml
        /** @var $productsForRemove Product[] */
        $productsForRemove = Product::model()->findAllByAttributes([
            'store_id' => $storeId,
            'status' => Product::STATUS_TEMPORARY,
        ]);
        // удаляем товары, которых больше нет в xml
        foreach ($productsForRemove as $product) {
            $product->status = Product::STATUS_DELETED;
            $product->save();
            $product->updateSphinx();
        }
        Yii::app()->redis->delete("store_{$storeId}_importXml_progress");
        $this->cleanup();
    }

    public function getVendor($offer, $countryId = null)
    {
        $vendor = Vendor::model()->findByAttributes(['name' => (string) $offer->brand]);
        if ($vendor && !$vendor->country_id && $countryId) {
            $vendor->country_id = (int) $countryId;
            $vendor->save();
        }
        if (!$vendor) {
            $vendor = new Vendor();
            $vendor->name = (string) $offer->brand;
            $vendor->user_id = 0;
            $vendor->country_id = (int) $countryId;
            $vendor->save();
        }
        return $vendor;
    }

    public function validate()
    {
        if (!isset($this->_object->shop->offers)) {
            throw new CException('Invalid products data in xml');
        }
    }

    public function getCountryId($offer)
    {
        $mapper = [
            'Российская Федерация' => 3159,
            'Соединенные Штаты Америки' => 5681,
            'Чешская республика' => 10874,
            'Таиланд' => 582050,
        ];
        if (isset($mapper[(string)$offer->country_of_origin])) {
            return $mapper[(string)$offer->country_of_origin];
        }
        $country = Country::model()->findByAttributes(['name'=>(string)$offer->country_of_origin]);
        if (!$country) {
            $country = new Country();
            $country->name = (string)$offer->country_of_origin;
            $country->save();
        }
        return $country->id;
    }
}