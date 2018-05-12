<?php

class CustomerModel {

	const TABLE = 'customer';

	/**
	 * @var Medoo\Medoo
	 */
	private $_database;

	public function __construct(Medoo\Medoo $database) {
		$this->_database = $database;
	}

	/**
	 * Check if customer exist
	 * @param int $id
	 * @return bool
	 */
	public function exist(int $id): bool {
		return $this->_database->has(self::TABLE, ['id' => $id]);
	}

	/**
	 * Prepare data from DB to customer resource
	 * @param array $dbRow
	 * @return array
	 */
	public static function dbRowToResource(array $dbRow): array {
		$resource = $dbRow;

		$resource['id'] = intval($dbRow['id']);

		return $resource;
	}

	/**
	 * Prepare customer data to insert/update data to DB
	 * @param array $resource - customer data
	 * @return array
	 * @throws BadInputException
	 */
	public static function resourceToDbRow(array $resource): array {
		$dbRow = [];

		if(isset($resource['first_name'])) {
			if(strlen($resource['first_name']) > 60) {
				throw BadInputException::newException('first_name', BadInputException::MAX_LENGHT, [60]);
			}
			$dbRow['first_name'] = strval($resource['first_name']);
		}

		if(isset($resource['last_name'])) {
			if(strlen($resource['last_name']) > 60) {
				throw BadInputException::newException('last_name', BadInputException::MAX_LENGHT, [60]);
			}
			$dbRow['last_name'] = strval($resource['last_name']);
		}

		if(isset($resource['email'])) {
			if(strlen($resource['email']) > 255) {
				throw BadInputException::newException('email', BadInputException::MAX_LENGHT, [255]);
			}
			if(!filter_var($resource['email'], FILTER_VALIDATE_EMAIL)) {
				throw BadInputException::newException('email', BadInputException::BAD_EMAIL_FORMAT);
			}
			$dbRow['email'] = strval($resource['email']);
		}

		return $dbRow;
	}

	/**
	 * Return customer
	 * @param int $id
	 * @return array
	 */
	public function get(int $id): array {
		$row = $this->_database->get(self::TABLE, ['id', 'first_name', 'last_name', 'email'], ['id' => $id]);

		return $row === false ? [] : self::dbRowToResource($row);
	}

	/**
	 * Return all customers
	 * @return array
	 */
	public function getAll(): array {
		$rows = $this->_database->select(self::TABLE, ['id', 'first_name', 'last_name', 'email']);

		$return = [];

		foreach($rows as $row) {
			$return[] = self::dbRowToResource($row);
		}

		return $return;
	}

	/**
	 * Insert new customer
	 * @param array $resource
	 * @return int
	 * @throws BadInputException
	 */
	public function insert(array $resource): int {
		$dbRow = self::resourceToDbRow($resource);

		$this->_database->insert(self::TABLE, $dbRow);

		return $this->_database->id();
	}

	/**
	 * Update customer
	 * @param int $id
	 * @param array $resource
	 * @return bool
	 * @throws BadInputException
	 */
	public function update(int $id, array $resource): bool {
		$dbRow = self::resourceToDbRow($resource);

		return $this->_database->update(self::TABLE, $dbRow, ['id' => $id])->rowCount() === 1;
	}

	/**
	 * Delete customer
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool {
		return $this->_database->delete(self::TABLE, ['id' => $id])->rowCount() === 1;
	}

}
