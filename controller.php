<?php defined('C5_EXECUTE') or die('Access Denied.');

class SimpleSelectAttributeTypeController extends AttributeTypeController {

	/**
	 * Tables
	 */
	protected $dbtOptions = 'atSimpleSelectOptions';
	protected $dbtOptionSelected = 'atSimpleSelectOptionSelected';

	protected $searchIndexField = 'X NULL';

	/**
	 * Get Attribute Value
	 */
	public function getValue() {
		$option = $this->getSelectedOption();
		if($option) {
			return $option->getID();
		}
	}

	/**
	 * Get Attribute Value for display
	 */
	public function getDisplayValue() {
		$option = $this->getSelectedOption();
		if($option) {
			return $option->getValue();
		}
	}

	/**
	 * Get Attribute Value for display sanitized
	 */
	public function getDisplaySanitizedValue() {
		$th = Loader::helper('text');
		$option = $this->getSelectedOption();
		if($option) {
			return $th->entities($option->getValue());
		}
	}

	/**
	 * Show search form
	 */
	public function search() {
		$form = Loader::helper('form');
		$this->set('form', $form);
		$options = $this->getOptionsForSelect();
		$options = array('' => t('** All')) + $options;
		$this->set('options', $options);
		$this->set('searchOptionID', $this->request('optionID'));
		$this->set('name', $this->field('optionID'));
	}

	/**
	 * Takes in db list and filters
	 */
	public function searchForm($list) {
		$ak = $this->getAttributeKey();
		$akHandle = $ak->getAttributeKeyHandle();
		$optionID = $this->request('optionID');
		$list->filterByAttribute($akHandle, $optionID, '=');
		return $list;
	}

	public function getSearchIndexValue() {
		return $this->getDisplayValue();
	}
	/**
	 * Show End User Form
	 */
	public function form() {
		$form = Loader::helper('form');
		$this->set('form', $form);
		$options = $this->getOptionsForSelect();
		$this->set('options', $options);
		$selectedOption = $this->getSelectedOption();
		if($selectedOption) {
			$this->set('selectedOption', $selectedOption->getID());
		}
		$this->set('name', $this->field('value'));
	}

	/**
	 * Save end user form
	 * @param array $data
	 */
	public function saveForm($data) {
		$option = SimpleSelectAttributeTypeOption::getByID($data);
		if($option) {
			$avID = $this->getAttributeValueID();
			$option->setAsSelected($avID);
		}
	}

	/**
	 * Save value from setAttribute
	 * @param mixed $value
	 */
	public function saveValue($value) {
		$option = SimpleSelectAttributeTypeOption::getByID($value);
		if($option) {
			$avID = $this->getAttributeValueID();
			$option->setAsSelected($avID);
		}
	}

	/**
	 * Where the magic happens
	 */
	public function saveKey($data) {
		if(!$data) {
			return false;
		}
		if(!$data['akSelectValue']) {
			return false;
		}

		$ak = $this->getAttributeKey();
		$akID = $ak->getAttributeKeyID();

		$db = Loader::db();

		$displayOrder = 0;

		// This is to delete any not included
		$updatedIDs = array();
		$currentIDs = array();
		$res = $db->GetArray(
			'SELECT ID FROM '.$this->dbtOptions.' WHERE akID=?',
			array($akID)
		);
		// Format array for in_array below
		foreach($res as $r) {
			$currentIDs[] = $r['ID'];
		}
		/**
		 * If we have old_ then we update
		 * If new_ then we insert
		 */
		foreach($data['akSelectValue'] as $key => $akSV) {
			if(strpos($key, 'old_') === 0) {
				$db->Execute(
					'UPDATE '.$this->dbtOptions.' SET value=?, displayOrder=? where ID=?',
					array(
						$akSV['value'],
						$displayOrder,
						$akSV['ID']
					)
				);
				$updatedIDs[] = $akSV['ID'];
			} else if(strpos($key, 'new_') === 0) {
				$db->Execute(
					'INSERT INTO '.$this->dbtOptions.' (akID, value, displayOrder) VALUES (?, ?, ?)',
					array(
						$akID,
						$akSV['value'],
						$displayOrder
					)
				);
				$updatedIDs[] = $akSV['ID'];
			}
			$displayOrder++;
		}

		/**
		 * Go through and delete any ids
		 * That weren't updated(meaning deleted)
		 */
		foreach($currentIDs as $currentID) {
			if(!in_array($currentID, $updatedIDs)) {
				$db->Execute(
					'DELETE FROM '.$this->dbtOptions.' where ID=?',
					array($currentID)
				);
			}
		}
	}
	/**
	 * Show Dashboard Form
	 */
	public function type_form() {
		$form = Loader::helper('form');
		$this->set('form', $form);
		$options = $this->getOptions();
		$this->set('options', $options);
	}

	/**
	 * Validate Dashboard Form
	 */
	public function validateForm($data) {
		$optionIDs = $this->getOptionIDs();
		if(!in_array($data, $optionIDs)) {
			return false;
		}
	}

