<?php
/**
 * Služba pro práci s obrázky
 *
 * @package LostPhone
 * @author Petr Sládek <xslade12@stud.fit.vutbr.cz>
 */

namespace App\Services;

use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Image;
use Nette\Utils\Strings;

class ImageService extends Object
{

	/** @var string */
	protected $uploadPath;
	/** @var string */
	protected $cachePath;
	/** @var array */
	protected $imageSizes;
	/** @var string */
	protected $cacheUrl;

	/**
	 * @param string $uploadPath Adresář pro nahrání originálů
	 * @param string $cachePath Adresář pro cachování zmenšenin (musí být do stupné z webu)
	 * @param array $imageSizes Pole s předdefinvoanými zmenšeninami
	 * @param string $cacheUrl URL adresa na složku s cachemi
	 */
	public function __construct($uploadPath, $cachePath, $imageSizes, $cacheUrl) {
        // Přidá lomítka na konec všech složek a url adres
		$this->uploadPath = rtrim($uploadPath,"/")."/";
		$this->cachePath = rtrim($cachePath,"/")."/";
		$this->imageSizes = $imageSizes;
        $this->cacheUrl = rtrim($cacheUrl,"/")."/";
	}

	/**
     * Vrátí cestu ke zmenšenině
     *
	 * @param int $id
	 * @param string $filename
	 * @param string $type
	 * @return string
	 */
	public function getThumbnailPath($id, $filename, $type) {
	return $this->cachePath . $this->getThumbnail($id, $filename, $type);
}

	/**
     * Vrátí URL ke zmenšenině
     *
	 * @param int $id
	 * @param string $filename
	 * @param string $type
	 * @return string
	 */
	public function getThumbnailUrl($id, $filename, $type) {
		return $this->cacheUrl . $this->getThumbnail($id, $filename, $type);
	}

	/**
     * Vygeneruje miniaturu (pokud neexistuje) a vrati nazev vytvoreneho souboru
     *
	 * @param int $id
	 * @param string $filename
	 * @param string $type
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getThumbnail($id, $filename, $type) {
		if(!array_key_exists($type,$this->imageSizes)) {
			throw new InvalidArgumentException("Neznámý typ obrázku (Není nastaven v configu) [$type]");
		}
		$params = $this->imageSizes[$type];
        // Zjistime název souboru pro zmenšeninu
		$thumbnail = $this->getCachePath($id, $filename, $params["width"], $params["height"], $params["flag"]);

        // Pokud zmenšenina ještě neexistuje, vygenerujeme ji z originálu
		if(!file_exists($this->cachePath . $thumbnail)) {
			$this->generate($id, $filename, $params["width"], $params["height"], $params["flag"]);
		}

		return $thumbnail;
	}

	/**
     * Vygeneruje zmenšeninu z originálu
	 * @param $id
	 * @param $filename
	 * @param $width
	 * @param $height
	 * @param $flag
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function generate($id, $filename, $width, $height, $flag) {
        $file = $this->uploadPath.$filename;
		if(!file_exists($file))
			throw new InvalidArgumentException("Obrázek neexistuje [$file]");


		$image = Image::fromFile($this->uploadPath . $filename);
		switch ($flag) {
			case 'fit':
                $image->resize($width,$height, Image::FIT);
            break;
			case 'exact':
                $image->resize($width,$height,Image::EXACT);
            break;
			case 'stretch':
                $image->resize($width,$height,Image::STRETCH);
            break;
			case 'shrink_only':
                $image->resize($width,$height,Image::SHRINK_ONLY);
            break;
			case 'fill':
                $image->resize($width,$height,Image::FILL);
            break;
			case 'centered':
				$image->resize($width,$height,Image::SHRINK_ONLY);
				$place = Image::fromBlank($width,$height, Image::rgb(255,255,255));
                $place->place($image,($width - $image->getWidth()) / 2, ($height - $image->getHeight()) / 2);
                $image = $place;
            break;
		}

		$thumbnail = $this->getCachePath($id, $filename, $width, $height, $flag);
		list($dir1, $dir2) = explode('/', $thumbnail);
		@mkdir( $this->cachePath . implode("/",array($dir1, $dir2)) . "/" , 0777, true);

		$image->save($this->cachePath . $thumbnail);
	}

	/**
     * Vrátí relativní cestu ke zmenšenině
	 * @param int $id
	 * @param string $filename
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $flag
	 * @return string
	 */
	protected function getCachePath($id, $filename, $width=null, $height=null, $flag=null) {
		$cacheName = $this->getCacheName($id, $filename, $width, $height, $flag);
		return substr($cacheName, 0, 2) . "/" . substr($cacheName, 2, 2) . "/" . $cacheName;
	}

	/**
     * Vrátí název souboru pro zmenšeninu
	 * @param int $id
	 * @param string $filename
	 * @param int|null $width
	 * @param int|null $height
	 * @param int|null $flag
	 * @return string
	 */
	protected function getCacheName($id,$filename, $width=null, $height=null, $flag=null) {
		$hash = md5( $id . $filename );
		return substr($hash,0,12)
			. '-' . $id
			. (!empty($width) ? "-{$width}" : "")
			. (!empty($height) ? "x{$height}" : "")
			. (!empty($flag) ? "-{$flag}" : "")
			. '.' . $this->getExtension($filename);
	}

	/**
     * Vrátí příponu z názvu souboru
	 * @param $filename
	 * @return string
	 */
	protected static function getExtension($filename) {
		$name_parts = explode(".",$filename);
		return end($name_parts);
	}


    /**
     * Ulozi uploadovanou fotku
     * @param FileUpload $upload
     * @param null $dir
     * @return \SplFileInfo
     */
    public function uploadImage(FileUpload $upload, $dir = null) {
        if($dir)
            $dir = rtrim($dir, "/")."/";

        // vytvorim sloyzku pokud neexistuje
        $path = $this->uploadPath.$dir;
        @mkdir($path, 0777, true); // vz

        $filename = Strings::lower($upload->getSanitizedName());
        if($dir)
            $filename = $dir.$filename;

        $file = $this->uploadPath.$filename;

        $upload->move($file);

        return new \SplFileInfo($file);
    }



}