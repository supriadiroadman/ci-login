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
      // Cegat akses ke controller auth saat masih login(ada session)
      if ($this->session->userdata('email')) {
         redirect('user');
      }


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
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">This Email has not been activated! Please cek your email</div>');

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
      // Cegat akses ke controller auth saat masih login(ada session)
      if ($this->session->userdata('email')) {
         redirect('user');
      }

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
         $email = $this->input->post('email', true);
         $data = [
            'name' => htmlspecialchars($this->input->post('name', true)),
            'email' => htmlspecialchars($email),
            'image' => 'default.jpg',
            'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
            'role_id' => 2,
            'is_active' => 0,
            'date_created' => time()
         ];

         // Siapkan token
         $token = base64_encode(random_bytes(32));
         // data untuk ke tabel user_token
         $user_token = [
            'email' => $email,
            'token' => $token,
            'date_created' => time()
         ];

         $this->db->insert('user', $data);
         $this->db->insert('user_token', $user_token);

         // Kirim email setelah registrasi berhasil
         $this->_sendEmail($token, 'verify');

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Congratulation! your account has been created. Please cek your email to activated your account.</div>');

         redirect('auth');
      }
   }

   private function _sendEmail($token, $type)
   {
      $this->load->library('email');

      $config = [
         'protocol' => 'smtp',
         'smtp_host' => 'ssl://smtp.googlemail.com',
         'smtp_user' => 'supriadiroadman2@gmail.com',
         'smtp_pass' => '?abcd1234?',
         'smtp_port' => 465,
         'mailtype' => 'html',
         'charset' => 'utf-8',
         'newline' => "\r\n"
      ];

      $this->email->initialize($config);
      $this->load->library('email', $config);

      $this->email->from('supriadiroadman2@gmail.com', 'Supriadi Roadman Siagian');
      $this->email->to($this->input->post('email'));

      if ($type = 'verify') {
         $this->email->subject('Account Verification');
         $this->email->message('Click this link to verify your account: <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Actived</a>');
      }


      if ($this->email->send()) {
         return true;
      } else {
         echo $this->email->print_debugger();
         die;
      }
   }

   // Fungsi untuk mencek activated sendemail
   public function verify()
   {
      // Ambil data dari url yg diklik user waktu aktivasi email
      $email = $this->input->get('email');
      $token = $this->input->get('token');

      // query ke database cek apakah email terdaftar di database user
      $user = $this->db->get_where('user', ['email' => $email])->row_array();

      if ($user) {
         // query ke database cek apakah email terdaftar di database user_token
         $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
         if ($user_token) {
            if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
               // Bila semua ok set is_active = 1 di tabel user
               $this->db->set('is_active', 1);
               $this->db->where('email', $email);
               $this->db->update('user');

               // Lalu hapus token dari tabel user_token
               $this->db->delete('user_token', ['email' => $email]);

               $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $email . ' has been activated! Please login.</div>');

               redirect('auth');
            } else {
               // Hapus usernya dari tabel user dan tabel user_token bila tidak diaktivasi sebelum satu hari
               $this->db->delete('user', ['email' => $email]);
               $this->db->delete('user_token', ['email' => $email]);

               $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Wrong token.</div>');

               redirect('auth');
            }
         } else {
            //  Bila token dari url salah/ dimanipulasi saat klik tombol aktivasi email
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Wrong token.</div>');

            redirect('auth');
         }
      } else {
         //  Bila email dari url salah/ dimanipulasi saat klik tombol aktivasi email
         $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Wrong email.</div>');

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
