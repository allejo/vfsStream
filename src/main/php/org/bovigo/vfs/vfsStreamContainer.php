<?php
declare(strict_types=1);
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
/**
 * Interface for stream contents that are able to store other stream contents.
 */
interface vfsStreamContainer extends \IteratorAggregate
{
    /**
     * adds child to the directory
     *
     * @param  vfsStreamContent  $child
     */
    public function addChild(vfsStreamContent $child);

    /**
     * removes child from the directory
     *
     * @param   string  $name
     * @return  bool
     */
    public function removeChild(string $name): bool;

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild(string $name): bool;

    /**
     * returns the child with the given name
     *
     * @param   string  $name
     * @return  vfsStreamContent|null
     */
    public function getChild(string $name): ?vfsStreamContent;

    /**
     * checks whether directory contains any children
     *
     * @return  bool
     * @since   0.10.0
     */
    public function hasChildren(): bool;

    /**
     * returns a list of children for this directory
     *
     * @return  vfsStreamContent[]
     */
    public function getChildren(): array;
}
