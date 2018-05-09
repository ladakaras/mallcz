<?php

class CustomerCest {

	public function CustomerListApi(ApiTester $I) {
		$I->sendGET('/customer');
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson([['id' => 1, 'first_name' => 'Ladislav', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']]);
		$I->seeResponseMatchesJsonType(['id' => 'integer']);
	}

	public function CustomerGetApi(ApiTester $I) {
		$I->sendGET('/customer/1');
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Ladislav', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']);
		$I->seeResponseMatchesJsonType(['id' => 'integer']);
	}

	public function CustomerGetNotExistApi(ApiTester $I) {
		$I->sendGET('/customer/3');
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}

	public function CustomerPostApi(ApiTester $I) {
		$I->sendPOST('/customer', json_encode(['first_name' => 'Eva', 'last_name' => 'Karasová', 'email' => 'EvaRodinova@gmail.com']));
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => 3]);
	}

	public function CustomerGetInsertedApi(ApiTester $I) {
		$I->sendGET('/customer/3');
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => 3, 'first_name' => 'Eva', 'last_name' => 'Karasová', 'email' => 'EvaRodinova@gmail.com']);
	}

	public function CustomerPutApi(ApiTester $I) {
		$I->sendPUT('/customer/1', json_encode(['first_name' => 'Lada']));
		$I->seeResponseCodeIs(204);
		$I->seeResponseEquals('');
	}

	public function CustomerGetUpdatedApi(ApiTester $I) {
		$I->sendGET('/customer/1');
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => 1, 'first_name' => 'Lada', 'last_name' => 'Karas', 'email' => 'ladakaras@gmail.com']);
	}

	public function CustomerDeleteApi(ApiTester $I) {
		$I->sendDELETE('/customer/1');
		$I->seeResponseCodeIs(204);
		$I->seeResponseEquals('');
	}

	public function CustomerGetDeletedApi(ApiTester $I) {
		$I->sendGET('/customer/1');
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['message' => 'Customer not found.']);
	}

}
