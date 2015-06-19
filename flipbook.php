<?php 
/*
 * Plugin Name: FlipBook
 * Plugin URI: http://wordpress.org/plugins/fbook
 * Description: Plugin para simular efeito de um livro, revista.
 * Version: 1.0
 * Author: Santins
 * Author URI: http://santins.com.br
 * License: GPLv2 or later
 *
 *
 * License:
 * ==============================================================================
 * Copyright 2014 - 2015 Santins  (email : eduardosilva@santins.com.br)
 * Hints, maintain updates in 2015 from Ron Guerin <ron@vnetworx.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
*/
 
if( !defined('WP_URL_FLIP') ){
  define('WP_URL_FLIP', untrailingslashit( dirname( __FILE__ ) ) );
}

if ( get_bloginfo("version") < 3.5 ) {
  die("Wordpress must be or above version 3.5");
}

include WP_URL_FLIP . '/class/WP_FlipBook.php';

function WP_FlipBook_Instantiate() {
  new WP_FlipBook( plugin_dir_url(__FILE__) );
}

add_action( 'plugins_loaded', 'WP_FlipBook_Instantiate', 1 );

?>