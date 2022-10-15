<?php
define('AFI_INFO_KEY', 'afi_info');
include __DIR__ . '/main.php';

if ( ! defined( 'AFI_PLUGIN_FILE' ) ) {
	define( 'AFI_PLUGIN_FILE', __FILE__ );
}

class AFFILIO {
	private $affilio_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'affilio_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'affilio_page_init' ) );
        $this->init();
	}

    private function init(){
		// wp_register_style( 'affilio-style', plugin_dir_path(AFI_PLUGIN_FILE) . 'style.css', array(), 324234);
		// wp_enqueue_style( 'affilio-style');

		$affilio_options = get_option( 'affilio_option_name' );
		$username = $affilio_options['username'];
		$password = $affilio_options['password'];
		if($username){
			$main = new MainClass();
			// $main->auth_login($username, $password);
		}
        // global $wpdb;
        // $table = $wpdb->options;
        // $afi_sent_cats_name = AFI_INFO_KEY;
        // $results = $wpdb->get_results("SELECT * FROM {$table} WHERE option_name = '{$afi_sent_cats_name}'", OBJECT);
        // $option_value = $results[0]->option_value;
        // $data = json_decode($option_value);
        // $this->affilio_options['username'] = $data->username;
        // var_dump($data);
        // echo "<br/>";
        // var_dump($data->username);
        // var_dump($data->password);
        // $this->affilio_options['password'] = $data->password;
        // $this->affilio_options['webstore'] = $data->webstore . "xxxxx";
		
    }

	private function syncMethod () {
		$main = new MainClass();
		$affilio_options = get_option( 'affilio_option_name' );
		$username = $affilio_options['username']; // username
		$password = $this->wp_encrypt($affilio_options['password'], 'd'); // password
		$result = $main->auth_login($username, $password);
		if($result){
			// var_dump($GLOBALS['bearer']);
			// echo "Sync_Loading";
			$main->init_categories();
			$main->init_products();
			// $main->init_orders();
			$msg = '<div id="message" class="updated notice is-dismissible"><p>همگام سازی با موفقیت انجام شد</p></div>';
            echo $msg;
			// echo "Sync_Completed";
		}
	}

    private function callbackForm (){
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == "true" ) {
			$main = new MainClass();
			$affilio_options = get_option( 'affilio_option_name' );
			$username = $affilio_options['username']; // username
			$password = $this->wp_encrypt($affilio_options['password'], 'd'); // password
			$result = $main->auth_login($username, $password);
			if($result){
				$msg = '<div id="message" class="updated notice is-dismissible"><p>اتصال به پنل افیلو با موفقیت انجام شد</p></div>';
				echo $msg;
				// show success login
			}
		}
		// $result = $main->auth_login($);
		// WP_Error( __( 'Sorry, you are not allowed to manage options for this site.' ) );
		// show_message( __( 'WordPress updated successfully.' ) );
		// error_log( __( 'WordPress updated successfully.' ) );
		// if ( ! empty( $result ) ) {
		// 	$msg = '<div id="message" class="error notice is-dismissible"><p>' . $result . '</p></div>';
		// }
		// echo $msg;

		// return new WP_Error( 'bad_request', $result );
		
		// $this->affilio_options = get_option( 'affilio_option_name' );
        // $affilio_options = $this->affilio_options;
        // $username = $affilio_options['username']; // username
        // $password = $affilio_options['password']; // password
        // $webstore = $affilio_options['webstore']; // webstore
        
        // if($username){
        //     $table = $wpdb->options;
        //     $values = json_encode(array(
        //         'username' => $username,
        //         'password' => wp_hash_password($password),
        //         'webstore' => $webstore,
        //     ));
        //     $data = array('option_name' => AFI_INFO_KEY, 'option_value' => $values);
        //     $format = array('%s', '%s');
        //     $result = $wpdb->insert($table, $data, $format);
        //     if(!$result){
        //         $where = array('option_name' => AFI_INFO_KEY);
        //         $result = $wpdb->update($table, $data, $where);
        //         var_dump($result);
        //     }
        // }
    }

	public function affilio_add_plugin_page() {
		add_options_page(
			'AFFILIO', // page_title
			'AFFILIO', // menu_title
			'manage_options', // capability
			'affilio', // menu_slug
			array( $this, 'affilio_create_admin_page' ) // function
		);
	}

	function sync_quick() {
		$this->syncMethod();
	}
	function sync_all() {
		$this->syncMethod();
	}

	public function affilio_create_admin_page() {
        $this->callbackForm();
		$affilio_options = get_option( 'affilio_option_name' );
		$username = $affilio_options['username']; // username
		if(array_key_exists('sync_quick', $_POST)) {
            $this->sync_quick();
        }
        else if(array_key_exists('sync_all', $_POST)) {
            $this->sync_all();
        }
		
         ?>
		<div class="wrap">
			<h1>افزونه همگام سازی ووکامرس با پلت فرم افیلیو</h1>
			<p>این افزونه تمامی فرایندهای لازم جهت همگام سازی با پلت فرم افیلیو را به صورت خودکار پیاده سازی میکند.</p>

			<table>
				<tr>
					<td>
						<?php settings_errors(); ?>
						<form method="post" action="options.php">
							<?php
								settings_fields( 'affilio_option_group' );
								do_settings_sections( 'affilio-admin' );
								submit_button();
							?>
						</form>
					</td>
					<td>
						<h2>همگام سازی داده ها</h2>
						<form method="post">
						<div>
							<h3>همگام سازی سریع</h3>
							<p>در صورتی که قبلا فرایند همگام سازی دادها با پنل افیلو را انجام داده اید و اطلاعات شما 
								در پنل افیلیو 
								<b>ناقص است</b>
								بر روی دکمه زیر کلیک نمایید:
							</p>
							<button name="sync_quick" >همگام سازی اطلاعات</button>
						</div>
						<div>
							<h3>همگام سازی کامل</h3>
							<p>
							جهت همگام سازی داده های موجود در سایت، با پنل افیلیو لطفا بر روی
							دکمه زیر کلیک نمایید.
							<br/>
							توجه داشته باشید، این فرایند بسته به محصولات، دسته بندی ها و سفارشات شما میتواند کمی زمان بر باشد.
							</p>
							<button name="sync_all">همگام سازی اطلاعات</button>
						</div>
						</form>
					</td>
				</tr>
			</table>
		</div>
	<?php }

	public function affilio_page_init() {
		register_setting(
			'affilio_option_group', // option_group
			'affilio_option_name', // option_name
			array( $this, 'affilio_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'affilio_setting_section', // id
			'تنظیمات پلاگین', // title
			array( $this, 'affilio_section_info' ), // callback
			'affilio-admin' // page
		);

		add_settings_field(
			'username', // id
			'نام کاربری پنل', // title
			array( $this, 'username_callback' ), // callback
			'affilio-admin', // page
			'affilio_setting_section' // section
		);

		add_settings_field(
			'password', // id
			'رمز عبور پنل', // title
			array( $this, 'password_callback' ), // callback
			'affilio-admin', // page
			'affilio_setting_section' // section
		);

		add_settings_field(
			'webstore', // id
			'شناسه فروشگاه', // title
			array( $this, 'webstore_callback' ), // callback
			'affilio-admin', // page
			'affilio_setting_section' // section
		);
	}

	public function affilio_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['username'] ) ) {
			$sanitary_values['username'] = sanitize_text_field( $input['username'] );
		}

		if ( isset( $input['password'] ) ) {
			$sanitary_values['password'] = sanitize_text_field( $this->wp_encrypt($input['password'] ) );
		}

		if ( isset( $input['webstore'] ) ) {
			$sanitary_values['webstore'] = sanitize_text_field( $input['webstore'] );
		}


		return $sanitary_values;
	}

	public function affilio_section_info() {
		?>
		<h2>تنظیمات اولیه</h2>
		<p>لطفا اطلاعات زیر را تکمیل نمایید</p>
	<?php }

	public function username_callback() {
		printf(
			'<input class="regular-text" type="text" name="affilio_option_name[username]" id="username" value="%s">',
			isset( $this->affilio_options['username'] ) ? esc_attr( $this->affilio_options['username']) : ''
		);
	}

	public function password_callback() {
		// printf(
		// 	'<input class="regular-text" type="text" name="affilio_option_name[password]" id="password" value="%s">',
		// 	isset( $this->affilio_options['password'] ) ? esc_attr( $this->affilio_options['password']) : ''
		// );
        printf(
			'<input class="regular-text" type="password" name="affilio_option_name[password]" id="password">'
		);
	}

	public function webstore_callback() {
		printf(
			'<input class="regular-text" type="text" name="affilio_option_name[webstore]" id="webstore" value="%s">',
			isset( $this->affilio_options['webstore'] ) ? esc_attr( $this->affilio_options['webstore']) : ''
		);
	}

	/* These need to go in your custom plugin (or existing plugin) */
	private function wp_encrypt($stringToHandle = "", $encryptDecrypt = 'e') {
		// // Set default output value
		return eval(base64_decode("ZGVmaW5lKCdBRklfS0VZJywgJ25mOGdmOGFeMypzJyk7DQoJCWRlZmluZSgnQUZJX0lWJywgJ3MmJjkiZGE0JTpAJyk7DQoJCSRvdXRwdXQgPSBudWxsOw0KCQkvLyBTZXQgc2VjcmV0IGtleXMNCgkJJHNlY3JldF9rZXkgPSBBRklfS0VZOyANCgkJJHNlY3JldF9pdiA9IEFGSV9JVjsNCgkJJGtleSA9IGhhc2goJ3NoYTI1NicsJHNlY3JldF9rZXkpOw0KCQkkaXYgPSBzdWJzdHIoaGFzaCgnc2hhMjU2Jywkc2VjcmV0X2l2KSwwLDE2KTsNCgkJLy8gQ2hlY2sgd2hldGhlciBlbmNyeXB0aW9uIG9yIGRlY3J5cHRpb24NCgkJaWYoJGVuY3J5cHREZWNyeXB0ID09ICdlJyl7DQoJCSAgIC8vIFdlIGFyZSBlbmNyeXB0aW5nDQoJCSAgICRvdXRwdXQgPSBiYXNlNjRfZW5jb2RlKG9wZW5zc2xfZW5jcnlwdCgkc3RyaW5nVG9IYW5kbGUsIkFFUy0yNTYtQ0JDIiwka2V5LDAsJGl2KSk7DQoJCX1lbHNlIGlmKCRlbmNyeXB0RGVjcnlwdCA9PSAnZCcpew0KCQkgICAvLyBXZSBhcmUgZGVjcnlwdGluZw0KCQkgICAkb3V0cHV0ID0gb3BlbnNzbF9kZWNyeXB0KGJhc2U2NF9kZWNvZGUoJHN0cmluZ1RvSGFuZGxlKSwiQUVTLTI1Ni1DQkMiLCRrZXksMCwkaXYpOw0KCQl9DQoJCS8vIFJldHVybiB0aGUgZmluYWwgdmFsdWUNCgkJcmV0dXJuICRvdXRwdXQ7"));
   }
}

if ( is_admin() )
	$affilio = new AFFILIO();

/* 
 * Retrieve this value with:
 * $affilio_options = get_option( 'affilio_option_name' ); // Array of All Options
 * $username = $affilio_options['username']; // username
 * $password = $affilio_options['password']; // password
 * $webstore = $affilio_options['webstore']; // webstore
 */
