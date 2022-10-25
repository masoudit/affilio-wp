<?php
include __DIR__ . '/main.php';

if ( ! defined( 'AFFILIO_PLUGIN_FILE' ) ) {
	define( 'AFFILIO_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'AFFILIO_INFO_KEY' ) ) {
	define('AFFILIO_INFO_KEY', 'afi_info');
}

class AFFILIO {
	private $affilio_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'affilio_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'affilio_page_init' ) );
        $this->init();
	}

    private function init(){
		$this->affilio_options = get_option( 'affilio_option_name' );
		$aff = $this->affilio_options;
    }

	// $type: product | category
	public function syncMethod ($type = null) {
		$main = new Affilio_Main();
		$affilio_options = get_option( 'affilio_option_name' );
		$username = $affilio_options['username']; // username
		$password = wp_encrypt($affilio_options['password'], 'd'); // password
		$result = $main->auth_login($username, $password);
		if($result){
			// var_dump($GLOBALS['bearer']);
			// echo "Sync_Loading";
			if($type === "category"){
				$result = $main->init_categories();
				return;
			}
			if($type === "products"){
				$resultP = $main->init_products();
				return;
			}
			$result = $main->init_categories();
			if($result === true){
				$resultP = $main->init_products();
				if($resultP === true){
					$msg = '<div id="message" class="updated notice is-dismissible"><p>همگام سازی با موفقیت انجام شد</p></div>';
					echo $msg;
				}
			}
			// $main->init_orders();
			// echo "Sync_Completed";
		}
	}

    private function callbackForm (){
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == "true" ) {
			$main = new Affilio_Main();
			$affilio_options = get_option( 'affilio_option_name' );
			$username = $affilio_options['username']; // username
			$password = wp_encrypt($affilio_options['password'], 'd'); // password
			$result = $main->auth_login($username, $password);
			if($result){
				$msg = '<div id="message" class="updated notice is-dismissible"><p>اتصال به پنل افیلو با موفقیت انجام شد</p></div>';
				echo $msg;
				// show success login
			}
		}
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
		// $affilio_options = get_option( 'affilio_option_name' );
		$username = $this->affilio_options['username']; // username
		if(array_key_exists('sync_quick', $_POST)) {
            $this->sync_quick();
        }
        else if(array_key_exists('sync_all', $_POST)) {
            $this->sync_all();
        }

		$isConnected = get_option("affilio_connected");
		
         ?>
		<div class="wrap">
			<h1>افزونه همگام سازی ووکامرس با پلت فرم افیلیو</h1>
			<p>این افزونه تمامی فرایندهای لازم جهت همگام سازی با پلت فرم افیلیو را به صورت خودکار پیاده سازی میکند.</p>

			<table>
				<tr>
					<td>
						<?php settings_errors(); ?>
						<form method="post" action="options.php" autocomplete="FALSE">
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
						<?php if($isConnected): ?>
						<div>
							<h3>همگام سازی سریع</h3>
							<p>در صورتی که قبلا فرایند همگام سازی دادها با پنل افیلو را انجام داده اید و اطلاعات شما 
								در پنل افیلیو 
								<b>ناقص است</b>
								بر روی دکمه زیر کلیک نمایید:
							</p>
							<button name="sync_quick" >همگام سازی اطلاعات</button>
						</div>
						<?php endif; ?>
						<?php if($username): ?>
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
						<?php endif; ?>
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
			$sanitary_values['password'] = sanitize_text_field( wp_encrypt($input['password'] ) );
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
		printf(
			'<input class="regular-text" type="password" name="affilio_option_name[password]" id="password" value="%s">',
			isset( $this->affilio_options['password'] ) ? '***' : '***'
		);
        // printf(
		// 	'<input class="regular-text" type="password" name="affilio_option_name[password]" id="password">'
		// );
	}

	public function webstore_callback() {
		printf(
			'<input class="regular-text" type="text" name="affilio_option_name[webstore]" id="webstore" value="%s">',
			isset( $this->affilio_options['webstore'] ) ? esc_attr( $this->affilio_options['webstore']) : ''
		);
	}

	/* These need to go in your custom plugin (or existing plugin) */
}

if ( is_admin() )
	$affilio = new AFFILIO();