	/**
	 * Copy Attribute Key
	 * @param AttributeKey $newAttributeKey
	 */
	public function duplicateKey($newAttributeKey) {
		$newAkID = $newAttributeKey->getAttributeKeyID();
		$orginalAkID = $this->attributeKey->getAttributeKeyID();
		$db = Loader::db();
		$res = $db->Execute(
			'select ID,value,displayOrder from '.$this->dbtOptions.' where akID=?',
			array($originalAkID)
		);
		while(!$res->EOF) {
			$db->AutoExecute(
				$this->dbtOptions,
				array(
					'ID' => $res->fields['ID'],
					'akID' => $newAkID,
					'value' => $res->fields['value'],
					'displayOrder' => $res->fields['displayOrder']
				),
				'insert'
			);
			$res->MoveNext();
		}
	}

	/**
	 * Delete Attribute Key
	 */
	public function deleteKey() {
		$akID = $this->attributeKey->getAttributeKeyID();
		$db = Loader::db();
		$res = $db->Execute(
			'select ID from '.$this->dbtOptions.' where akID=?',
			array($akID)
		);
		while(!$res->EOF) {
			$db->Execute(
				'delete from '.$this->dbtOptionSelected.' where optionID=?',
				array($res->fields['ID'])
			);
			$res->MoveNext();
		}
	}

	/**
	 * Delete Attribute Value
	 */
	public function deleteValue() {
		$avID = $this->getAttributeValueID();
		$db = Loader::db();
		$db->Execute(
			'delete from '.$this->dbtOptionSelected.' where avID=?',
			array($avID)
		);
	}

	/**
	 * Get array of options
	 */
	public function getOptions() {
		$ak = $this->getAttributeKey();
		if(!$ak) {
			return array();
		}
		$akID = $ak->getAttributeKeyID();

		$options = array();
		$db = Loader::db();
		$res = $db->Execute(
			'select ID from '.$this->dbtOptions.' where akID=? ORDER BY displayOrder',
			array($akID)
		);
		while(!$res->EOF) {
			$option = SimpleSelectAttributeTypeOption::getByID($res->fields['ID']);
			if($option) {
				$options[] = $option;
			}	
			$res->MoveNext();
		}
		return $options;
	}

	/**
	 * Get array of options for user
	 * in form select id => value
	 */
	public function getOptionsForSelect() {
		$options = array();
		$ak = $this->getAttributeKey();
		$akID = $ak->getAttributeKeyID();
		$db = Loader::db();
		$res = $db->Execute(
			'select ID from '.$this->dbtOptions.' where akID=?',
			array($akID)
		);
		while(!$res->EOF) {
			$option = SimpleSelectAttributeTypeOption::getByID($res->fields['ID']);
			if($option) {
				$options[$option->getID()] = $option->getValue();
			}
			$res->MoveNext();
		}
		return $options;
	}

	/**
	 * Get an array of just optionIDs
	 */
	public function getOptionIDs() {
		$options = array();
		$ak = $this->getAttributeKey();
		$akID = $ak->getAttributeKeyID();
		$db = Loader::db();
		$res = $db->Execute(
			'SELECT ID FROM '.$this->dbtOptions.' where akID=?',
			array($akID)
		);
		while(!$res->EOF) {
			$options[] = $res->fields['ID'];
		}
		return $optionIDs;
	}

	/**
	 * Get selected option
	 */
	public function getSelectedOption() {
		$avID = $this->getAttributeValueID();
		$db = Loader::db();
		$optionID = $db->GetOne(
			'select optionID from '.$this->dbtOptionSelected.' where avID=?',
			array($avID)
		);
		$option = SimpleSelectAttributeTypeOption::getByID($optionID);
		return $option;
	}
}

class SimpleSelectAttributeTypeOption extends Object {

	public $ID;
	public $value;
	public $displayOrder;

	private $dbtOptions = 'atSimpleSelectOptions';
	private $dbtOptionSelected = 'atSimpleSelectOptionSelected';

	public function __construct($ID, $value, $displayOrder) {
		$this->ID = $ID;
		$this->value = $value;
		$this->displayOrder = $displayOrder;
	}

	public static function getByID($ID) {
		$db = Loader::db();
		$res = $db->GetRow(
			'select ID, value, displayOrder from atSimpleSelectOptions where ID=?',
			array($ID)
		);
		if($res) {
			$option = new SimpleSelectAttributeTypeOption (
				$res['ID'],
				$res['value'],
				$res['displayOrder']
			);
			return $option;	
		} else {
			return false;
		}
	}

	public function getID() {
		return $this->ID;
	}

	public function setID($ID) {
		if(ctype_digit($ID)) {
			$this->id = $ID;
		} else {
			return false;
		}
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		if(strlen($value) < 255) {
			$this->value = $value;
		} else {
			return false;
		}
	}

	public function getDisplayOrder() {
		return $this->displayOrder;
	}

	public function setOrder() {
		if(ctype_digit($ID)) {
			$this->ID = $ID;
		} else {
			return false;
		}
	}

	public function setAsSelected($avID) {
		$db = Loader::db();
		$db->Replace(
			$this->dbtOptionSelected,
			array(
				'avID' => $avID,
				'optionID' => $this->ID
			),
			'avID',
			true
		);
	}
}
