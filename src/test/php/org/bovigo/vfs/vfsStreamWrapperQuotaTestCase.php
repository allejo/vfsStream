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
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for quota related functionality of org\bovigo\vfs\vfsStreamWrapper.
 *
 * @group  issue_35
 */
class vfsStreamWrapperQuotaTestCase extends TestCase
{
    /**
     * access to root
     *
     * @type  vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        vfsStream::setQuota(10);
    }

    /**
     * @test
     */
    public function writeLessThanQuotaWritesEverything()
    {
        assertThat(file_put_contents(vfsStream::url('root/file.txt'), '123456789'), equals(9));
        assertThat($this->root->getChild('file.txt')->getContent(), equals('123456789'));
    }

    /**
     * @test
     */
    public function writeUpToQotaWritesEverything()
    {
        assertThat(file_put_contents(vfsStream::url('root/file.txt'), '1234567890'), equals(10));
        assertThat($this->root->getChild('file.txt')->getContent(), equals('1234567890'));
    }

    /**
     * @test
     */
    public function writeMoreThanQotaWritesOnlyUpToQuota()
    {
        expect(function() {
            file_put_contents(vfsStream::url('root/file.txt'), '12345678901');
        })->triggers()
          ->withMessage('file_put_contents(): Only 10 of 11 bytes written, possibly out of free disk space');

        assertThat($this->root->getChild('file.txt')->getContent(), equals('1234567890'));
    }

    /**
     * @test
     */
    public function considersAllFilesForQuota()
    {
        vfsStream::newFile('foo.txt')
             ->withContent('foo')
             ->at(vfsStream::newDirectory('bar')->at($this->root));
        expect(function() {
            file_put_contents(vfsStream::url('root/file.txt'), '12345678901');
        })->triggers()
          ->withMessage('file_put_contents(): Only 7 of 11 bytes written, possibly out of free disk space');

        assertThat($this->root->getChild('file.txt')->getContent(), equals('1234567'));
    }

    /**
     * @test
     * @group  issue_33
     */
    public function truncateToLessThanQuotaWritesEverything()
    {
        $fp = fopen(vfsStream::url('root/file.txt'), 'w+');
        assertTrue(ftruncate($fp, 9));
        fclose($fp);
        assertThat($this->root->getChild('file.txt')->size(), equals(9));
        assertThat(
            $this->root->getChild('file.txt')->getContent(),
            equals("\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_33
     */
    public function truncateUpToQotaWritesEverything()
    {
        $fp = fopen(vfsStream::url('root/file.txt'), 'w+');
        assertTrue(ftruncate($fp, 10));
        fclose($fp);
        assertThat($this->root->getChild('file.txt')->size(), equals(10));
        assertThat(
            $this->root->getChild('file.txt')->getContent(),
            equals("\0\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_33
     */
    public function truncateToMoreThanQotaWritesOnlyUpToQuota()
    {
        $fp = fopen(vfsStream::url('root/file.txt'), 'w+');
        assertTrue(ftruncate($fp, 11));
        fclose($fp);
        assertThat($this->root->getChild('file.txt')->size(), equals(10));
        assertThat(
            $this->root->getChild('file.txt')->getContent(),
            equals("\0\0\0\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_33
     */
    public function truncateConsidersAllFilesForQuota()
    {
        vfsStream::newFile('bar.txt')
                 ->withContent('bar')
                 ->at(vfsStream::newDirectory('bar')
                               ->at($this->root)
                   );
        $fp = fopen(vfsStream::url('root/file.txt'), 'w+');
        assertTrue(ftruncate($fp, 11));
        fclose($fp);
        assertThat($this->root->getChild('file.txt')->size(), equals(7));
        assertThat(
            $this->root->getChild('file.txt')->getContent(),
            equals("\0\0\0\0\0\0\0")
        );
    }

    /**
     * @test
     * @group  issue_33
     */
    public function canNotTruncateToGreaterLengthWhenDiscQuotaReached()
    {
        vfsStream::newFile('bar.txt')
                 ->withContent('1234567890')
                 ->at(vfsStream::newDirectory('bar')
                               ->at($this->root)
                   );
        $fp = fopen(vfsStream::url('root/file.txt'), 'w+');
        assertFalse(ftruncate($fp, 11));
        fclose($fp);
        assertThat($this->root->getChild('file.txt')->size(), equals(0));
        assertThat(
            $this->root->getChild('file.txt')->getContent(),
            equals('')
        );
    }
}
