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

require_once(NCMBPostNotification_PLUGIN_DIR . 'ncmb-client.php');

class NCMBPostNotificationImplements {

	public function __construct() {
		add_action('transition_post_status', array($this, 'on_transition_post_status'), 10, 3);
	}

	public function on_transition_post_status($new_status, $old_status, $post) {
		if ($new_status == 'publish') {
			$data = array(
				'immediateDeliveryFlag' => true,
				'target' => array(),
				'title' => mb_substr($post->post_title, 0, 20),
				'message' => mb_substr($post->post_content, 0, 50),
				'deliveryExpirationTime' => '10 day',
			);

			$options = get_option('ncmb_post_notification_option');

			if (isset($options['to_ios'])) {
				$data['target'][] = 'ios';
				$data['sound'] = 'default';
				$data['badgeIncrementFlag'] = true;
			}

			if (isset($options['to_android'])) {
				$data['target'][] = 'android';
			}

			$ncmb_client = new NCMBClient($options['application_key'], $options['client_key']);

			$ncmb_client->post('/push', json_encode($data));
		}
	}
}
?>
