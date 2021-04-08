<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->db->db_debug = FALSE;
	}

	public function select_data($param)
	{
		if (!empty($param['field'])) {
			$this->db->select($param['field']);
		}
		if (!empty($param['distinct'])) {
			$this->db->distinct($param['distinct']);
		}
		$this->db->from($param['table']);
		if (!empty($param['join'])) {
			foreach ($param['join'] as $key) {
				$this->db->join($key['table'], $key['on'], $key['type']);
			}
		}
		if (!empty($param['column_search'])) {
			$i = 0;
			foreach ($param['column_search'] as $item) {
				if (!empty($param['search'])) {
					if ($i === 0) {
						$this->db->group_start();
						$this->db->like($item, $param['search']);
					} else {
						$this->db->or_like($item, $param['search']);
					}

					if (count($param['column_search']) - 1 == $i) {
						$this->db->group_end();
					}
				}
				$i++;
			}
		}
		if (!empty($param['like'])) {
			$this->db->like($param['like']);
		}
		if (!empty($param['or_like'])) {
			if (!empty($param['grouping']) && ($param['grouping'] === true)) {
				$this->db->group_start();
			}
			foreach ($param['or_like'] as $key) {
				$this->db->or_like($key);
			}
			if (!empty($param['grouping']) && ($param['grouping'] === true)) {
				$this->db->group_end();
			}
		}
		if (!empty($param['not_like'])) {
			$this->db->not_like($param['not_like']);
		}
		if (!empty($param['or_not_like'])) {
			if (!empty($param['grouping']) && ($param['grouping'] === true)) {
				$this->db->group_start();
			}
			foreach ($param['or_not_like'] as $key) {
				$this->db->or_not_like($key);
			}
			if (!empty($param['grouping']) && ($param['grouping'] === true)) {
				$this->db->group_end();
			}
		}
		if (!empty($param['where_custom'])) {
			$this->db->where($param['where_custom']);
		} else {
			if (!empty($param['where'])) {
				$this->db->where($param['where']);
			}
			if (!empty($param['or_where'])) {
				if (!empty($param['grouping']) && ($param['grouping'] === true)) {
					$this->db->group_start();
				}
				foreach ($param['or_where'] as $key) {
					$this->db->or_where($key);
				}
				if (!empty($param['grouping']) && ($param['grouping'] === true)) {
					$this->db->group_end();
				}
			}
		}
		if (!empty($param['where_in'])) {
			$this->db->where_in(key($param['where_in']), $param['where_in'][key($param['where_in'])]);
		}
		if (!empty($param['where_not_in'])) {
			$this->db->where_not_in(key($param['where_not_in']), $param['where_not_in'][key($param['where_not_in'])]);
		}
		if (!empty($param['limit'])) {
			if (is_array($param['limit'])) {
				$this->db->limit(key($param['limit']), $param['limit'][key($param['limit'])]);
			} else {
				$this->db->limit($param['limit']);
			}
		}
		if (!empty($param['order_by'])) {
			$this->db->order_by(key($param['order_by']), $param['order_by'][key($param['order_by'])]);
		}
		if (!empty($param['group_by'])) {
			$this->db->group_by($param['group_by']);
		}

		return $this->db->get();
	}

	public function send_data($param)
	{
		if (!empty($param['where'])) {
			$this->db->where($param['where']);
			$this->db->update($param['table'], $param['data']);

			$db_error = $this->db->error();
			if (!empty($db_error['code'])) {
				return [
					'error' => TRUE,
					'system' => 'Database error! Error Code [' . $db_error['code'] . '] Error: ' . $db_error['message']
				];
			} else {
				return [
					'error' => FALSE
				];
			}
		} else {
			$this->db->insert($param['table'], $param['data']);

			$db_error = $this->db->error();
			if (!empty($db_error['code'])) {
				return [
					'error' => TRUE,
					'system' => 'Database error! Error Code [' . $db_error['code'] . '] Error: ' . $db_error['message']
				];
			} else {
				return [
					'error' => FALSE
				];
			}
		}
	}

	public function delete_data($param)
	{
		$this->db->where($param['where']);
		$this->db->delete($param['table']);

		$db_error = $this->db->error();
		if (!empty($db_error['code'])) {
			return [
				'error' => TRUE,
				'system' => 'Database error! Error Code [' . $db_error['code'] . '] Error: ' . $db_error['message']
			];
		} else {
			return [
				'error' => FALSE
			];
		}
	}

	public function count_all_data($param)
	{
		if (!empty($param['field'])) {
			$this->db->select($param['field']);
		}
		if (!empty($param['distinct'])) {
			$this->db->distinct($param['distinct']);
		}
		$this->db->from($param['table']);
		if (!empty($param['join'])) {
			foreach ($param['join'] as $key) {
				$this->db->join($key['table'], $key['on'], $key['type']);
			}
		}
		if (!empty($param['column_search'])) {
			$i = 0;
			foreach ($param['column_search'] as $item) {
				if (!empty($param['search'])) {
					if ($i === 0) {
						$this->db->group_start();
						$this->db->like($item, $param['search']);
					} else {
						$this->db->or_like($item, $param['search']);
					}

					if (count($param['column_search']) - 1 == $i) {
						$this->db->group_end();
					}
				}
				$i++;
			}
		}
		if (!empty($param['like'])) {
			$this->db->like($param['like']);
		}
		if (!empty($param['or_like'])) {
			foreach ($param['or_like'] as $key) {
				$this->db->or_like($key);
			}
		}
		if (!empty($param['not_like'])) {
			$this->db->not_like($param['not_like']);
		}
		if (!empty($param['or_not_like'])) {
			foreach ($param['or_not_like'] as $key) {
				$this->db->or_not_like($key);
			}
		}
		if (!empty($param['where'])) {
			$this->db->where($param['where']);
		}
		if (!empty($param['where_in'])) {
			$this->db->where_in(key($param['where_in']), $param['where_in'][key($param['where_in'])]);
		}
		if (!empty($param['order_by'])) {
			$this->db->order_by(key($param['order_by']), $param['order_by'][key($param['order_by'])]);
		}
		if (!empty($param['group_by'])) {
			$this->db->group_by($param['group_by']);
		}

		return $this->db->get()->num_rows();
	}
}
