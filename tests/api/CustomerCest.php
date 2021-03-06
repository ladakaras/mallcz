<?php

class CustomerCest {

	public function listApi(ApiTester $I) {
		$I->sendGET('/customers');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson([['id' => 1, 'first_name' => 'Ladislav', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']]);
		$I->seeResponseMatchesJsonType(['id' => 'integer']);
	}

	public function getApi(ApiTester $I) {
		$I->sendGET('/customers/1');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Ladislav', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com', 'date_registrated' => '2018-05-12T18:05:00.000Z']);
		$I->seeResponseMatchesJsonType(['id' => 'integer']);
	}

	public function getNotExistApi(ApiTester $I) {
		$I->sendGET('/customers/3');
		$this->_notFound($I);
	}

	public function postApi(ApiTester $I) {
		$I->sendPOST('/customers', json_encode(['first_name' => 'Eva', 'last_name' => 'Karasová', 'email' => 'EvaRodinova@gmail.com']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(201);
		$I->seeResponseMatchesJsonType(['id' => 'integer']);
		$I->seeResponseContainsJson(['id' => 3, 'first_name' => 'Eva', 'last_name' => 'Karasová', 'email' => 'EvaRodinova@gmail.com']);
	}

	public function getInsertedApi(ApiTester $I) {
		$I->sendGET('/customers/3');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => 3, 'first_name' => 'Eva', 'last_name' => 'Karasová', 'email' => 'EvaRodinova@gmail.com']);
	}
	
	public function postBadEmailApi(ApiTester $I) {
		$I->sendPOST('/customers', json_encode(['first_name' => 'Test', 'last_name' => 'Test', 'email' => 'not.email@com']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(400);
		$I->seeResponseContainsJson(['message' => 'Input `email` must be valid email address.']);
	}
	
	public function postOverflowFirstNameApi(ApiTester $I) {
		$I->sendPOST('/customers', json_encode(['first_name' => str_repeat('0', 61), 'last_name' => 'Test', 'email' => 'not.email@com']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(400);
		$I->seeResponseContainsJson(['message' => 'Input `first_name` must be maximal 60 chars lenght.']);
	}
	
	public function postOverflowLastNameApi(ApiTester $I) {
		$I->sendPOST('/customers', json_encode(['first_name' => 'Test', 'last_name' => str_repeat('0', 61), 'email' => 'not.email@com']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(400);
		$I->seeResponseContainsJson(['message' => 'Input `last_name` must be maximal 60 chars lenght.']);
	}
	
	public function postOverflowEmailApi(ApiTester $I) {
		$I->sendPOST('/customers', json_encode(['first_name' => 'Test', 'last_name' => 'Test', 'email' => str_repeat('a', 250).'@a.com']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(400);
		$I->seeResponseContainsJson(['message' => 'Input `email` must be maximal 255 chars lenght.']);
	}

	public function putApi(ApiTester $I) {
		$I->sendPUT('/customers/1', json_encode(['first_name' => 'Lada']));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Lada', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']);
	}

	public function putNotExistApi(ApiTester $I) {
		$I->sendPUT('/customers/4', json_encode(['first_name' => 'Neexistujici']));
		$this->_notFound($I);
	}

	public function putTryChangeIdApi(ApiTester $I) {
		$I->sendPUT('/customers/1', json_encode(['first_name' => 'Lada', 'id' => 5]));
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Lada', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']);
		
		$I->sendGET('/customers/1');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Lada', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']);
		
		$I->sendGET('/customers/5');
		$this->_notFound($I);
	}

	public function deleteApi(ApiTester $I) {
		$I->sendDELETE('/customers/1');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['message' => 'Customer deleted.']);
	}

	public function deleteNotExistApi(ApiTester $I) {
		$I->sendDELETE('/customers/4');
		$this->_notFound($I);
	}

	public function getDeletedApi(ApiTester $I) {
		$I->sendGET('/customers/1');
		$this->_notFound($I);
	}

	private function _notFound(ApiTester $I) {
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(404);
		$I->seeResponseContainsJson(['message' => 'Customer not found.']);
	}
}
