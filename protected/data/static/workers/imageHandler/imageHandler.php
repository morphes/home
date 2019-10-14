<?php

/**
 * @brief Обработчикзагружаемых на сайт изображений
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class imageHandler
{
	const FORMAT_JPEG = 'jpeg';
	const FORMAT_PNG = 'png';
	const FORMAT_BMP = 'bmp';
	const FORMAT_GIF = 'gif';

	
	const RESIZE_DECREASE = 1;
	const RESIZE_INCREASE = 2;
	const RESIZE_BOTH = 3;
	/**
	 * @var Imagick
	 */
	private $_image = null;
	/**
	 * @var string
	 */
	private $_extension = null;
	private $_originPath = null;
	private $_format = null;
	private $_isPrepared=false;

	public function __construct($originPath, $format = self::FORMAT_PNG)
	{
		$this->_originPath = $originPath;
		$this->_image = new Imagick($originPath);
		$this->_image->setimageformat($format);
		$extension = pathinfo($originPath, PATHINFO_EXTENSION);
		$this->_extension = strtolower($extension);
		$this->_format = $format;
	}

	/**
	 * Sets image format.
	 * @param string $format
	 */
	public function setImageFormat($format)
	{
		$this->_image->setimageformat($format);
		$this->_format = $format;
		return $this;
	}

	/**
	 * Saves image to file by specified outup path.
	 * @param string $savePath
	 */
	public function saveImage($savePath)
	{
		return $this->_image->writeImage($savePath);
	}

	public function getImage()
	{
		return $this->_image;
	}

	/**
	 * Remove origin file 
	 */
	public function removeOrigin()
	{
		$this->_image->destroy();
		return unlink($this->_originPath);
	}
	
	
	/**
	 * Checks if current resize behavior requires resizing actions.
	 * @param integer $newWidth
	 * @param integer $newHeight
	 * @param integer $resize
	 * @return boolean
	 */
	private function _checkResizeBehavior($newWidth, $newHeight, $resize)
	{
		if ($resize == self::RESIZE_DECREASE) {
			if ($newWidth > $this->_image->getimagewidth() && $newHeight > $this->_image->getimageheight()) {
				return false;
			}
		}
		if ($resize == self::RESIZE_INCREASE) {
			if ($newWidth < $this->_image->getimagewidth() && $newHeight < $this->_image->getimageheight()) {
				return false;
			}
		}
		return true;
	}

	
	/**
	 * Resize image.
	 * @param integer $newWidth
	 * @param integer $newHeight
	 * @param integer $resize Defines behavior for resizing.
	 * @param integer $quality Quality of result image compressing (i.e. JPEG compression quality).
	 */
	public function resizeImage($newWidth, $newHeight, $quality = 100, $resize = self::RESIZE_BOTH, $bestFit=true)
	{
		if (!$this->_checkResizeBehavior($newWidth, $newHeight, $resize)) {
			return $this;
		}
		if ($this->_format == self::FORMAT_JPEG) {
			$this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
		}
		$this->_image->setImageCompressionQuality($quality);
		if ($this->_extension == 'gif') {
			foreach ($this->_image as $frame) {
				$frame->thumbnailImage($newWidth, $newHeight, $bestFit);
				$w = $frame->getImageWidth();
				$h = $frame->getImageHeight();
				$frame->setImagePage($w, $h, 0, 0);
			}
		} else {
			$this->_image->thumbnailimage($newWidth, $newHeight, $bestFit);
		}
		$this->prepareBackground();

		$this->_image->stripImage();
		return $this;
	}

	/**
	 * Делает фон белым
	 */
	private function prepareBackground()
	{
		if ($this->_format == self::FORMAT_JPEG && !$this->_isPrepared) {
			$white=new Imagick();
			$white->newImage($this->_image->getimagewidth(), $this->_image->getimageheight(), "white");
			$white->compositeimage($this->_image, Imagick::COMPOSITE_OVER, 0 , 0);
			$this->_image = $white;
			$this->_isPrepared = true;
		}
	}
	
	/**
	 * Add transparent border to png image
	 * @param integer $width
	 * @param integer $height
	 * @return imageHandler 
	 */
	public function addTransparentBorder($width, $height)
	{
                $imgWidth = $this->_image->getImageWidth();
                $imgHeight = $this->_image->getImageHeight();

                if ($imgWidth < $width || $imgHeight < $height) {
                        $this->_image->frameImage('#ffffffff', ($width - $this->_image->getImageWidth()) / 2,
                                ($height - $this->_image->getImageHeight()) / 2, 0, 0);
                }
                return $this;
	}
	
	/**
	 * Crop images for new size
	 * @param integer $width
	 * @param integer $height
	 * @return imageHandler 
	 */
	public function cropImage($width, $height, $quality = 100)
	{
		$imgWidth = $this->_image->getImageWidth();
		$imgHeight = $this->_image->getImageHeight();

		$dx = $imgWidth / $width;
		$dy = $imgHeight / $height;
		$this->prepareBackground();

		if ($dx > 1 && $dy > 1 ) { // обе стороны исходника больше запрошенных
			if ($dx > $dy) {
				$this->resizeImage($imgWidth, $height, $quality);
			} else {
				$this->resizeImage($width, $imgHeight, $quality);
			}
		} elseif ($dx < 1 && $dy < 1 ) { // обе стороны исходника меньше запрошенных
			$this->addTransparentBorder($width, $height);
			return $this;
		} elseif ($dx < 1 || $dy < 1) { // одна из сторон оригинала меньше либо равна запрошенному
			$this->resizeImage($width, $height, $quality, self::RESIZE_BOTH, true);
			$this->addTransparentBorder($width, $height);
			return $this;
		}

		$x = intval(($this->_image->getImageWidth() - $width) / 2);
		$x = ($x < 0) ? 0 : $x;
		$y = intval(($this->_image->getImageHeight() - $height) / 2);
		$y = ($y < 0) ? 0 : $y;

		$this->_image->cropImage($width, $height, $x, $y);
		return $this;
	}

	/**
	 * Кроп с нужным соотношением сторон, и последующим ресайзом.
	 *
	 * @param $width Ширина области, вырезаемая из оригинальной фотографии
	 * @param $height Высота области, вырезамая из оригинальной фотографии
	 * @param $offset_x Горизонтальное смещение области на оригинальной фотографии отностиельно левого верхнего угла
	 * @param $offset_y Вериткальное смещение области на оригинальной фотографии отностиельно левого верхнего угла
	 * @param $target_w Нужная итоговая ширина изображения
	 * @param $target_h Нужная итоговая высота изображения
	 *
	 * @return imageHandler
	 */
	public function jCrop($width, $height, $offset_x, $offset_y, $target_w, $target_h, $quality = 100)
	{
		if ($this->_format == self::FORMAT_JPEG) {
			$this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
		}
		$this->_image->setImageCompressionQuality($quality);
		
		$this->_image->cropImage($width+3, $height+3, $offset_x, $offset_y);
		$this->_image->thumbnailimage($target_w+3, $target_h+3, true);
		$this->_image->cropImage($target_w, $target_h, 0, 0);

		return $this;
	}

	/**
	 * Усовершенствованный кроп для оригиналов. Вырезает указанную область оригинала
	 *
	 * Величины указываются в диапазоне [0..1]
	 * @param $x x координата области, вырезаемая из оригинальной фотографии
	 * @param $y y координата области, вырезамая из оригинальной фотографии
	 * @param $width Ширина области, вырезаемая из оригинальной фотографии
	 * @param $height Высота области, вырезамая из оригинальной фотографии
	 *
	 * @return imageHandler
	 */
	public function jCrop2($x, $y, $width, $height, $quality = 100)
	{
		if ($this->_format == self::FORMAT_JPEG) {
			$this->_image->setImageCompression(Imagick::COMPRESSION_JPEG);
		}
		$this->_image->setImageCompressionQuality($quality);
		$imgWidth = $this->_image->getImageWidth();
		$imgHeight = $this->_image->getImageHeight();

		$x *= $imgWidth;
		$y *= $imgHeight;
		$width *= $imgWidth;
		$height *= $imgHeight;

		$this->_image->cropImage($width, $height, $x, $y);
		$this->_image->stripImage();

		return $this;
	}

	/**
	 * Возвращает размер изображения в байтах
	 *
	 * @return int Кол-во байт
	 */
	public function getImageSize()
	{
		return $this->_image->getimagelength();
	}

	/**
	 * Добавление водяного знака в правый нижний угол
	 * @param $path путь до водяного знака
	 */
	public function watemark($path)
	{
		$watermark = new Imagick($path);
		$wWidth = $watermark->getImageWidth();
		$wHeight = $watermark->getImageHeight();

		$width = $this->_image->getImageWidth();
		$height = $this->_image->getImageHeight();

		$xOffset = $width - $wWidth - 30;
		$yOffset = $height - $wHeight - 30;
		if ($xOffset < 0)
			$xOffset = 0;
		if ($yOffset < 0)
			$yOffset = 0;

		$this->_image->compositeimage($watermark, Imagick::COMPOSITE_DEFAULT, $xOffset , $yOffset);
		return $this;
	}

        /**
         * Устанавливает sRGB, если colorspace CMYK
         * @return imageHandler
         */
        public function updateColorspace()
        {
                if ($this->_image->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
                        $profiles = $this->_image->getImageProfiles('*', false);
                        // we're only interested if ICC profile(s) exist
                        $has_icc_profile = (array_search('icc', $profiles) !== false);
                        // if it doesnt have a CMYK ICC profile, we add one
                        if ($has_icc_profile === false) {
                                $icc_cmyk = file_get_contents(__DIR__.'/image_profiles/USWebUncoated.icc');
                                $this->_image->profileImage('icc', $icc_cmyk);
                                unset($icc_cmyk);
                        }
                        // then we add an RGB profile
                        $icc_rgb = file_get_contents(__DIR__.'/image_profiles/sRGB_v4_ICC_preference.icc');
                        $this->_image->profileImage('icc', $icc_rgb);
                        unset($icc_rgb);
                }
                $this->_image->stripImage();
                return $this;
        }
}