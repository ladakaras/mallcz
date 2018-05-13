<?php

require '../api/include/CustomerModel.php';

class CustomerModelTest extends \Codeception\TestCase\Test {

	public function testResourceToDbRow() {
		$dbRow = CustomerModel::resourceToDbRow(['id' => 5, 'first_name' => 'Lada']);

		$this->assertArrayNotHasKey('id', $dbRow);
	}

	public function testResourceToDbRowBadEmail() {
		try {
			CustomerModel::resourceToDbRow(['email' => 'asdsad@sadas']);
			$this->fail('Not throw BadInputException for bad email');
		} catch(BadInputException $exc) {
			$this->assertSame(BadInputException::BAD_EMAIL_FORMAT, $exc->getCode());
			$this->assertSame('email', $exc->getInput());
		}
	}

	public function testResourceToDbRowLongEmail() {
		try {
			CustomerModel::resourceToDbRow(['email' => str_repeat('a', 248) . '@seznam.cz']);
			$this->fail('Not throw BadInputException for long email');
		} catch(BadInputException $exc) {
			$this->assertSame(BadInputException::MAX_LENGHT, $exc->getCode());
			$this->assertSame('email', $exc->getInput());
			$this->assertSame([255], $exc->getParams());
		}
	}

	public function testResourceToDbRowLongFirstName() {
		try {
			CustomerModel::resourceToDbRow(['first_name' => str_repeat('a', 61)]);
			$this->fail('Not throw BadInputException for long first_name');
		} catch(BadInputException $exc) {
			$this->assertSame(BadInputException::MAX_LENGHT, $exc->getCode());
			$this->assertSame('first_name', $exc->getInput());
			$this->assertSame([60], $exc->getParams());
		}
	}

	public function testResourceToDbRowLongLastName() {
		try {
			CustomerModel::resourceToDbRow(['last_name' => str_repeat('a', 61)]);
			$this->fail('Not throw BadInputException for long last_name');
		} catch(BadInputException $exc) {
			$this->assertSame(BadInputException::MAX_LENGHT, $exc->getCode());
			$this->assertSame('last_name', $exc->getInput());
			$this->assertSame([60], $exc->getParams());
		}
	}

	public function testDbRowToResource() {
		$resource = CustomerModel::dbRowToResource(['id' => '5', 'first_name' => 'Lada', 'date_registrated' => '2017-05-03 17:03:02']);

		$this->assertSame(5, $resource['id']);
		$this->assertSame('2017-05-03T17:03:02.000Z', $resource['date_registrated']);
	}

}
