<?php

namespace deBrand\RDWOpenData\Api;

use Exception;

class Client {

	const TIMEOUT = 10;
	const CONNECTION_TIMEOUT = 0;
	const URL = "https://opendata.rdw.nl/resource/{endpoint}.json?kenteken={kenteken}";

	/**
	 * Retrieves license plate data from opendata.rdw.nl
	 *
	 * @param string $license_plate
	 * @param string $endpoint
	 * @return false|array
	 * @throws Exception
	 */
	public static function getData(string $license_plate, string $endpoint): ?array {
		$url = self::getUrl($license_plate, $endpoint);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECTION_TIMEOUT);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);

		$output = false;

		if(curl_errno($ch) == 0) {
			$output = curl_exec($ch);
			if($output) {
				$output = json_decode($output, true);
			}
		}
		curl_close($ch);

		return $output ?: null;
	}

	/**
	 * Validates an endpoint and returns the url for that endpoint with the given license plate
	 *
	 * @param string $license_plate
	 * @param string $endpoint
	 * @return array|string|string[]
	 * @throws Exception
	 */
	protected static function getUrl(string $license_plate, string $endpoint): string {
		if(!in_array($endpoint, Endpoints::ALL)) {
			throw new Exception('Invalid endpoint given: ' . $endpoint);
		}
		return str_replace(array('{license_plate}', '{endpoint}'), array($license_plate, $endpoint), self::URL);
	}
}