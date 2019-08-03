<?php

namespace Moonlight\Properties;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Exception;

class FileProperty extends BaseProperty
{
    protected $folderName = null;
    protected $hash = null;
    protected $folderPath = null;
    protected $folderWebPath = null;
    protected $assetsName = 'assets';
    protected $maxSize = 2048;
    protected $allowedMimeTypes = [
        'txt', 'pdf', 'xls', 'xlsx', 'ppt', 'doc', 'docx', 'xml', 'rtf',
        'gif', 'jpeg', 'pjpeg', 'png', 'tiff', 'ico',
        'zip', 'rar', 'tar',
    ];
    protected $driver = null;
    protected $driverFolderName = 'documents';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->
        addRule('max:'.$this->maxSize, 'Максимальный размер файла: '.$this->maxSize.' Кб')->
        addRule('mimes:'.join(',', $this->allowedMimeTypes), 'Недопустимый формат файла');

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

    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    public function path()
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename();
            return Storage::disk($this->driver)->url($filename);
        }

        return asset(
            $this->getAssetsName()
            .'/'
            .$this->getFolderName()
            .'/'
            .$this->getValue()
        );
    }

    public function abspath()
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename();
            return $this->getValue() && Storage::disk($this->driver)->url($filename);
        }

        return
            public_path()
            .DIRECTORY_SEPARATOR.$this->getAssetsName()
            .DIRECTORY_SEPARATOR.$this->getFolderName()
            .DIRECTORY_SEPARATOR.str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $this->getValue()
            );
    }

    public function filename()
    {
        return basename($this->getValue());
    }

    public function filesize()
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename();
            return Storage::disk($this->driver)->size($filename);
        }

        return $this->exists() ? filesize($this->abspath()) : 0;
    }

    public function filesize_kb($precision = 0)
    {
        return round($this->filesize() / 1024, $precision);
    }

    public function filesize_mb($precision = 0)
    {
        return round($this->filesize() / 1024 / 1024, $precision);
    }

    public function exists()
    {
        if ($this->driver) {
            $filename = $this->getDriverFilename();
            return $this->getValue() && Storage::disk($this->driver)->exists($filename);
        }

        return $this->getValue() && file_exists($this->abspath());
    }

    public function folder_path()
    {
        return dirname($this->abspath());
    }

    public function folder_exists()
    {
        return is_dir($this->folder_path());
    }

    public function buildInput()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        return $request->file($name);
    }

    public function set()
    {
        $request = $this->getRequest();
        $name = $this->getName();

        if ($request->hasFile($name)) {
            $file = $request->file($name);

            if ($file->isValid() && $file->getMimeType()) {
                $this->drop();

                $original = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                if (! $extension) $extension = 'txt';

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

                $filename = sprintf('%s_%s.%s',
                    $name,
                    $hash,
                    $extension
                );

                $value = $folderHash
                    ? $folderHash.'/'.$filename
                    : $filename;

                $this->setValue($value);

                if ($this->driver) {
                    Storage::disk($this->driver)->putFileAs(
                        $this->driverFolderName,
                        $file,
                        $filename
                    );

                    unlink($path);
                } else {
                    $file->move($destination, $filename);
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
    }

    public function getListView()
    {
        $exists = $this->exists();

        if (! $exists) {
            return [
                'exists' => false,
                'src' => null,
                'width' => null,
                'height' => null,
            ];
        }

        $scope = [
            'exists' => $exists,
            'path' => $this->path(),
            'filename' => $this->filename(),
            'filesize' => $this->filesize_kb(1),
        ];

        return $scope;
    }

    public function getEditView()
    {
        $exists = $this->exists();

        if (! $exists) {
            $scope = [
                'name' => $this->getName(),
                'title' => $this->getTitle(),
                'readonly' => $this->getReadonly(),
                'maxFilesize' => $this->getMaxSize(),
                'value' => null,
                'exists' => false,
                'path' => null,
                'filesize' => null,
                'filename' => null,
            ];

            return $scope;
        }

        $scope = [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'readonly' => $this->getReadonly(),
            'maxFilesize' => $this->getMaxSize(),
            'value' => $this->getValue(),
            'exists' => $exists,
            'path' => $this->path(),
            'filesize' => $this->filesize_kb(null, 1),
            'filename' => $this->filename(),

        ];

        return $scope;
    }
}
