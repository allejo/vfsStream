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
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamBlock;

/**
 * Visitor which traverses a content structure recursively to print it to an output stream.
 *
 * @since  0.10.0
 * @see    https://github.com/mikey179/vfsStream/issues/10
 */
class vfsStreamPrintVisitor extends vfsStreamAbstractVisitor
{
    /**
     * target to write output to
     *
     * @var  resource
     */
    protected $out;
    /**
     * current depth in directory tree
     *
     * @var  int
     */
    protected $depth = 0;

    /**
     * constructor
     *
     * If no file pointer given it will fall back to STDOUT.
     *
     * @param   resource  $out  optional
     * @throws  \InvalidArgumentException
     * @api
     */
    public function __construct($out = STDOUT)
    {
        if (!is_resource($out) || get_resource_type($out) !== 'stream') {
            throw new \InvalidArgumentException('Given filepointer is not a resource of type stream');
        }

        $this->out = $out;
    }

    /**
     * visit a file and process it
     *
     * @param   vfsStreamFile  $file
     * @return  vfsStreamPrintVisitor
     */
    public function visitFile(vfsStreamFile $file): vfsStreamVisitor
    {
        $this->printContent($file->getName());
        return $this;
    }

    /**
     * visit a block device and process it
     *
     * @param   vfsStreamBlock  $block
     * @return  vfsStreamPrintVisitor
     */
    public function visitBlockDevice(vfsStreamBlock $block): vfsStreamVisitor
    {
        $name = '[' . $block->getName() . ']';
        $this->printContent($name);
        return $this;
    }

    /**
     * visit a directory and process it
     *
     * @param   vfsStreamDirectory  $dir
     * @return  vfsStreamPrintVisitor
     */
    public function visitDirectory(vfsStreamDirectory $dir): vfsStreamVisitor
    {
        $this->printContent($dir->getName());
        $this->depth++;
        foreach ($dir as $child) {
            $this->visit($child);
        }

        $this->depth--;
        return $this;
    }

    /**
     * helper method to print the content
     *
     * @param  string   $name
     */
    protected function printContent(string $name)
    {
        fwrite($this->out, str_repeat('  ', $this->depth) . '- ' . $name . "\n");
    }
}
