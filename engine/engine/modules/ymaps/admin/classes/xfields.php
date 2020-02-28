<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */
if (!defined('DATALIFEENGINE')) {
	die("Go fuck yourself!");
}

/**
 * xfClass - класс для управления допполями
 */
class xfClass {

	/**
	 * @var
	 */
	public $result;
	/**
	 * @var string
	 */
	public $xFile;

	/**
	 *
	 */
	function __construct() {
		// конструктор
		$this->xFile = ENGINE_DIR . '/data/xfields.txt';

	}

	/**
	 * @param $type
	 * @param $name
	 * @param $description
	 * @param $value
	 * @param $fieldType
	 *
	 * @return $this
	 */
	public function xf($type, $name, $description, $value, $fieldType) {

		switch ($type) {
			case 'add':
				$this->addField($name, $description, $value, $fieldType);
				$this->result = $this->searchField($name);
				break;
			case 'change':
				$change = $this->changeField($name, $description, $value, $fieldType);

				if ($change) {
					$this->writeXfields($change);
					$this->result = true;
				} else {
					$this->result = false;
				}
				break;
			case 'delete':
				$this->deleteField($name);
				$this->result = (!$this->searchField($name)) ? true : false;

				break;
		}

		return $this;

	}

	/**
	 * @param $name
	 * @param $description
	 * @param $value
	 * @param $fieldType
	 *
	 * @return bool
	 */
	public function addField($name, $description, $value, $fieldType = 'text') {
		if (!$this->searchField($name)) {
			$arr = $this->readXfields();

			array_push($arr, $name . '|' . $description . '||' . $fieldType . '|' . $value . '|1|0|0|1');
			$this->writeXfields($arr);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $text
	 *
	 * @return bool
	 */
	public function searchField($text) {
		$find = false;
		foreach ($this->readXfields() as $v) {
			if (strpos($v, $text) !== false) {
				$find = true;
				break;
			}
		}

		return $find;
	}

	/**
	 * @return array
	 */
	public function readXfields() {
		$filehandle = file_get_contents($this->xFile);

		return explode("\r\n", $filehandle);
	}

	/**
	 * @param $arr
	 *
	 * @return int
	 */
	public function writeXfields($arr, $empty = false) {
		$arr = array_filter($arr);
		$putText = implode("\r\n", $arr);

		return file_put_contents($this->xFile, $putText, LOCK_EX);
	}

	/**
	 * @param $name
	 * @param $description
	 * @param $value
	 * @param $fieldType
	 *
	 * @return array|bool
	 */
	public function changeField($name, $description, $value, $fieldType = 'text') {
		if ($this->searchField($name)) {
			$newXFieldsArr = [];
			foreach ($this->readXfields() as $k => $v) {
				$newXFieldsArr[$k] = $v;
				if (strpos($v, $name) !== false) {
					$newXFieldsArr[$k] = $name . '|' . $description . '||' . $fieldType . '|' . $value . '|1|0|0|1';
				}
			}

			return $newXFieldsArr;
		} else {
			return false;
		}
	}

	/**
	 * @param $name
	 *
	 * @return int
	 */
	public function deleteField($name) {

		$arr = $this->readXfields();
		foreach ($arr as $k => $v) {
			if (strpos($v, $name) !== false) {
				unset($arr[$k]);
			}
		}

		return $this->writeXfields($arr);
	}

	/**
	 * @return mixed
	 */
	public function getResult() {
		return $this->result;
	}

}