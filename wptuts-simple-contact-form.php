<?php
/*
Plugin Name: Simple Contact Form Shortcode
Plugin URI: http://wp.tutsplus.com/author/barisunver/
Text Domain: wptuts-simple-contact-form
Domain Path: ./languages
Description: [Modified Version] A simple contact form for simple needs. Usage: <code>[contact email="your@email.address"]</code>
Version: 1.0
Author: Barış Ünver
Author URI: http://beyn.org/
*/

function plugin_name_load_plugin_textdomain() {
 
	$domain = 'wptuts-simple-contact-form';
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
 
	// wp-content/languages/plugin-name/plugin-name-de_DE.mo
	load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
	// wp-content/plugins/plugin-name/languages/plugin-name-de_DE.mo
	load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
 
}
add_action( 'init', 'plugin_name_load_plugin_textdomain' );


// function to get the IP address of the user
function wptuts_get_the_ip() {
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	else {
		return $_SERVER["REMOTE_ADDR"];
	}
}

// the shortcode
function wptuts_contact_form_sc($atts) {
	$result = $sent = $info = "";
	$form_data = array();
	extract(shortcode_atts(array(
		"email" => get_bloginfo('admin_email'),
		"subject" => '',
		"label_name" => __('Name','wptuts-simple-contact-form'),
		"label_email" => __('E-mail Address','wptuts-simple-contact-form'),
		"label_subject" => __('Subject','wptuts-simple-contact-form'),
		"label_message" => __('Message','wptuts-simple-contact-form'),
		"label_submit" => __('Submit','wptuts-simple-contact-form'),
		"error_empty" => __('Please fill in all the required fields.','wptuts-simple-contact-form'),
		"error_noemail" => __('Please enter a valid e-mail address.','wptuts-simple-contact-form'),
		"success" => __('Thanks for your e-mail! We\'ll get back to you as soon as we can.','wptuts-simple-contact-form'),
		"css_class" => ''
	), $atts));

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$error = false;
		$required_fields = array("your_name", "email", "message", "subject");

		foreach ($_POST as $field => $value) {
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			$form_data[$field] = strip_tags($value);
		}

		foreach ($required_fields as $required_field) {
			$value = trim($form_data[$required_field]);
			if(empty($value)) {
				$error = true;
				$result = $error_empty;
			}
		}

		if(!is_email($form_data['email'])) {
			$error = true;
			$result = $error_noemail;
		}

		if ($error == false) {
			$email_subject = "[" . get_bloginfo('name') . "] " . $form_data['subject'];
			$email_message = $form_data['message'] . "\n\nIP: " . wptuts_get_the_ip();
			$headers  = "From: ".$form_data['your_name']." <".$form_data['email'].">\n";
			$headers .= "Content-Type: text/plain; charset=UTF-8\n";
			$headers .= "Content-Transfer-Encoding: 8bit\n";
			wp_mail($email, $email_subject, $email_message, $headers);
			$result = $success;
			$sent = true;
		}
	}

	if($result != "") {
		$info = '<div class="info">'.$result.'</div>';
	}
	$email_form = '<form class="contact-form '.$css_class.'" method="post" action="'.get_permalink().'" role="form">
		<div class="form-group">
			<label for="cf_name">'.$label_name.':</label>
			<input type="text" class="form-control" name="your_name" id="cf_name" size="50" maxlength="50" value="'.$form_data['your_name'].'" />
		</div>
		<div class="form-group">
			<label for="cf_email">'.$label_email.':</label>
			<input type="email" class="form-control" name="email" id="cf_email" size="50" maxlength="50" value="'.$form_data['email'].'" />
		</div>
		<div class="form-group">
			<label for="cf_subject">'.$label_subject.':</label>
			<input type="text" class="form-control" name="subject" id="cf_subject" size="50" maxlength="50" value="'.$subject.$form_data['subject'].'" />
		</div>
		<div class="form-group">
			<label for="cf_message">'.$label_message.':</label>
			<textarea name="message" id="cf_message" class="form-control" cols="50" rows="15">'.$form_data['message'].'</textarea>
		</div>
		<input type="submit" value="'.$label_submit.'" name="send" id="cf_send" class="btn btn-default" />
	</form>';
	
	if($sent == true) {
		return $info;
	} else {
		return $info.$email_form;
	}
}
add_shortcode('contact', 'wptuts_contact_form_sc');

?>