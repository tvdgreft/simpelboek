<?php
namespace SIMPELBOEK;

if ( ! class_exists( 'notices' ) ) 
{
	class notices
	{
		function requirements_error()
		{
			$html = '';
			$html .= '<div class="error">';
			$html .= '<p>' . NAME . 'error: Your environment doesnot meet all of the system requirements listed below.</p>';
			$html .= '<ul class="ul-disc">';
			$html .= '<li>';
			$html .= '<strong>PHP ' . REQUIRED_PHP_VERSION . '+</strong><em>(You are running version ' . PHP_VERSION. ')</em>';
			$html .= '</li>';
			$html .= '<li>';
			$html .= '<strong>WordPress ' . REQUIRED_WP_VERSION . '+</strong><em>(You are running version ' . esc_html($wp_version) . ')</em>';
			$html .= '</li>';
			$html .= '<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>';
			$html .= '</div>';
			return($html);
		}
		function trap($message)
		{
			$html = '';
			$html .= '<div class="error">';
			$html .= '<p>' . $message . '</p>';
			$html .= '</div>';
			return($html);
		}
			
	}# end notices
}