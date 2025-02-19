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

use function bovigo\assert\assertFalse;
use function bovigo\assert\expect;
/**
 * Test for permissions related functionality.
 *
 * @group  permissions
 */
class PermissionsTestCase extends TestCase
{
    /**
     * @type  vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup(
            'root',
            null,
            ['test_directory' => ['test.file' => '']]
        );
    }

    /**
     * @test
     * @group  issue_52
     */
    public function canNotChangePermissionWhenDirectoryNotWriteable()
    {
        $this->root->getChild('test_directory')->chmod(0444);
        assertFalse(@chmod(vfsStream::url('root/test_directory/test.file'), 0777));
    }

    /**
     * @test
     * @group  issue_53
     */
    public function canNotChangePermissionWhenFileNotOwned()
    {
        $this->root->getChild('test_directory')->getChild('test.file')->chown(vfsStream::OWNER_USER_1);
        assertFalse(@chmod(vfsStream::url('root/test_directory/test.file'), 0777));
    }

    /**
     * @test
     * @group  issue_52
     */
    public function canNotChangeOwnerWhenDirectoryNotWriteable()
    {
        $this->root->getChild('test_directory')->chmod(0444);
        assertFalse(@chown(vfsStream::url('root/test_directory/test.file'), vfsStream::OWNER_USER_2));
    }

    /**
     * @test
     * @group  issue_53
     */
    public function canNotChangeOwnerWhenFileNotOwned()
    {
        $this->root->getChild('test_directory')->getChild('test.file')->chown(vfsStream::OWNER_USER_1);
        assertFalse(@chown(vfsStream::url('root/test_directory/test.file'), vfsStream::OWNER_USER_2));
    }

    /**
     * @test
     * @group  issue_52
     */
    public function canNotChangeGroupWhenDirectoryNotWriteable()
    {
        $this->root->getChild('test_directory')->chmod(0444);
        assertFalse(@chgrp(vfsStream::url('root/test_directory/test.file'), vfsStream::GROUP_USER_2));
    }

    /**
     * @test
     * @group  issue_53
     */
    public function canNotChangeGroupWhenFileNotOwned()
    {
        $this->root->getChild('test_directory')->getChild('test.file')->chown(vfsStream::OWNER_USER_1);
        assertFalse(@chgrp(vfsStream::url('root/test_directory/test.file'), vfsStream::GROUP_USER_2));
    }

    /**
     * @test
     * @group  issue_107
     * @since  1.5.0
     */
    public function touchOnNonWriteableDirectoryTriggersError()
    {
        $this->root->chmod(0555);
        expect(function() { touch($this->root->url() . '/touch.txt'); })
            ->triggers()
            ->withMessage('Can not create new file in non-writable path root');
    }

    /**
     * @test
     * @group  issue_107
     * @since  1.5.0
     */
    public function touchOnNonWriteableDirectoryDoesNotCreateFile()
    {
        $this->root->chmod(0555);
        assertFalse(@touch($this->root->url() . '/touch.txt'));
        assertFalse($this->root->hasChild('touch.txt'));
    }
}
