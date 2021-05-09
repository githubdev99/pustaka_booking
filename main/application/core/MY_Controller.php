<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends MX_Controller
{
	public $core = [];

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');

		$this->load->module('master');

		$this->core['url_images'] = base_url() . 'assets/images/';
		$this->core['logo_mini'] = $this->core['url_images'] . 'logo-mini.png';
		$this->core['logo_full'] = $this->core['url_images'] . 'logo-mini.png';
		$this->core['app_name'] = 'Pustaka Booking';
		$this->core['imageNotFound'] = "{$this->core['url_images']}img-thumbnail.png";
		$this->core['imageUpload'] = base_url() . "assets/upload/";

		if ($this->session->has_userdata('id')) {
			$this->core['user'] = $this->dataUser($this->session->userdata('id'));
		}

		$this->core['totalMember'] = $this->api_model->count_all_data([
			'where' => [
				'role_id' => 2
			],
			'table' => 'user',
		]);
		$this->core['totalBook'] = $this->api_model->count_all_data([
			'table' => 'book',
		]);
		$this->core['category'] = $this->api_model->select_data([
			'field' => '*',
			'table' => 'category',
			'order_by' => [
				'name' => 'asc',
			],
		])->result();
	}

	public function dataUser($id)
	{
		$parsing['user'] = $this->api_model->select_data([
			'field' => 'user.*, role.name as role_name',
			'table' => 'user',
			'join' => [
				[
					'table' => 'role',
					'on' => 'role.id = user.role_id',
					'type' => 'inner'
				],
			],
			'where' => [
				'user.id' => $id,
			],
		])->row();

		if (empty($parsing['user'])) {
			return [];
		} else {
			$user['id'] = $parsing['user']->id;
			$user['name'] = $parsing['user']->name;
			$user['email'] = $parsing['user']->email;
			$user['image'] = (!empty($parsing['user']->image)) ? base_url() . 'assets/images/' . $parsing['user']->image : base_url() . 'assets/images/user-default.jpg';
			$user['role'] = [
				'id' => $parsing['user']->role_id,
				'name' => $parsing['user']->role_name,
			];

			return $user;
		}
	}

	public function core($title)
	{
		$this->core['title_page'] = $title . ' | ' . $this->core['app_name'];

		return $this->core;
	}

	public function alert_popup($message)
	{
		$sweet_alert = '
		Swal.mixin({
			toast: true,
			position: "top",
			showCloseButton: !0,
			showConfirmButton: false,
			timer: 2000,
			timerProgressBar: true,
			onOpen: (toast) => {
				toast.addEventListener("mouseenter", Swal.stopTimer)
				toast.addEventListener("mouseleave", Swal.resumeTimer)
			}
		}).fire({
			icon: "' . $message['swal']['type'] . '",
			title: "' . $message['swal']['title'] . '"
		});
		';

		$this->session->set_flashdata($message['name'], $sweet_alert);
	}

	public function auth($param)
	{
		if ($param['session'] == 'admin') {
			if ($param['login']) {
				if (!empty($this->core['user']) && ($this->core['user']['role']['id'] == '1')) {
					redirect(base_url() . 'admin/dashboard', 'refresh');
				}
			} else {
				if (empty($this->core['user'])) {
					redirect(base_url() . 'auth/login', 'refresh');
				}
			}
		}

		if ($param['session'] == 'member') {
			if ($param['login']) {
				if (!empty($this->core['user']) && ($this->core['user']['role']['id'] == '2')) {
					redirect(base_url() . 'member/dashboard', 'refresh');
				}
			} else {
				if (empty($this->core['user'])) {
					redirect(base_url() . 'auth/login', 'refresh');
				}
			}
		}
	}
}
