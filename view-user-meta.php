<?php
/*
Plugin Name: View User Metadata
Plugin URI: https://ss88.us/plugins/view-user-metadata
Description: A lightweight plugin that is easy to use and enables Administrators to view metadata (user meta) associated with users.
Version: 1.2
Author: SS88 LLC
Author URI: https://ss88.us
*/

class SS88_ViewUserMetadata {

    protected $V = 1.2;

    public static function init() {

        $C = __CLASS__;
        new $C;

    }

    function __construct() {

        global $pagenow;

        if(!current_user_can('administrator')) return;

        if($pagenow == 'user-edit.php' || $pagenow == 'profile.php') {

            add_action('show_user_profile', [$this, 'showUserMeta']);
            add_action('edit_user_profile', [$this, 'showUserMeta']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        }

        if(is_admin()) {

            add_action('wp_ajax_SS88_VUM_delete', [$this, 'deleteMeta']);

        }

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);

    }

    function admin_enqueue_scripts() {

        wp_enqueue_style('SS88_VUM-user', plugin_dir_url( __FILE__ ) . 'assets/css/user.css', false, $this->V);
        wp_enqueue_script('SS88_VUM-user', plugin_dir_url( __FILE__ ) . 'assets/js/user.js', false, $this->V);

		wp_localize_script('SS88_VUM-user', 'SS88_VUM_translations', [
			'confirm_delete' => __('Are you sure you wish to permanently delete this key and value?', 'view-user-metadata'),
			'error' => __('Error:', 'view-user-metadata'),
			'success' => __('Success!', 'view-user-metadata')
		]);

    }

	function showUserMeta($U) {

        $UserMeta = get_user_meta($U->ID);
        ksort($UserMeta, SORT_STRING | SORT_FLAG_CASE);

		?>

<h2><?php _e('View User Meta', 'view-user-metadata'); ?> <input type="checkbox" id="SS88VUM-toggle" /><label for="SS88VUM-toggle">Toggle</label></h2>

<div id="SS88-VUM-table-wrapper">
    <table class="form-table" role="presentation" id="SS88-VUM-table">
        <tbody>
            <?php foreach($UserMeta as $Key => $Value) { $ValueSingle = get_user_meta($U->ID, $Key, true); ?>
            <tr>
                <th>
					<?php echo esc_html($Key); ?>
					<button class="btn-delete" data-key="<?php echo esc_html($Key); ?>" data-uid="<?php echo intval($U->ID); ?>" title="Delete this entry"><span class="dashicons dashicons-trash"></span></button>
				</th>
                <td>
                    <?php echo wp_kses_post($this->outputValue($ValueSingle)); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

		<?php

	}

    function outputValue($Value) {

        if(is_array($Value)) return '<pre>' . print_r($Value, true) . '</pre>';
        else return $Value;

    }

	function deleteMeta() {

		$UserID = intval($_POST['uid']);
		$MetaKey = sanitize_text_field($_POST['key']);
		$returnData = [];

		if(empty($MetaKey) || $UserID === 0) {

			wp_send_json_error(['httpcode' => -1, 'body' => __('Either the meta key or user ID was not supplied. Please refresh and try again.', 'view-user-metadata')]);

		}

		$MetaExists = get_user_meta($UserID, $MetaKey, true);

		if($MetaExists===false) {

			wp_send_json_error(['httpcode' => -1, 'body' => __('The meta key does not exist for this user. Nothing to delete.', 'view-user-metadata')]);

		}

		$DeleteMeta = delete_user_meta($UserID, $MetaKey);

		if($DeleteMeta) {

			wp_send_json_success(['body' => __('The meta key and value was deleted.', 'view-user-metadata')]);

		}
		else {

			wp_send_json_error(['httpcode' => -1, 'body' => __('The meta key and value was not deleted.', 'view-user-metadata')]);

		}

	}

    function plugin_action_links($actions) {
        $mylinks = [
            '<a href="https://wordpress.org/support/plugin/view-user-metadata/" target="_blank">Need help?</a>',
        ];
        return array_merge( $actions, $mylinks );
    }

	function debug($msg) {

		error_log("\n" . '[' . date('Y-m-d H:i:s') . '] ' .  $msg, 3, plugin_dir_path(__FILE__) . 'debug.log');

	}

}

if(method_exists('SS88_ViewUserMetadata', 'init')) add_action('plugins_loaded', ['SS88_ViewUserMetadata', 'init']);