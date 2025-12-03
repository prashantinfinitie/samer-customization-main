<?php
class Migrate extends CI_Controller{
    public function index(){
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
			$this->load->library('migration');
			if ($this->migration->latest() === FALSE) {
				show_error($this->migration->error_string());
			}else{
				// echo $this->db->dbprefix('orders');

				echo "Migration Successfully";
			}
		}else{
			echo "You are not authorized to do this";
		}
    }
    public function rollback($version = ''){
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin() && defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 1) {
			$this->load->library('migration');
			if(!empty($version) && is_numeric($version)){
				$this->migration->version($version);
			}else{
				show_error($this->migration->error_string());
			}
		}else{
			echo "You are not authorized to do this";
		}
    }

	public function to_version($version = '')
	{

		if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
			$this->load->library('migration');
			if (!empty($version) && is_numeric($version)) {
				$result = $this->migration->version($version);
				if ($result === FALSE) {
					show_error($this->migration->error_string());
				} else {
					echo "Migration to version " . $version . " completed successfully. Current version: " . $result;
				}
			} else {
				echo "Please provide a valid version number. Usage: /admin/migrate/to_version/057";
			}
		} else {
			echo "You are not authorized to do this";
		}
	}
}
