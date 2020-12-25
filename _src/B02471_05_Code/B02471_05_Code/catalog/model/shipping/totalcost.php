<?php
class ModelShippingtotalcost extends Model {
	public function getQuote($address) {
		$this->load->language('shipping/totalcost');

		$quote_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name");

		foreach ($query->rows as $result) {
			if ($this->config->get('totalcost_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}

			if ($status) {
				$cost = '';
				$totalcost = $this->cart->getSubTotal();

				$rates = explode(',', $this->config->get('totalcost_' . $result['geo_zone_id'] . '_rate'));

				foreach ($rates as $rate) {
					$data = explode(':', $rate);

					if ($data[0] >= $totalcost) {
						if (isset($data[1])) {
							$cost = $data[1];
						}

						break;
					}
				}

				if ((string)$cost != '') {
					$quote_data['totalcost_' . $result['geo_zone_id']] = array(
						'code'         => 'totalcost.totalcost_' . $result['geo_zone_id'],
						'title'        => $result['name'] . '  (' . $this->language->get('text_totalcost') . ' ' . $this->totalcost->format($totalcost, $this->config->get('config_totalcost_class_id')) . ')',
						'cost'         => $cost,
						'tax_class_id' => $this->config->get('totalcost_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('totalcost_tax_class_id'), $this->config->get('config_tax')))
					);
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
			$method_data = array(
				'code'       => 'totalcost',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('totalcost_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}