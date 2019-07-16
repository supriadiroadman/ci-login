<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{

   public function __construct()
   {
      parent::__construct();
      // Cek session dan rolenya
      is_logged_in();
   }


   public function index()
   {
      $data['title'] = 'Dashboard';
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      $this->load->view('templates/header', $data);
      $this->load->view('templates/sidebar', $data);
      $this->load->view('templates/topbar', $data);
      $this->load->view('admin/index', $data);
      $this->load->view('templates/footer');
   }
}
