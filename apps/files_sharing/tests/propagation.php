<?php
/**
 * ownCloud
 *
 * @author Robin Appelman
 * @copyright 2015 Robin Appelman <icewind@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files_sharing\Tests;

use OC\Files\View;

class Propagation extends TestCase {
	public function testEtagPropagationSingleUserShare() {
		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$view1 = new View('/' . self::TEST_FILES_SHARING_API_USER1 . '/files');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER2);
		$view2 = new View('/' . self::TEST_FILES_SHARING_API_USER2 . '/files');
		$view2->mkdir('/sharedfolder/subfolder');
		$view2->file_put_contents('/sharedfolder/subfolder/foo.txt', 'bar');

		$sharedFolderInfo = $view2->getFileInfo('/sharedfolder');
		\OCP\Share::shareItem('folder', $sharedFolderInfo->getId(), \OCP\Share::SHARE_TYPE_USER, self::TEST_FILES_SHARING_API_USER1, 31);
		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$this->assertTrue($view1->file_exists('/sharedfolder/subfolder/foo.txt'));

		$rootInfo = $view1->getFileInfo('');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER2);
		$view2->file_put_contents('/sharedfolder/subfolder/foo.txt', 'foobar');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$newRootInfo = $view1->getFileInfo('');
		$this->assertNotEquals($rootInfo->getEtag(), $newRootInfo->getEtag());
	}

	public function testEtagPropagationGroupShare() {
		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$view1 = new View('/' . self::TEST_FILES_SHARING_API_USER1 . '/files');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER2);
		$view2 = new View('/' . self::TEST_FILES_SHARING_API_USER2 . '/files');
		$view2->mkdir('/sharedfolder/subfolder');
		$view2->file_put_contents('/sharedfolder/subfolder/foo.txt', 'bar');

		$sharedFolderInfo = $view2->getFileInfo('/sharedfolder');
		\OCP\Share::shareItem('folder', $sharedFolderInfo->getId(), \OCP\Share::SHARE_TYPE_GROUP, 'group', 31);
		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$this->assertTrue($view1->file_exists('/sharedfolder/subfolder/foo.txt'));

		$rootInfo = $view1->getFileInfo('');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER2);
		$view2->file_put_contents('/sharedfolder/subfolder/foo.txt', 'foobar');

		$this->loginAsUser(self::TEST_FILES_SHARING_API_USER1);
		$newRootInfo = $view1->getFileInfo('');
		$this->assertNotEquals($rootInfo->getEtag(), $newRootInfo->getEtag());
	}
}
