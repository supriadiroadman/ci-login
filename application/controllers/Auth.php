<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

   public function __construct()
   {
      parent::__construct();
      $this->load->library('form_validation');
   }


   public function index()
   {
      $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
      $this->form_validation->set_rules('password', 'Pasword', 'trim|required');

      if ($this->form_validation->run() == FALSE) {
         $data['title'] = 'Login Page';
         $this->load->view('templates/auth_header', $data);
         $this->load->view('auth/login');
         $this->load->view('templates/auth_footer');
      } else {
         // Validasi sukses
         $this->_login();
      }
   }

   // Method terpisah
   private function _login()
   {
      // Ambil inputan user
      $email = $this->input->post('email');
      $password = $this->input->post('password');

      // Ambil data dari database
      $user = $this->db->get_where('user', ['email' => $email])->row_array();

      // Jika Usernya ada di database
      if ($user) {
         // Jika Usernya aktif
         if ($user['is_active'] == 1) {
            // Cek password
            if (password_verify($password, $user['password'])) {

               // Buat session datanya untuk dipakai di halaman user
               $data = [
                  'email' => $user['email'],
                  'role_id' => $user['role_id']
               ];

               $this->session->set_userdata($data);

               if ($user['role_id'] == 1) {
                  // Ke controller admin method index
                  redirect('admin');
               } else {
                  // Ke controller user method index
                  redirect('user');
               }
            } else {
               $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong Password!</div>');

               redirect('auth');
            }
         } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">This Email has not been activated!</div>');

            redirect('auth');
         }
      } else {
         // Jika tidak ada user didatabase
         $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> Email not registered!</div>');

         redirect('auth');
      }
   }

   public function registration()
   {

      $this->form_validation->set_rules('name', 'Name', 'required|trim');
      $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
         'is_unique' => 'This email has ready already register!'
      ]);
      $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
         'matches' => 'Password not match!',
         'min_length' => 'Password too short!'
      ]);
      $this->form_validation->set_rules('password2', 'Password', 'trim|required|matches[password1]');

      if ($this->form_validation->run() == FALSE) {
         $data['title'] = 'User Registration';

         $this->load->view('templates/auth_header', $data);
         $this->load->view('auth/registration');
         $this->load->view('templates/auth_footer');
      } else {

         $data = [
            'name' => htmlspecialchars($this->input->post('name', true)),
            'email' => htmlspecialchars($this->input->post('email', true)),
            'image' => 'default.jpg',
            'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
            'role_id' => 2,
            'is_active' => 1,
            'date_created' => time()
         ];

         $this->db->insert('user', $data);

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please Login</div>');

         redirect('auth');
      }
   }

   public function logout()
   {
      // Hapus session email dan role id yang di set di method _login
      $this->session->unset_userdata('email');
      $this->session->unset_userdata('role_id');

      $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');

      redirect('auth');
   }

   public function blocked()
   {
      $this->load->view('auth/blocked');
   }
}
