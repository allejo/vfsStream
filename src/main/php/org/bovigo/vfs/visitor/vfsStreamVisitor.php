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
namespace org\bovigo\vfs\visitor;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamBlock;

/**
 * Interface for a visitor to work on a vfsStream content structure.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
interface vfsStreamVisitor
{
    /**
     * visit a content and process it
     *
     * @param   vfsStreamContent  $content
     * @return  self
     */
    public function visit(vfsStreamContent $content): self;

    /**
     * visit a file and process it
     *
     * @param   vfsStreamFile  $file
     * @return  self
     */
    public function visitFile(vfsStreamFile $file): self;

    /**
     * visit a directory and process it
     *
     * @param   vfsStreamDirectory  $dir
     * @return  self
     */
    public function visitDirectory(vfsStreamDirectory $dir): self;

    /**
     * visit a block device and process it
     *
     * @param   vfsStreamBlock  $block
     * @return  self
     */
    public function visitBlockDevice(vfsStreamBlock $block): self;
}
