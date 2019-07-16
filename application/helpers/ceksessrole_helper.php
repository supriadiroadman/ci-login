<?php

function is_logged_in()
{

   $CI = get_instance();
   // Bila belum login
   if (!$CI->session->userdata('email')) {
      redirect('auth');
   } else {
      // Bila sudah login
      $role_id = $CI->session->userdata('role_id');
      $menu = $CI->uri->segment(1);

      // Ambil id dari tabel user_menu
      $queryMenu = $CI->db->get_where('user_menu', ['menu' => $menu])->row_array();
      $menu_id = $queryMenu['id'];

      // Cek role id bisa akses menu_id yang mana
      $userAccess = $CI->db->get_where('user_access_menu', [
         'role_id' => $role_id,
         'menu_id' => $menu_id
      ]);

      // Cek bila ada jumlah barisnya dari database
      if ($userAccess->num_rows() < 1) {
         // Panggil method blocked di controller auth
         redirect('auth/blocked');
      }
   }
}

function check_access($role_id, $menu_id)
{
   $CI = get_instance();
   $result = $CI->db->get_where('user_access_menu', [
      'role_id' => $role_id,
      'menu_id' => $menu_id
   ]);

   if ($result->num_rows() > 0) {
      return "checked= 'checked'";
   }
}
