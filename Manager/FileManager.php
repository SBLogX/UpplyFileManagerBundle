<?php

declare(strict_types=1);

/**
 * This file is part of a Upply project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Upply\FileManagerBundle\Manager;

use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileManager
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var FilesystemMap
     */
    private $filesystemMap;

    /**
     * @var string
     */
    private $filesystemName;

    /**
     * @var array
     */
    private $upplyDirNames;

    /**
     * @param ValidatorInterface $validator
     * @param FilesystemMap      $filesystemMap
     * @param array              $upplyDirNames
     * @param string             $filesystemName
     */
    public function __construct(
        ValidatorInterface $validator,
        FilesystemMap $filesystemMap,
        string $filesystemName,
        array $upplyDirNames
    ) {
        $this->validator = $validator;
        $this->filesystemMap = $filesystemMap;
        $this->filesystemName = $filesystemName;
        $this->upplyDirNames = $upplyDirNames;
    }

    /**
     * Check if the given file is valid.
     *
     * @param $file
     *
     * @return Symfony\Component\Validator\ConstraintViolationList
     */
    public function validate($file)
    {
        return $this->validator->validate($file, [
            new Constraints\NotBlank(),
            new Constraints\File([
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/octet-stream',
                    'text/csv',
                ],
                'maxSize' => '5M',
            ]),
        ]);
    }

    /**
     * Returns upply filesystem.
     *
     * @return Gaufrette\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystemMap->get($this->filesystemName);
    }

    /**
     * Check if a namespace exists.
     *
     * @param string $namespace
     *
     * @return bool
     */
    public function hasNamespace(string $namespace)
    {
        return isset($this->upplyDirNames[$namespace]['name']);
    }

    /**
     * Returns namespace path.
     *
     * @param string $namespace
     *
     * @return string
     */
    public function getNamespacePath(string $namespace)
    {
        if (!$this->hasNamespace($namespace)) {
            throw new \UnexpectedValueException(sprintf('Undefined file namespace: \'%s\'', $namespace));
        }

        return $this->upplyDirNames[$namespace]['name'];
    }

    /**
     * Returns the file path.
     *
     * @param string $namespace
     * @param string $key
     * @param bool   $stream
     *
     * @return string
     */
    public function getFilePath(string $namespace, string $key, bool $stream = false)
    {
        if ($stream) {
            return sprintf('gaufrette://%s/%s', $this->filesystemName, $this->getFilePath($namespace, $key));
        }

        return sprintf('%s/%s', $this->getNamespacePath($namespace), $key);
    }

    /**
     * Reads the content of the file.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return string|bool if cannot read content
     */
    public function read(string $namespace, string $key)
    {
        return $this->getFilesystem()->read($this->getFilePath($namespace, $key));
    }

    /**
     * Returns the file matching the specified key.
     *
     * @param string $namespace
     * @param string $key
     *
     * @throws Exception\FileNotFound
     * @throws \InvalidArgumentException If $key is invalid
     *
     * @return File
     */
    public function get(string $namespace, string $key)
    {
        return $this->getFilesystem()->get($this->getFilePath($namespace, $key));
    }

    /**
     * Writes the given content into the file.
     *
     * @param string $namespace
     * @param string $key
     * @param string $content
     *
     * @return int|bool The number of bytes that were written into the file
     */
    public function write(string $namespace, string $key, string $content)
    {
        return $this->getFilesystem()->write($this->getFilePath($namespace, $key), $content);
    }

    /**
     * Indicates whether the file exists.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return bool
     */
    public function has(string $namespace, string $key)
    {
        return $this->getFilesystem()->has($this->getFilePath($namespace, $key));
    }

    /**
     * Deletes the file.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $namespace, string $key)
    {
        return $this->getFilesystem()->delete($this->getFilePath($namespace, $key));
    }

    /**
     * Slugify the given string.
     *
     * @param string $string
     * @param mixed  $delimiter
     *
     * @return string
     */
    public static function slugify(string $string, $delimiter = '-')
    {
        $clean = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($string);
        $clean = preg_replace("/[^a-zA-Z0-9 \._-]/", '', $clean);
        $clean = mb_strtolower($clean);
        $clean = preg_replace('/[ ]+/', $delimiter, $clean);

        return trim($clean, $delimiter);
    }
}
