<?php
/*
	Post Notification by NIFTY Cloud mobile backend  v0.0.1
*/
/*
        Copyright 2015 nd.yuya

        Licensed under the Apache License, Version 2.0 (the "License");
        you may not use this file except in compliance with the License.
        You may obtain a copy of the License at

                http://www.apache.org/licenses/LICENSE-2.0

        Unless required by applicable law or agreed to in writing, software
        distributed under the License is distributed on an "AS IS" BASIS,
        WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
        See the License for the specific language governing permissions and
        limitations under the License.
*/

class NCMBPostNotificationSettingsPage {
	private $options;
  
	public function __construct() {
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'page_init'));    
	}

	public function add_plugin_page() {
		add_options_page(
			'Post Notification by NIFTY Cloud mobile backend',
			'更新通知 (NCMB)',
			'manage_options',
			'ncmb-post-notification',
			array($this, 'create_setting_page')
		);
	}
  
	public function create_setting_page() {
		$this->options = get_option('ncmb_post_notification_option');
?>
	<div class="wrap">
<?php screen_icon(); ?>
		<h2>更新通知 (NIFTY Cloud mobile backend)</h2>
		<form method="post" action="options.php">
<?php
		settings_fields('ncmb_post_notification_option_group');
		do_settings_sections('ncmb-post-notification');
		submit_button();
?>
		</form>
	</div>
<?php
	}

	public function page_init() {
		register_setting(
			'ncmb_post_notification_option_group',
			'ncmb_post_notification_option',
			array($this, 'sanitize')
		);

		add_settings_section(
			'ncmb-post-notification-section',
			'',
			'',
			'ncmb-post-notification'
		);

		add_settings_field(
			'application_key',
			'Application Key',
			array($this, 'application_key_callback'),
			'ncmb-post-notification',
			'ncmb-post-notification-section'
		);

		add_settings_field(
			'client_key',
			'Client Key',
			array($this, 'client_key_callback'),
			'ncmb-post-notification',
			'ncmb-post-notification-section'
		);
	}

	public function sanitize($input) {
		$new_input = array();

		if (isset($input['application_key']))
			$new_input['application_key'] = sanitize_text_field($input['application_key']);

		if (isset($input['client_key']))
			$new_input['client_key'] = sanitize_text_field($input['client_key']);

		return $new_input;
	}

	public function application_key_callback() {
		printf(
			'<input type="text" id="application_key" name="ncmb_post_notification_option[application_key]" value="%s" />',
			isset($this->options['application_key']) ? esc_attr($this->options['application_key']) : ''
		);
	}

	public function client_key_callback() {
		printf(
			'<input type="text" id="client_key" name="ncmb_post_notification_option[client_key]" value="%s" />',
			isset($this->options['client_key']) ? esc_attr($this->options['client_key']) : ''
		);
	}
}

?>
