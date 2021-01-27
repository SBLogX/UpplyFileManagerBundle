<?php

declare(strict_types=1);

/**
 * This file is part of a Upply project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Upply\FileManagerBundle\Manager;

use Gaufrette\Exception\FileNotFound;
use Gaufrette\File;
use Gaufrette\FilesystemInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Transliterator;
use UnexpectedValueException;

class FileManager
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var array
     */
    private $upplyDirNames;

    public function __construct(
        ValidatorInterface $validator,
        FilesystemInterface $filesystem,
        array $upplyDirNames
    ) {
        $this->validator = $validator;
        $this->filesystem = $filesystem;
        $this->upplyDirNames = $upplyDirNames;
    }

    /**
     * Check if the given file is valid.
     *
     * @param $file
     *
     * @return ConstraintViolationListInterface
     */
    public function validate($file): ConstraintViolationListInterface
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
                    'multipart/form-data',
                ],
                'maxSize' => '5M',
            ]),
        ]);
    }

    /**
     * Check if a namespace exists.
     *
     * @param string $namespace
     *
     * @return bool
     */
    public function hasNamespace(string $namespace): bool
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
    public function getNamespacePath(string $namespace): string
    {
        if (!$this->hasNamespace($namespace)) {
            throw new UnexpectedValueException(sprintf('Undefined file namespace: \'%s\'', $namespace));
        }

        return $this->upplyDirNames[$namespace]['name'];
    }

    /**
     * Returns the file path.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return string
     */
    public function getFilePath(string $namespace, string $key): string
    {
        return sprintf('%s/%s', $this->getNamespacePath($namespace), $key);
    }

    /**
     * Returns the file stream path.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return string
     */
    public function getFileStreamPath(string $namespace, string $key): string
    {
        return sprintf('gaufrette://upply/%s', $this->getFilePath($namespace, $key));
    }

    /**
     * Reads the content of the file.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return string|bool if cannot read content
     */
    public function read(string $namespace, string $key): string
    {
        return $this->filesystem->read($this->getFilePath($namespace, $key));
    }

    /**
     * Returns the file matching the specified key.
     *
     * @param string $namespace
     * @param string $key
     *
     * @throws FileNotFound
     * @throws InvalidArgumentException If $key is invalid
     *
     * @return File|mixed
     */
    public function get(string $namespace, string $key): File
    {
        return $this->filesystem->get($this->getFilePath($namespace, $key));
    }

    /**
     * Writes the given content into the file.
     *
     * @param string $namespace
     * @param string $key
     * @param string $content
     * @param bool   $override
     *
     * @return int|bool The number of bytes that were written into the file
     */
    public function write(string $namespace, string $key, string $content, bool $override = false): int
    {
        return $this->filesystem->write($this->getFilePath($namespace, $key), $content, $override);
    }

    /**
     * Indicates whether the file exists.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return bool
     */
    public function has(string $namespace, string $key): bool
    {
        return $this->filesystem->has($this->getFilePath($namespace, $key));
    }

    /**
     * Deletes the file.
     *
     * @param string $namespace
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $namespace, string $key): bool
    {
        return $this->filesystem->delete($this->getFilePath($namespace, $key));
    }

    /**
     * Slugify the given string.
     *
     * @param string $string
     * @param mixed  $delimiter
     *
     * @return string
     */
    public static function slugify(string $string, $delimiter = '-'): string
    {
        $clean = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($string);
        $clean = preg_replace("/[^a-zA-Z0-9 ._-]/", '', $clean);
        $clean = mb_strtolower($clean);
        $clean = preg_replace('/[ ]+/', $delimiter, $clean);

        return trim($clean, $delimiter);
    }
}
