<?php

namespace li3_filesystem\extensions\adapter\storage\filesystem;

/**
 * Lithium Filesystem: managing file uploads the easy way
 *
 * @copyright     Copyright 2012, Little Boy Genius (http://www.littleboygenius.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\Libraries;

/**
 * A File Filesystem adapter implementation. Requires
 * writable folder on filesystem for example webroot\uploads
 *
 * The `File` filesystem adapter is meant to be used through the `FileSystem` interface,
 * which abstracts away file writting, adapter instantiation and filter
 * implementation.
 *
 * A simple configuration of this adapter can be accomplished in `config/bootstrap/filesystem.php`
 * as follows:
 *
 * {{{
 * FileSystem::config(array(
 *     'filesystem-config-name' => array(
 *         'adapter' => 'File',
 *         'path' => '/webroot/img',
 *     )
 * ));
 * }}}
 */

class File extends \lithium\core\Object {

	/**
	 * Class constructor.
	 *
	 * @see app\extensions\storage\FileSystem::config()
	 * @param array $config Configuration parameters for this filesystem adapter. These settings are
	 *        indexed by name and queryable through `FileSystem::config('name')`.
	 *        The defaults are:
	 *        - 'path' : Path where uploaded files live `LITHIUM_APP_PATH . '/webroot/uploads'`.
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'path' => Libraries::get(true, 'path') . '/webroot/uploads'
		);
		parent::__construct($config + $defaults);
	}

	/**
     * @param string $filename
     * @param string $data
     * @param array $options
     * @return mixed returns filename or false otherwise.
     */
	public function write($filename, $data, array $options = array()) {
		$path = $this->_config['path'];

		return function($self, $params) use (&$path) {
			$data = $params['data'];
			$path = "{$path}/{$params['filename']}";

			if (!file_exists(dirname($path))) {
				mkdir(dirname($path), 0775, true);
			}

			if (file_put_contents($path, $data)) {
				return $path;
			}

			return false;
		};
	}

	/**
     * @param string $filename
     * @return string|boolean
     */
	public function read($filename) {
		$path = $this->_config['path'];

		return function($self, $params) use (&$path) {
			if ($params['filename'][0] == "/") {
				$path = "{$path}{$params['filename']}";
			} else {
				$path = "{$path}/{$params['filename']}";
			}

				return file_get_contents($path);

			return false;
		};
	}

	/**
     * @param string $filename
     * @return boolean
     */
	public function delete($filename) {
		$path = $this->_config['path'];
		return function($self, $params) use (&$path) {
			$path = "{$path}/{$params['filename']}";

			if (file_exists($path)) {
				if (is_dir($path)) {
					return rmdir($path);
				}
				if (is_file($path)) {
					return unlink($path);
				}
			}

			return false;
		};
	}

	/**
	 * @param string $filename
	 * @return boolean
	 */
	public function exists($filename) {
		$path = $this->_config['path'];
		return function($self, $params) use (&$path) {
			$path = "{$path}/{$params['filename']}";
			clearstatcache(true, $path);
			return file_exists($path);
		};
	}

	/**
	 * @param string $filename
	 * @return boolean
	 */
	public function makeDir($filename, $params = array()) {
		$path = $this->_config['path'];
		return function($self, $params) use (&$path) {
			$params += array(
				'mode' => 0777,
				'recursive' => true
			);
			extract($params);
			$path = "{$path}/{$params['filename']}";
			if (!file_exists($path)) {
				mkdir($path, $mode, $recursive);
				return file_exists($path);
			}
			return true;
		};
	}

	/**
	 * @param string $filename
	 * @return boolean
	 */
	public function getImageSize($filename, $params = array()) {
		$path = $this->_config['path'];
		return function($self, $params) use (&$path) {
			extract($params);
			$path = "{$path}/{$params['filename']}";
			if (!file_exists($path)) {
				return getimagesize($path);
			}
			return array(0,0);
		};
	}

	public function getExifData($filename, $params = array()) {
		$path = $this->_config['path'];
		return function($self, $params) use (&$path) {
			extract($params);
			$path = "{$path}/{$params['filename']}";
			return exif_read_data($path);
		};
	}
}

?>
