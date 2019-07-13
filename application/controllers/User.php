<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
   public function index()
   {
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      echo '(User / Index) Selamat datang ' . $data['user']['name'];
   }
}
