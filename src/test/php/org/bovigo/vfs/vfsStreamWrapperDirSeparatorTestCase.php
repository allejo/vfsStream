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
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test that using windows directory separator works correct.
 *
 * @since  0.9.0
 * @group  issue_8
 */
class vfsStreamWrapperDirSeparatorTestCase extends TestCase
{
    /**
     * root diretory
     *
     * @var  vfsStreamDirectory
     */
    protected $root;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @test
     */
    public function fileCanBeAccessedUsingWinDirSeparator()
    {
        $structure = ['foo' => ['bar' => []]];
        vfsStream::create($structure, $this->root);
        vfsStream::newFile('baz.txt')
                 ->at($this->root->getChild('foo')->getChild('bar'))
                 ->withContent('test');
        assertThat(file_get_contents('vfs://root/foo\bar\baz.txt'), equals('test'));
    }


    /**
     * @test
     */
    public function directoryCanBeCreatedUsingWinDirSeparator()
    {
        mkdir('vfs://root/dir\bar\foo', 0777, true);
        assertTrue($this->root->hasChild('dir'));
        assertTrue($this->root->getChild('dir')->hasChild('bar'));
        assertTrue($this->root->getChild('dir/bar')->hasChild('foo'));
    }

    /**
     * @test
     */
    public function directoryExitsTestUsingTrailingWinDirSeparator()
    {
        $structure = ['dir' => ['bar' => []]];
        vfsStream::create($structure, $this->root);
        assertTrue(file_exists(vfsStream::url('root/').'dir\\'));
    }
}
