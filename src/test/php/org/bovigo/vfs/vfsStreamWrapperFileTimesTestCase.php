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
use function bovigo\assert\predicate\equals;
/**
 * Test for org\bovigo\vfs\vfsStreamWrapper.
 *
 * @since  0.9.0
 */
class vfsStreamWrapperFileTimesTestCase extends TestCase
{
    private $root;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup()
             ->lastModified(50)
             ->lastAccessed(50)
             ->lastAttributeModified(50);
    }

    /**
     * @test
     */
    public function filemtimeEqualStreamTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100);
        assertThat(filemtime($file->url()), equals($file->filemtime()));
    }

    /**
     * @test
     */
    public function fileatimeEqualStreamTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastAccessed(100);
        assertThat(fileatime($file->url()), equals($file->fileatime()));
    }

    /**
     * @test
     */
    public function filectimeEqualStreamTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastAttributeModified(100);
        assertThat(filectime($file->url()), equals($file->filectime()));
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function openFileChangesAttributeTimeOnly()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100)
             ->lastAccessed(100)
             ->lastAttributeModified(100);
        fclose(fopen($file->url(), 'rb'));
        assertThat(fileatime($file->url()), equals(time(), 2));
        assertThat(filemtime($file->url()), equals(100));
        assertThat(filectime($file->url()), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function fileGetContentsChangesAttributeTimeOnly()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100)
             ->lastAccessed(100)
             ->lastAttributeModified(100);
        file_get_contents($file->url());
        assertThat(fileatime($file->url()), equals(time(), 2));
        assertThat(filemtime($file->url()), equals(100));
        assertThat(filectime($file->url()), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     * @group  issue_26
     */
    public function openFileWithTruncateChangesAttributeAndModificationTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100)
             ->lastAccessed(100)
             ->lastAttributeModified(100);
        fclose(fopen($file->url(), 'wb'));
        assertThat(fileatime($file->url()), equals(time(), 2));
        assertThat(filemtime($file->url()), equals(time(), 2));
        assertThat(filectime($file->url()), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function readFileChangesAccessTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100)
             ->lastAccessed(100)
             ->lastAttributeModified(100);
        $fp = fopen($file->url(), 'rb');
        $openTime = time();
        sleep(2);
        fread($fp, 1024);
        fclose($fp);
        assertThat(filemtime($file->url()), equals(100));
        assertThat(fileatime($file->url()), equals($openTime + 2, 1));
        assertThat(filectime($file->url()), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function writeFileChangesModificationTime()
    {
        $file = vfsStream::newFile('foo.txt')
             ->at($this->root)
             ->lastModified(100)
             ->lastAccessed(100)
             ->lastAttributeModified(100);
        $fp = fopen($file->url(), 'wb');
        $openTime = time();
        sleep(2);
        fwrite($fp, 'test');
        fclose($fp);
        assertThat(filemtime($file->url()), equals($openTime + 2, 1));
        assertThat(fileatime($file->url()), equals($openTime, 1));
        assertThat(filectime($file->url()), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function createNewFileSetsAllTimesToCurrentTime()
    {
        $url = vfsStream::url('root/foo.txt');
        file_put_contents($url, 'test');
        assertThat(filemtime($url), equals(time(), 1));
        assertThat(fileatime($url), equals(filectime($url)));
        assertThat(fileatime($url), equals(filemtime($url)));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function createNewFileChangesAttributeAndModificationTimeOfContainingDirectory()
    {
        $url = vfsStream::url('root/foo.txt');
        file_put_contents($url, 'test');
        assertThat($this->root->filemtime(), equals(time(), 1));
        assertThat($this->root->filemtime(), equals(time(), 1));
        assertThat($this->root->fileatime(), equals(50));
    }

    /**
     * @test
     * @group  issue_7
     */
    // public function addNewFileNameWithLinkFunctionChangesAttributeTimeOfOriginalFile()
    // {
    //     $this->markTestSkipped('Links are currently not supported by vfsStream.');
    // }

    /**
     * @test
     * @group  issue_7
     */
    // public function addNewFileNameWithLinkFunctionChangesAttributeAndModificationTimeOfDirectoryContainingLink()
    // {
    //     $this->markTestSkipped('Links are currently not supported by vfsStream.');
    // }

    /**
     * @test
     * @group  issue_7
     */
    public function removeFileChangesAttributeAndModificationTimeOfContainingDirectory()
    {
        $file = vfsStream::newFile('baz.txt')
            ->at($this->root)
            ->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        $this->root->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        unlink($file->url());
        assertThat($this->root->filemtime(), equals(time(), 1));
        assertThat($this->root->filemtime(), equals(time(), 1));
        assertThat($this->root->fileatime(), equals(100));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function renameFileChangesAttributeAndModificationTimeOfAffectedDirectories()
    {
        $target = vfsStream::newDirectory('target')
            ->at($this->root)
            ->lastModified(200)
            ->lastAccessed(200)
            ->lastAttributeModified(200);
        $source = vfsStream::newDirectory('bar')->at($this->root);
        $file   = vfsStream::newFile('baz.txt')
            ->at($source)
            ->lastModified(300)
            ->lastAccessed(300)
            ->lastAttributeModified(300);
        $source->lastModified(100)
            ->lastAccessed(100)
            ->lastAttributeModified(100);
        rename($file->url(), vfsStream::url('root/target/baz.txt'));
        assertThat($source->filemtime(), equals(time(), 1));
        assertThat($source->filectime(), equals(time(), 1));
        assertThat($source->fileatime(), equals(100));
        assertThat($target->filemtime(), equals(time(), 1));
        assertThat($target->filectime(), equals(time(), 1));
        assertThat($target->fileatime(), equals(200));
    }

    /**
     * @test
     * @group  issue_7
     */
    public function renameFileDoesNotChangeFileTimesOfFileItself()
    {
        vfsStream::newDirectory('target')->at($this->root);
        $file = vfsStream::newFile('baz.txt')
            ->at($this->root)
            ->lastModified(300)
            ->lastAccessed(300)
            ->lastAttributeModified(300);
        $target = vfsStream::url('root/target/baz.txt');
        rename($file->url(), $target);
        assertThat(filemtime($target), equals(300));
        assertThat(fileatime($target), equals(300));
        assertThat(filectime($target), equals(300));
    }

    /**
     * @test
     * @group  issue_7
     */
    // public function changeFileAttributesChangesAttributeTimeOfFileItself()
    // {
    //     $this->markTestSkipped('Changing file attributes via stream wrapper for self-defined streams is not supported by PHP.');
    // }
}
