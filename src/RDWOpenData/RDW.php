<?php

namespace deBrand\RDWOpenData;

use DateTime;
use Exception;
use deBrand\RDWOpenData\Api\Client;
use deBrand\RDWOpenData\Api\Endpoints;

class RDW {

	/**
	 * Retrieve Vehicle data for a given license plate
	 * Use RdwOpenData\Api\Endpoints for a list of available endpoints
	 *
	 * @param string $license_plate
	 * @param array $endpoints
	 * @return array|null
	 * @throws Exception
	 */
	public static function get(string $license_plate, array $endpoints = Endpoints::ALL): ?array {
		$license_plate = str_replace("-", "", strtoupper($license_plate));
		$data = [];

		$client = new Client();

		foreach($endpoints as $endpoint) {
			if(!empty($output = $client->getData($license_plate, $endpoint))) {
				$data = self::mergeOutput($data, $output, $endpoint);
			}
		}

		if(empty($data)) {
			return null;
		}

		return self::mapOutput($data);
	}

	/**
	 * Merges Api output into a more logical structure
	 *
	 * @param array $data
	 * @param array $output
	 * @param string $endpoint
	 * @return array
	 */
	protected static function mergeOutput(array $data, array $output, string $endpoint): array {
		if($endpoint === Endpoints::ASSEN) {
			usort($output, function($a, $b) {
				return $a['as_nummer'] - $b['as_nummer'];
			});
			foreach($output as $output_as) {
				foreach($output_as as $item => $value) {
					$data['as_' . $output_as['as_nummer'] . '_' . $item] = $value;
				}
			}
		} else {
			$data = array_merge($data, $output[0]);
		}
		return $data;
	}

	/**
	 * Maps Api output to more useful values and types
	 *
	 * @param array $output
	 * @return array
	 */
	protected static function mapOutput(array $output): array {
		array_walk($output, function(&$value, $key) use (&$output) {
			switch($key) {
				case 'merk':
					$value = ucfirst(strtolower($value));
					break;
				case 'nettomaximumvermogen':
					$output['nettomaximumvermogen_pk'] = round($value * 1.35962);
					$output['nettomaximumvermogen_kw'] = $value;
					$value = null;
					break;
				case 'datum_eerste_toelating':
				case 'datum_eerste_tenaamstelling_in_nederland':
				case 'datum_eerste_afgifte_nederland':
				case 'datum_tenaamstelling':
				case 'vervaldatum_apk':
				case 'vervaldatum_tachograaf':
					$dt_key = $key . '_dt';
					if(isset($output[$dt_key])) {
						$value = DateTime::createFromFormat('Y-m-d\TH:i:s.u', $output[$dt_key]);
					} else {
						$value = DateTime::createFromFormat('Ymd', $value);
						$value->setTime(0, 0, 0, 0);
					}
					break;
				case 'api_gekentekende_voertuigen_assen':
				case 'api_gekentekende_voertuigen_brandstof':
				case 'api_gekentekende_voertuigen_carrosserie':
				case 'api_gekentekende_voertuigen_carrosserie_specifiek':
				case 'api_gekentekende_voertuigen_voertuigklasse':
					$value = null;
					break;
			}
		});
		return array_filter($output);
	}

}