<?php

namespace Moonlight\Properties;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Moonlight\Utils\Image;
use Exception;

class ImageProperty extends BaseProperty
{
    protected $folderName = null;
    protected $hash = null;
    protected $folderPath = null;
    protected $folderWebPath = null;
    protected $assetsName = 'assets';
    protected $resize = null;
    protected $resizes = [];
    protected $driver = null;
    protected $driverFolderName = 'images';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->addRule("image", "Здесь должно загружаться изображение");

        return $this;
    }

    public static function create($name)
    {
        return new self($name);
    }

    public function isSortable()
    {
        return false;
    }

    public function setAssetsName($assetsName)
    {
        $this->assetsName = $assetsName;

        return $this;
    }

    public function getAssetsName()
    {
        return $this->assetsName;
    }

    public function getFolderName()
    {
        return method_exists($this->getItemClass(), 'getFolder')
            ? $this->getItemClass()->getFolder()
            : $this->getItemClass()->getTable();
    }

    public function setDriver(string $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setDriverFolderName(string $driverFolderName)
    {
        $this->driverFolderName = $driverFolderName;

        return $this;
    }

    public function getDriverFolderName()
    {
        return $this->driverFolderName;
    }

    public function getDriverFilename($name = null)
    {
        return trim($this->driverFolderName, '/')
            .'/'.$this->getResizeValue();
    }

    public function getResizeValue($name = null)
    {
        return $name
            ? str_replace($this->getName(), $this->getName().'_'.$name, $this->getValue())
            : $this->getValue();
    }

    public function setResize($width, $height, $quality = 100)
    {
        $this->resize = array($width, $height, $quality);

        return $this;
    }

    public function getResize()
    {
        return $this->resize;
    }

    public function addResize($name, $width, $height, $quality)
    {
        $this->resizes[$name] = array($width, $height, $quality);

        return $this;
    }

    public function getResizes()
    {
        return $this->resizes;
    }

    public function src($name = null)
    {
        return $this->path($name);
    }

    public function width($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            $metadata = Storage::disk($this->driver)->getMetadata($filename);
            return $metadata['metadata']['width'] ?? 0;
        }

        if ($this->exists($name)) {
            try {
                list($width, $height, $type, $attr) = getimagesize($this->abspath($name));
                return $width;
            } catch (Exception $e) {
            }
        }

        return 0;
    }

    public function height($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            $metadata = Storage::disk($this->driver)->getMetadata($filename);
            return $metadata['metadata']['height'] ?? 0;
        }

        if ($this->exists($name)) {
            try {
                list($width, $height, $type, $attr) = getimagesize($this->abspath($name));
                return $height;
            } catch (Exception $e) {
            }
        }

        return 0;
    }

    public function path($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            return Storage::disk($this->driver)->url($filename);
        }

        return asset(
            $this->getAssetsName()
            .'/'
            .$this->getFolderName()
            .'/'
            .$this->getResizeValue($name)
        );
    }

    public function abspath($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            return $this->getValue() && Storage::disk($this->driver)->url($filename);
        }

        return
            public_path()
            .DIRECTORY_SEPARATOR.$this->getAssetsName()
            .DIRECTORY_SEPARATOR.$this->getFolderName()
            .DIRECTORY_SEPARATOR.str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $this->getResizeValue($name)
            );
    }

    public function filename($name = null)
    {
        return basename($this->getResizeValue($name));
    }

    public function filesize($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            return Storage::disk($this->driver)->size($filename);
        }

        return $this->exists($name) ? filesize($this->abspath($name)) : 0;
    }

    public function filesize_kb($name = null, $precision = 0)
    {
        return round($this->filesize($name) / 1024, $precision);
    }

    public function filesize_mb($name = null, $precision = 0)
    {
        return round($this->filesize($name) / 1024 / 1024, $precision);
    }

    public function exists($name = null)
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename($name);
            return $this->getValue() && Storage::disk($this->driver)->exists($filename);
        }

        return $this->getValue() && file_exists($this->abspath($name));
    }

    public function folder_path($name = null)
    {
        return dirname($this->abspath($name));
    }

    public function folder_exists($name = null)
    {
        return is_dir($this->folder_path($name));
    }

    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        return $request->file($name);
    }

    public function set($field = null)
    {
        $request = $this->getRequest();
        $name = $this->getName();

        if ($request->hasFile($name)) {
            $file = $request->file($name);

            if ($file->isValid() && $file->getMimeType()) {
                $this->drop();

                $path = $file->getRealPath();
                $original = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                if (! $extension) {
                    return $this;
                }

                $folderPath =
                    public_path()
                    .DIRECTORY_SEPARATOR
                    .$this->getAssetsName()
                    .DIRECTORY_SEPARATOR
                    .$this->getFolderName()
                    .DIRECTORY_SEPARATOR;

                if (! file_exists($folderPath)) {
                    mkdir($folderPath, 0755);
                }

                $folderHash =
                    method_exists($this->element, 'getFolderHash')
                        ? trim(
                        $this->element->getFolderHash(),
                        DIRECTORY_SEPARATOR
                    ) : '';

                $destination = $folderHash
                    ? $folderPath.DIRECTORY_SEPARATOR.$folderHash
                    : $folderPath;

                if (! file_exists($destination)) {
                    mkdir($destination, 0755);
                }

                $hash = substr(md5(rand()), 0, 8);

                foreach ($this->resizes as $resizeName => $resize) {
                    list($width, $height, $quality) = $resize;

                    $resizeFilename = sprintf('%s_%s_%s.%s',
                        $name,
                        $resizeName,
                        $hash,
                        $extension
                    );

                    Image::resizeAndCopy(
                        $path,
                        $destination.DIRECTORY_SEPARATOR.$resizeFilename,
                        $width,
                        $height,
                        $quality
                    );

                    if ($this->driver) {
                        list($width, $height, $type, $attr) = getimagesize(
                            $destination.DIRECTORY_SEPARATOR.$resizeFilename
                        );

                        Storage::disk($this->driver)->putFileAs(
                            $this->driverFolderName,
                            new File($destination.DIRECTORY_SEPARATOR.$resizeFilename),
                            $resizeFilename,
                            [
                                'Metadata' => [
                                    'width' => $width,
                                    'height' => $height,
                                ],
                            ]
                        );

                        unlink($destination.DIRECTORY_SEPARATOR.$resizeFilename);
                    }
                }

                $filename = sprintf('%s_%s.%s',
                    $name,
                    $hash,
                    $extension
                );

                $value = $folderHash
                    ? $folderHash.'/'.$filename
                    : $filename;

                $this->setValue($value);

                if (is_array($this->resize)) {
                    list($width, $height, $quality) = $this->resize;

                    Image::resizeAndCopy(
                        $path,
                        $destination.DIRECTORY_SEPARATOR.$filename,
                        $width,
                        $height,
                        $quality
                    );

                    if ($this->driver) {
                        list($width, $height, $type, $attr) = getimagesize(
                            $destination.DIRECTORY_SEPARATOR.$filename
                        );

                        Storage::disk($this->driver)->putFileAs(
                            $this->driverFolderName,
                            new File($destination.DIRECTORY_SEPARATOR.$filename),
                            $filename,
                            [
                                'Metadata' => [
                                    'width' => $width,
                                    'height' => $height,
                                ],
                            ]
                        );

                        unlink($destination.DIRECTORY_SEPARATOR.$filename);
                    }

                    unlink($path);
                } else {
                    if ($this->driver) {
                        list($width, $height, $type, $attr) = getimagesize($path);

                        Storage::disk($this->driver)->putFileAs(
                            $this->driverFolderName,
                            $file,
                            $filename,
                            [
                                'Metadata' => [
                                    'width' => $width,
                                    'height' => $height,
                                ],
                            ]
                        );

                        unlink($path);
                    } else {
                        $file->move($destination, $filename);
                    }
                }

                $this->element->$name = $value;
            }
        } elseif (
            $request->has($name.'_drop')
            && $request->input($name.'_drop')
        ) {
            $this->drop();

            $this->element->$name = null;
        }

        return $this;
    }

    public function drop()
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename();
            Storage::disk($this->driver)->delete($filename);
        } elseif ($this->exists()) {
            try {
                unlink($this->abspath());
            } catch (\Exception $e) {
            }
        }

        foreach ($this->resizes as $name => $resize) {
            if ($this->driver) {
                $filename = $this->getDriverFilename($name);
                Storage::disk($this->driver)->delete($filename);
            } elseif ($this->exists($name)) {
                try {
                    unlink($this->abspath($name));
                } catch (\Exception $e) {
                }
            }
        }
    }

    public function getListView()
    {
        return $this->exists() ? [
            'exists' => true,
            'src' => $this->src(),
            'width' => $this->width(),
            'height' => $this->height(),
        ] : [
            'exists' => false,
            'src' => null,
            'width' => null,
            'height' => null,
        ];
    }

    public function getEditView()
    {
        $exists = $this->exists();

        if ($exists) {
            $src = $this->src();
            $width = $this->width();
            $height = $this->height();
            $filesize = $this->filesize_kb(null, 1);
            $filename = $this->filename();
        } else {
            $src = null;
            $width = null;
            $height = null;
            $filesize = null;
            $filename = null;
        }

        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'readonly' => $this->getReadonly(),
            'captions' => $this->getCaptions(),
            'exists' => $exists,
            'src' => $src,
            'width' => $width,
            'height' => $height,
            'filesize' => $filesize,
            'filename' => $filename,
        ];
    }
}
