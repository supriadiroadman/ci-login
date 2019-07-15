<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu extends CI_Controller
{
   public function index()
   {
      $data['title'] = 'Menu Management';
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      $data['menu'] = $this->db->get('user_menu')->result_array();

      $this->form_validation->set_rules('menu', 'Menu', 'trim|required');


      if ($this->form_validation->run() == FALSE) {
         $this->load->view('templates/header', $data);
         $this->load->view('templates/sidebar', $data);
         $this->load->view('templates/topbar', $data);
         $this->load->view('menu/index', $data);
         $this->load->view('templates/footer');
      } else {
         $this->db->insert('user_menu', ['menu' => $this->input->post('menu')]);

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New menu added</div>');

         redirect('menu');
      }
   }

   public function submenu()
   {
      $data['title'] = 'Submenu Management';
      // Ambil data dari tabel user dan dari session berdasarkan email
      $data['user'] = $this->db->get_where('user', ['email' =>
      $this->session->userdata('email')])->row_array();

      $this->load->model('Menu_model', 'menu');


      $data['SubMenu'] = $this->menu->getSubMenu();
      $data['menu'] = $this->db->get('user_menu')->result_array();

      $this->form_validation->set_rules('title', 'Title', 'trim|required');
      $this->form_validation->set_rules('menu_id', 'Menu', 'trim|required');
      $this->form_validation->set_rules('url', 'URL', 'trim|required');
      $this->form_validation->set_rules('icon', 'Icon', 'trim|required');


      if ($this->form_validation->run() == FALSE) {
         $this->load->view('templates/header', $data);
         $this->load->view('templates/sidebar', $data);
         $this->load->view('templates/topbar', $data);
         $this->load->view('menu/submenu', $data);
         $this->load->view('templates/footer');
      } else {
         $data = [
            'menu_id' => $this->input->post('menu_id'),
            'title' => $this->input->post('title'),
            'url' => $this->input->post('url'),
            'icon' => $this->input->post('icon'),
            'is_active' => $this->input->post('is_active')
         ];
         $this->db->insert('user_sub_menu', $data);

         $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">New Submenu added</div>');

         redirect('menu/submenu');
      }
   }
}
