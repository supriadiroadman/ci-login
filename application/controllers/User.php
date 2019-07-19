<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
   public function __construct()
   {
      parent::__construct();
      // Cek session dan rolenya
      is_logged_in();
   }

   public function index()
   {
      $data['title'] = 'My Profile';
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('user/index', $data);
      $this->load->view('templates/footer');
   }

   public function edit()
   {
      $data['title'] = 'Edit Profile';
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      $this->form_validation->set_rules('name', 'Fullname', 'required|trim');

      if ($this->form_validation->run() == FALSE) {
         $this->load->view('templates/header', $data);
         $this->load->view('templates/sidebar', $data);
         $this->load->view('templates/topbar', $data);
         $this->load->view('user/edit', $data);
         $this->load->view('templates/footer');
      } else {
         $name = $this->input->post('name');
         $email = $this->input->post('email');

         $upload_image = $_FILES['image']['name'];
         if ($upload_image) {
            $config['upload_path'] = './assets/img/profile/';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size']     = '2048';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('image')) {

               // Ambil nama gambar di database
               $old_image = $data['user']['image'];
               // bila gambar lama tidak sama dengan nama image di folder profile
               if ($old_image != 'default.jpg') {
                  // hapus gambar lama di folder
                  unlink(FCPATH . 'assets/img/profile/' . $old_image);
               }
               // Masukkan gambar baru
               $new_image = $this->upload->data('file_name');
               $this->db->set('image', $new_image);
            } else {
               echo $this->upload->display_errors();
            }
         }

         $this->db->set('name', $name);
         $this->db->where('email', $email);
         $this->db->update('user');

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Your profile has been updated!</div>');

         redirect('user');
      }
   }
}
