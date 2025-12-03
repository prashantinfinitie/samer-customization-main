<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language']);
        $this->load->model(['Shipping_company_model', 'Area_model', 'ion_auth_model']);
    }

    public function index()
    {


        if (!$this->ion_auth->logged_in() && !$this->ion_auth->is_shipping_company()) {


            $this->data['main_page'] = FORMS . 'login';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Shipping Company Login Panel | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Shipping Company Login Panel | ' . $settings['app_name'];
            $this->data['app_name'] = $settings['app_name'];
            $this->data['logo'] = get_settings('logo');
            $identity = $this->config->item('identity', 'ion_auth');
            if (empty($identity)) {
                $identity_column = 'text';
            } else {
                $identity_column = $identity;
            }

            $this->data['identity_column'] = $identity_column;
            $this->load->view('shipping_company/login', $this->data);
        } else if ($this->ion_auth->logged_in() && $this->ion_auth->is_shipping_company()) {
            redirect('shipping-company/home', 'refresh');
        } else if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            redirect('admin/home', 'refresh');
        }
    }


    public function sign_up()
    {


        $this->data['main_page'] = FORMS . 'shipping-company-registration';
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Sign Up Shipping Company | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Sign Up Shipping Company| ' . $settings['app_name'];
        $this->data['logo'] = get_settings('logo');

        $this->data['fetched_data'] = $this->db->select(' u.* ')
            ->join('users_groups ug', ' ug.user_id = u.id ')
            ->where(['ug.group_id' => '3'])
            ->get('users u')
            ->result_array();

        $this->data['shipping_method'] = get_settings('shipping_method', true);
        $this->data['system_settings'] = get_settings('system_settings', true);
        $this->data['cities'] = fetch_details('cities', "", 'name,id', '5');
        $this->load->view('shipping_company/login', $this->data);
    }


    // Login
    public function auth()
    {
        // Only allow POST
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $identity_column = $this->config->item('identity', 'ion_auth');

        // Form Validation
        $this->form_validation->set_rules('identity', ucfirst($identity_column), 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            return $this->_json_response(true, validation_errors());
        }

        $identity = $this->input->post('identity', true);

        $user = $this->db->select('id, status, active')
            ->where($identity_column, $identity)
            ->limit(1)
            ->get('users')
            ->row_array();

        if (empty($user)) {
            return $this->_json_response(true, ucfirst($identity_column) . ' field is not correct');
        }

        // Check Group: shipping_company
        if (!$this->ion_auth_model->in_group('shipping_company', $user['id'])) {
            return $this->_json_response(true, 'You are not registered as a shipping company user');
        }

        // Attempt Login (use standard 3-arg signature)
        $remember = (bool) $this->input->post('remember', true);
        $password = $this->input->post('password', true);

        $login_result = $this->ion_auth->login($identity, $password, $remember);

        if (!$login_result) {
            $errors = $this->ion_auth->errors();
            // log full details for debugging, but don't expose raw HTML to client
            log_message('error', 'IonAuth login failed for identity: ' . $identity . ' | errors: ' . print_r($errors, true));
            return $this->_json_response(true, 'Login failed: ' . strip_tags($errors));
        }

        // Re-fetch user in case login changed session / last_login or if you need current flags
        $user = $this->db->select('id, status, active')->where('id', $user['id'])->get('users')->row_array();

        // Check Approval Status
        if ((int) ($user['status'] ?? 0) === 0) {
            $this->ion_auth->logout();
            return $this->_json_response(true, 'Wait for admin approval.');
        }

        // Success
        return $this->_json_response(false, $this->ion_auth->messages());
    }


    /**
     * Helper to send JSON response with CSRF
     */
    private function _json_response($error, $message)
    {
        $response = [
            'error'    => (bool) $error,
            'message'  => $message,
            'csrfName' => $this->security->get_csrf_token_name(),
            'csrfHash' => $this->security->get_csrf_hash(),
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }


    //Create shipping company

    public function create_shipping_company()
    {
        // Password strength regex (same as delivery boy)
        $regex = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

        // Basic validation
        $this->form_validation->set_rules('company_name', 'Company Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]|max_length[16]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required|matches[password]|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');


        // KYC required for new registration
        if (!isset($_POST['edit_shipping_company'])) {
            if (!isset($_FILES['kyc_documents']['name'][0]) || empty($_FILES['kyc_documents']['name'][0])) {
                $this->form_validation->set_rules('kyc_documents', 'KYC Documents', 'trim|required|xss_clean', array('required' => 'Please upload at least one KYC document'));
            }
        }

        // fetch existing docs (edit flow)
        if (isset($_POST['edit_shipping_company'])) {
            $company_data = fetch_details('users', ['id' => $_POST['edit_shipping_company']], 'kyc_documents');
            if (isset($company_data[0]['kyc_documents']) && !empty($company_data[0]['kyc_documents'])) {
                $kyc_documents = explode(',', $company_data[0]['kyc_documents']);
            }
        }

        // password strength
        if (!preg_match($regex, $this->input->post('password', true))) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] =
                "Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, one number, and one special character.";

            echo json_encode($this->response);
            return;
        }

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = validation_errors();
            echo json_encode($this->response);
            return;
        }

        // Upload KYC docs
        if (!file_exists(FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH)) {
            mkdir(FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH, 0777, true);
        }

        $files = $_FILES;
        $images_new_name_arr = array();
        $images_info_error = "";
        $allowed_media_types = implode('|', allowed_media_types());
        $config = [
            'upload_path' =>  FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH,
            'allowed_types' => $allowed_media_types,
            'max_size' => 8000,
        ];

        if (isset($files['kyc_documents']) && !empty($files['kyc_documents']['name'][0])) {
            $doc_count = count((array)$files['kyc_documents']['name']);
            $doc_upload = $this->upload;
            $doc_upload->initialize($config);

            // if edit, delete old docs
            if (isset($_POST['edit_shipping_company']) && !empty($_POST['edit_shipping_company']) && isset($company_data[0]['kyc_documents']) && !empty($company_data[0]['kyc_documents'])) {
                $old_docs = explode(',', $company_data[0]['kyc_documents']);
                foreach ($old_docs as $old_doc) {
                    if (file_exists(FCPATH . $old_doc)) {
                        unlink(FCPATH . $old_doc);
                    }
                }
            }

            for ($i = 0; $i < $doc_count; $i++) {
                if (!empty($_FILES['kyc_documents']['name'][$i])) {
                    $_FILES['temp_doc']['name'] = $files['kyc_documents']['name'][$i];
                    $_FILES['temp_doc']['type'] = $files['kyc_documents']['type'][$i];
                    $_FILES['temp_doc']['tmp_name'] = $files['kyc_documents']['tmp_name'][$i];
                    $_FILES['temp_doc']['error'] = $files['kyc_documents']['error'][$i];
                    $_FILES['temp_doc']['size'] = $files['kyc_documents']['size'][$i];

                    if (!$doc_upload->do_upload('temp_doc')) {
                        $images_info_error = 'kyc_documents: ' . $images_info_error . ' ' . $doc_upload->display_errors();
                    } else {
                        $temp_array = $doc_upload->data();
                        resize_review_images($temp_array, FCPATH . SHIPPING_COMPANY_DOCUMENTS_PATH);
                        $images_new_name_arr[$i] = SHIPPING_COMPANY_DOCUMENTS_PATH . $temp_array['file_name'];
                    }
                }
            }

            // rollback if errors
            if ($images_info_error != NULL) {
                if (!empty($images_new_name_arr)) {
                    foreach ($images_new_name_arr as $val) {
                        if (file_exists(FCPATH . $val)) {
                            unlink(FCPATH . $val);
                        }
                    }
                }
                $this->response['error'] = true;
                $this->response['message'] = $images_info_error;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return;
            }
        }

        // uniqueness checks for new registration
        if (!isset($_POST['edit_shipping_company'])) {
            if (!$this->form_validation->is_unique($this->input->post('mobile'), 'users.mobile') || !$this->form_validation->is_unique($this->input->post('email'), 'users.email')) {
                $response["error"]   = true;
                $response["message"] = "Email or mobile already exists !";
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }
        } else {
            // edit flow uniqueness (allow same email/mobile for same user)
            if (!edit_unique($this->input->post('email', true), 'users.email.' . $this->input->post('edit_shipping_company', true) . '') || !edit_unique($this->input->post('mobile', true), 'users.mobile.' . $this->input->post('edit_shipping_company', true) . '')) {
                $response["error"]   = true;
                $response["message"] = "Email or mobile already exists !";
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }
        }

        // prepare identity & other fields
        $identity_column = $this->config->item('identity', 'ion_auth');
        $email = strtolower($this->input->post('email'));
        $mobile = $this->input->post('mobile');
        $identity = ($identity_column == 'mobile') ? $mobile : $email;
        $password = $this->input->post('password');

        $serviceable_zipcodes = (isset($_POST['serviceable_zipcodes']) && !empty($_POST['serviceable_zipcodes'])) ? implode(",", $this->input->post('serviceable_zipcodes', true)) : NULL;
        $serviceable_cities = (isset($_POST['serviceable_cities']) && !empty($_POST['serviceable_cities'])) ? implode(",", $this->input->post('serviceable_cities', true)) : NULL;

        // new registration
        if (!isset($_POST['edit_shipping_company'])) {
            $additional_data = [
                'username' => $this->input->post('company_name'),
                'address' => $this->input->post('address'),
                'serviceable_zipcodes' => $serviceable_zipcodes,
                'serviceable_cities' => $serviceable_cities,
                'type' => 'phone',
                'kyc_documents' => implode(',', (array)$images_new_name_arr),
                'status' => 0, // pending approval
                'is_shipping_company' => 1
            ];

            // group id 6 same as admin flow
            $insert_id = $this->ion_auth->register($identity, $password, $email, $additional_data, ['6']);

            if (!empty($insert_id)) {
                // optionally send notification / e-mail to company and admins
                $company = fetch_details('users', ['id' => $insert_id]);
                // send email to company (if email settings configured)
                $email_settings = get_settings('email_settings', true);
                if (!empty($email_settings) && !empty($company[0]['email'])) {
                    $title = "Shipping Company Registered - Awaiting Approval";
                    $mail_admin_msg = 'Thank you for registering. Your application will be reviewed by admin and activated upon approval.';
                    $email_message = array(
                        'username' => 'Hello, Dear <b>' . ucfirst($company[0]['username']) . '</b>, ',
                        'subject' => $title,
                        'email' => $company[0]['email'],
                        'message' => $mail_admin_msg
                    );
                    send_mail($company[0]['email'],  $title, $this->load->view('admin/pages/view/contact-email-template', $email_message, TRUE));
                }
            }

            // keep user inactive until admin approves: do not call update_details active=1
        } else {
            // edit flow: update user record (similar to admin model)
            $_POST['serviceable_zipcodes'] = $serviceable_zipcodes;
            $_POST['serviceable_cities'] = $serviceable_cities;
            $_POST['kyc_documents'] = isset($images_new_name_arr) && !empty($images_new_name_arr) ? implode(',', (array)$images_new_name_arr) : (isset($company_data[0]['kyc_documents']) ? $company_data[0]['kyc_documents'] : '');
            $this->Shipping_company_model->update_shipping_company($_POST);
        }

        $this->response['error'] = false;
        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
        $message = (isset($_POST['edit_shipping_company'])) ? 'Shipping Company Updated Successfully' : 'Shipping Company Registered Successfully. Wait for admin approval.';
        $this->response['message'] = $message;
        echo json_encode($this->response);
    }

    /**
     * Update user profile for shipping company
     */
    public function update_user()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
        }

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_shipping_company()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Unauthorized access';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = $this->session->userdata('identity');
        $regex_password = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        $user = $this->ion_auth->user()->row();
        $tables = $this->config->item('tables', 'ion_auth');

        // Validation rules
        if ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'required|xss_clean|trim|valid_email|edit_unique[users.email.' . $user->id . ']');
        } else {
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|xss_clean|trim|numeric|edit_unique[users.mobile.' . $user->id . ']');
        }
        $this->form_validation->set_rules('username', 'Company Name', 'required|xss_clean|trim');

        // Password validation (only if changing password)
        if (!empty($_POST['old']) || !empty($_POST['new']) || !empty($_POST['new_confirm'])) {
            $this->form_validation->set_rules('old', 'Old Password', 'required|xss_clean');
            $this->form_validation->set_rules('new', 'New Password', 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', 'Confirm Password', 'required|xss_clean');

            if (!preg_match($regex_password, $_POST['new'])) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'Password must be at least 8 characters with uppercase, lowercase, number and special character.';
                echo json_encode($this->response);
                return;
            }
        }

        if (!$this->form_validation->run()) {
            if (validation_errors()) {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = validation_errors();
                echo json_encode($response);
                return false;
            }
        }

        // Change password if requested
        if (!empty($_POST['old']) || !empty($_POST['new']) || !empty($_POST['new_confirm'])) {
            if (!$this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'))) {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->ion_auth->errors();
                echo json_encode($response);
                return;
            }
        }

        // Process profile image upload
        if (!file_exists(FCPATH . USER_IMG_PATH)) {
            mkdir(FCPATH . USER_IMG_PATH, 0777, true);
        }

        $profile_doc = '';
        $profile_error = "";

        if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
            $config = [
                'upload_path' => FCPATH . USER_IMG_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif|webp',
                'max_size' => 8000,
            ];

            $this->load->library('upload');
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('image')) {
                $profile_error = 'Image: ' . $this->upload->display_errors('', '');
            } else {
                $temp_array = $this->upload->data();
                if (function_exists('resize_review_images')) {
                    resize_review_images($temp_array, FCPATH . USER_IMG_PATH);
                }
                $profile_doc = USER_IMG_PATH . $temp_array['file_name'];
            }
        }

        if (!empty($profile_error)) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = $profile_error;
            echo json_encode($this->response);
            return;
        }

        // Update user data
        $set = [
            'username' => $this->input->post('username', true),
            'address' => $this->input->post('address', true),
            'image' => (!empty($profile_doc)) ? $profile_doc : $this->input->post('old_profile_image', true)
        ];

        if ($identity_column == 'email') {
            $set['email'] = $this->input->post('email', true);
        } else {
            $set['mobile'] = $this->input->post('mobile', true);
        }

        $set = escape_array($set);
        $this->db->set($set)->where('id', $user->id)->update($tables['login_users']);

        $response['error'] = false;
        $response['csrfName'] = $this->security->get_csrf_token_name();
        $response['csrfHash'] = $this->security->get_csrf_hash();
        $response['message'] = 'Profile updated successfully';
        echo json_encode($response);
    }

    /**
     * Forgot password page for shipping company
     */
    public function forgot_password()
    {
        $this->data['main_page'] = FORMS . 'forgot-password';
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Forgot Password | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Forgot Password | ' . $settings['app_name'];
        $this->data['logo'] = get_settings('logo');
        $this->load->view('shipping_company/login', $this->data);
    }

    /**
     * Get zipcodes for shipping company registration
     */
    public function get_zipcodes()
    {
        $limit = (isset($_GET['limit'])) ? $this->input->get('limit', true) : 25;
        $offset = (isset($_GET['offset'])) ? $this->input->get('offset', true) : 0;
        $search = (isset($_GET['search'])) ? $_GET['search'] : null;
        $zipcodes = $this->Area_model->get_zipcodes($search, $limit, $offset);
        $this->response['data'] = $zipcodes['data'];
        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
        print_r(json_encode($this->response));
    }

    /**
     * Get cities for shipping company registration
     */
    public function get_cities()
    {
        $search = $this->input->get('search');
        $response = $this->Area_model->get_cities_list($search);
        echo json_encode($response);
    }
}
