<?php
/*
	Plugin Name: Post Notification with NIFTY Cloud mobile backend
	Plugin URI: http://mb.cloud.nifty.com/
	Description: 記事が公開されると、ニフティクラウドmobile backendを通じてプッシュ通知を配信する
	Author: nd.yuya
	Author URI: http://www.nifty.co.jp/
	Version: 0.0.2
	License: Apache License, Version 2.0
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

define('NCMBPostNotification_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (is_admin()) {
	require_once(NCMBPostNotification_PLUGIN_DIR . 'ncmb-post-notification-settings-page.php');
	$ncmb_post_notification_settings_page = new NCMBPostNotificationSettingsPage();
}

require_once(NCMBPostNotification_PLUGIN_DIR . 'ncmb-post-notification-implements.php');
$ncmb_post_notification_implements = new NCMBPostNotificationImplements();

?>
