<?php
/**
 * Plugin Name: Hosting
 * Plugin URI: https://bestwebsite.com/hosting/pricing/
 * Description: Best Website Hosting Integration
 * Version: 1.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Best Website Hosting
 * Author URI: https://bestwebsite.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hosting
 */ 

/**
 * class BestwebsitePayments
 */
 if(!class_exists('BestwebsitePayments')){
 	
 	class BestwebsitePayments
 	{
		public $next_billing_date = '';
		public $invoicedue = '';
		public $invoice_amount = '';
		public $customer_info = array();
		public $admin_notice = '';
		public $customer_subscription = '';
		
 		function __construct()
 		{
 			# code...
 			require_once(ABSPATH . 'wp-admin/includes/screen.php');

			//add_action('admin_init', array($this, 'paywhirl_settings'));
 			add_action('admin_enqueue_scripts', array($this, 'bw_scripts'));
 			add_action('admin_init', array($this,'check_paywhirl_status'));
			add_action('admin_menu', array($this, 'bwpayments_menu'));
			add_action('current_screen', array($this, 'redirect_to_bwsettings_page'));			
			add_action('wp_ajax_email_verify_code', array($this, 'email_verify_code'));
			add_action('wp_ajax_verify_email', array($this, 'verify_email'));
			add_action('init', array($this, 'check_user_payment_status'));
			add_action('wp_ajax_update_addr', array($this, 'update_addr'));
 		}

 		public function check_paywhirl_status(){
 			//get user id from wp settings 
 			$customerid = get_option('bwcustomerid');

			if($customerid){ 
				$user_subcription = $this->getpaywhirlInfo('getSubscriptions');
				$this->customer_subscription = $user_subcription[0];
				$this->customer_info['info'] = $this->getpaywhirlInfo('getCustomer');
				$GLOBALS['bwcustomerid'] = get_option('bwcustomerid');
				$GLOBALS['bwcustomeremail'] = $this->customer_info['info']->email;
				
				if(!$this->customer_info['info']->email){
					$this->admin_notice = "invalidcid";
					add_action('admin_notices',array($this, 'admin_notice_show'));

				}

				// if(get_option('bwemail') != $this->customer_info['info']->email){
				// 		$this->admin_notice = 'mismatch';
 			    // 		add_action('admin_notices', array($this,'admin_notice_show'));
				// }
				//print_r($user_subcription);

				if($user_subcription[0]->plan->billing_amount):
					if(date('Y-m-d', $user_subcription[0]->current_period_end)){
						$this->next_billing_date = date('Y-m-d', $user_subcription[0]->current_period_end);
						$this->invoicedue = $user_subcription[0]->plan->billing_amount . '' . $user_subcription[0]->plan->currency;
						//get upcoming invoices
						$invoice = $this->getpaywhirlInfo('getInvoices');
						$this->invoice_amount = $invoice[0]->amount_due . ' '.$invoice[0]->currency;
 						//add_action('admin_notices', array($this,'add_paywhirl_notice'));
					}
				endif;
				if($this->customer_info['info']->email){
					$daysto_nextbilling = $this->checkDate();
					if($daysto_nextbilling <= 5){
						$this->admin_notice = 'invoicenotice';
						add_action('admin_notices', array($this, 'admin_notice_show'));
					}
				}else{
					$this->admin_notice = 'invalidcid';
					add_action('admin_notices', array($this, 'admin_notice_show'));
				}
			}
 			if(!$customerid){
 				$this->admin_notice = 'noiderr';
 				add_action('admin_notices', array($this, 'admin_notice_show'));
 			}
 		}

 		public function paywhirl_settings()
 		{
 			# code...
			register_setting('general', 'paywhirl_customerid');
 			add_settings_field(
 				'paywhirl_customerid',
 				'Bestwebsite Customer ID',
 				array($this,'paywhirl_settings_callback'),
 				'general',
 				'default',
 				array( 'label_for' => 'paywhirl_customerid' )
 			);
 		}

 		public function paywhirl_settings_callback()
 		{
 			$paywhirl_customer_id = get_option('paywhirl_customerid');

 			echo '<input type="text" value="'.$paywhirl_customer_id.'" class="regular-text" name="paywhirl_customerid" id="paywhirl_customerid" placeholder="Paywhirl Customer ID" />';
 		}
		public function checkDate()
 		{
 			$next_bill_date = $this->next_billing_date;
 			$current_date = date('Y-m-d');
 			$datetime_2 = strtotime($next_bill_date);
 			$datetime_1 = strtotime($current_date);

 			$datediff = ($datetime_2-$datetime_1)/60/60/24;

 			return $datediff;
 		}
		
		//plugin options page

		public function bwpayments_menu()
		{
			//create a new toplevel menu
			add_menu_page('Hosting', 
				'Hosting', 'administrator', __FILE__, array($this,'bwpayments_settings_page'), plugin_dir_url(__FILE__).'img/bw-hosting.png');
			//call register settings
			add_action('admin_init', array($this, 'register_bwpayments_settings'));
		}

		public function redirect_to_bwsettings_page()
		{
 			if(!$_POST['bwcustomerid']){
 				if(!get_option('bwcustomerid') || !$this->customer_info['info']->email){
 					$screen = get_current_screen();
 					//print_r($screen);
					if($screen->base !== 'toplevel_page_hosting/hosting'){
			 			header('Location: /wp-admin/admin.php?page=hosting%2Fhosting.php');
 						die();
 					} 
				}
			}
 		}

		public function register_bwpayments_settings()
		{
			//register plugin settings
			register_setting('bwpayments-settings', 'bwcustomerid');
			//register_setting('bwpayments-settings', 'bwemail');
			//register_setting('bwpayments-settings', 'bwpaymentmethod');
		}

		public function bwpayments_settings_page()
		{
			?>
			<div class="wrap bw-wrap">
				<div class="bw-header">
					<a href="https://bestwebsite.com/" target="_blank">
						<img src="https://bestwebsite.com/wp-content/uploads/2017/08/logo-dark.png" alt="" />
					</a>
					<div class="button-section">
						<a href="https://bestwebsite.com/contact/" class="button button-primary" target="_blank">Contact</a>
					</div>
				</div>
					<div class="bw-form first-section">
				<h1>Best Website Hosting Account</h1>
				<form method="POST" action="options.php">
					<?php
					 settings_fields('bwpayments-settings'); 
					 do_settings_sections('bwpayments-settings');
					?>
						<p><label for="bwcustomerid">Customer ID</label><br />
							<input type="text" name="bwcustomerid" id="bwcustomerid" placeholder="Enter your customer id" class="wide-fat" value="<?php echo esc_attr(get_option('bwcustomerid')); ?>" /></p>
							<?php
							/* echo 	'<p>
								<label for="bwemail">Email Address (Make sure to enter email id associated with your customer id)</label><br />
									<input style="padding: 10px; max-width: 80%; width: 500px;" type="email" name="bwemail" name="bwemail" id="bwemail" placeholder="Enter Email" value=" echo esc_attr(get_option("bwemail"));">
								
							</p>'; */
							?>
							<?php submit_button(); ?>
							<?php if(!get_option('bwcustomerid')){
							 //!= $this->customer_info['info']->email){
								echo '<h4>Where are these? <a href="https://bestwebsite.com/contact/" target="_blank" class="btn btn-primary">Contact Support</a></h4>';
							}
							//print_r(get_current_screen()->base);
							
							?>			
				</form>
				<?php 
					if(get_option('bwcustomerid') && isset($this->customer_info['info']->email)){ //== $this->customer_info['info']->email){
					
				?>
				<h3>
					Billing Details </h3>
						<h4><button type="button" id="bwp_updateInfo" class="button">Update address or account details</button></h4>
						<p class="email-verify-text">
							We are sending a verification code to the email address associated with this account....
						</p>
						<p class="email-verify">
							<label for="email-verify">Enter verification code sent to hosting account email</label>
							<input type="text" name="email-verify" id="email-verify" class="bwinfo-input"/>
							<button type="button" class="verifycode-btn">Verify</button>
								
						</p>
					<p>
						<label for="first_name">First Name</label><br/>
						<input type="text" class="bwinfo-input" name="first_name" id="first_name" readonly value="<?php echo $this->customer_info['info']->first_name; ?>">
					</p>
					<p>
						<label for="last_name">Last Name</label><br/>
						<input type="text" class="bwinfo-input" name="last_name" id="last_name" readonly value="<?php echo $this->customer_info['info']->last_name; ?>">
					</p>
					<p>
						<label for="phone">Phone</label><br/>
						<input type="text" class="bwinfo-input" name="phone" id="phone" readonly value="<?php echo $this->customer_info['info']->phone; ?>">
					</p>
										<p>
						<label for="address">Address</label><br/>
						<input type="text" class="bwinfo-input" name="address" id="address" readonly value="<?php echo $this->customer_info['info']->address; ?>">
					</p>

					<p>
						<label for="city">City</label><br/>
						<input type="text" class="bwinfo-input" name="city" id="city" readonly value="<?php echo $this->customer_info['info']->city; ?>">
					</p>
										<p>
						<label for="state">State</label><br/>
						<input type="text" class="bwinfo-input" name="state" id="state" readonly value="<?php echo $this->customer_info['info']->state; ?>">
					</p>

										<p>
						<label for="zip">Zip Code</label><br/>
						<input type="text" class="bwinfo-input" name="zip" id="zip" readonly value="<?php echo $this->customer_info['info']->zip; ?>">
					</p>

										<p>
						<label for="country">Country</label><br/>
						<input type="text" readonly class="bwinfo-input" name="country" id="country" value="<?php echo $this->customer_info['info']->country; ?>">
					</p>
						<p>
							<button type="submit" class="button button-primary" disabled id="bw_updateAddr">Submit</button>
						</p>
					<?php } ?>
				</div>
					<?php
						if(get_option('bwcustomerid')){ //== $this->customer_info['info']->email){
					?>
				<div class="second-section hosting-info">
				<?php if($this->customer_info['info']->email){ ?>
					<h3>
					Hosting Plan Details
					</h3>
					Dedicated IP: <span><?php echo $_SERVER['SERVER_ADDR']; ?></span><br />
					Current Plan: <span><?php echo $this->customer_subscription->plan->name; ?></span><br/>
					Monthly Price: <span><?php echo $this->invoice_amount; ?></span><br/>
					Next Billing Date: <span><?php echo $this->next_billing_date; ?></span><br/>
					<a href="https://bestwebsite.com/accounts/" target="_blank" class="button button-paynow">Manage Account</a>
				<?php } ?>
					<div class="server-status">
						
					<h3>
						Server Status
					</h3>
						<ul>
							<li><strong>Loaded In: </strong> <?php echo $this->get_server_response_time(); ?></li>
<!-- 							<li><strong>Uptime - </strong><?php echo $this->uptime(); ?></li> -->
							<li><strong>WordPress Version: </strong> <?php echo get_bloginfo('version'); ?></li>
							<li><strong>PHP Version: </strong> <?php echo phpversion(); ?></li>
					<li><strong>MySQL Version: </strong> <?php
							$mysql_client_version = mysqli_get_client_info(); 
							if($mysql_client_version){
								$mysqli_ver =  explode('-dev -', $mysql_client_version);
								$mysqli_version = explode(' ', $mysqli_ver[0]);
								echo $mysqli_version[1];
							}
						?></li>
							<li><strong>Memory: </strong>Using <?php 
							$memory_usage = @round(memory_get_usage()/pow(1024,2)); 
							$memory_limit = ini_get('memory_limit'); 
							$memory_limit_number = explode('M',$memory_limit);
								echo $memory_usage.'MB of '.$memory_limit.'B'; ?>
							<br/><?php $widthper = @round(($memory_usage*100)/$memory_limit_number[0]); ?>
								<span class="progressbar"><span style="background-color:green; width:<?php echo $widthper; ?>%;"></span></span>
							</li>
<!-- 							<li><strong>File Upload Limit: </strong> <?php echo ini_get('upload_max_filesize'); ?>B</li> -->
							<li><strong>Disk Space: </strong><?php 
								$total_space = @floor(disk_total_space('/dev')/pow(1024, 2)/7629.395);
								$free_space = @round(disk_free_space('/dev')/pow(1024, 2)/7629.395);
								$widthper = ($free_space*100)/$total_space; 
							$disk_info = shell_exec('df -H /dev/xvda1');
							$disk_info = preg_replace("/\s+/", " ", $disk_info);
							$disk_array = explode(' ', $disk_info);
							//echo json_encode($disk_array);
							echo 'Using '.$disk_array[9].'B of '. $disk_array[8].'B';?>
							<span class="progressbar"><span style="background-color:green; width:<?php
							echo $disk_array[11];	?>;"></span></span>
</li>
						<li>
						<strong>CPU Usage:</strong>
							<br />
						
						<div class="cpuChart" width="100%" height="400"></div>
						<?php echo $this->uptime(); ?>
							</li>
						</ul>
				</div>
				<?php } ?>
				
			</div>
			<?php
		}
		public function bw_scripts() {
			wp_enqueue_style('bw-css', plugin_dir_url(__FILE__).'css/bw.css');
			wp_enqueue_style('chartist-css', 'https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.css');
			wp_enqueue_script('chartist-js', 'https://cdnjs.cloudflare.com/ajax/libs/chartist/0.11.4/chartist.min.js', array('jquery'), false, false);
			wp_enqueue_script('bw-js', plugin_dir_url(__FILE__).'js/bw.js', array('jquery'), false, false);
		}
		
		public function admin_notice_show()
		{
			$msg_to_show = '';
			$msgclass = 'notice-error';
			if($this->admin_notice == 'mismatch'){
				$msg_to_show = 'The hosting account Customer ID provided cannot be found. Please add an active Customer ID to avoid service interruption. <a href="/wp-admin/admin.php?page=bestwebsite-payments%2Fbestwebsite-payments.php">Update Now</a>';
				$msgclass = 'notice-error';
			}else if($this->admin_notice == 'noiderr'){
				$msg_to_show = 'Please add your hosting account Customer ID to avoid service interruption. <a href="/wp-admin/admin.php?page=bestwebsite-payments%2Fbestwebsite-payments.php">Update Now</a>';
				$msgclass = 'notice-warning';
			}else if($this->admin_notice == "invoicenotice"){
				$msg_to_show = 'An automatic payment is scheduled on '.$this->next_billing_date.' for '.$this->invoice_amount;
				$msgclass = 'notice-warning';
			}else if($this->admin_notice == 'invalidcid'){
				$msg_to_show = 'The hosting account Customer ID provided is incorrect, please update now to avoid service interruption.';
			}
			?>
			<div class="bw-notice notice <?php echo $msgclass; ?> is-dismissible">
				<p style="font-size:1rem;"><?php _e($msg_to_show, 'best-website-payments'); ?></p>
			</div>
			<?php
		}
		
		//ajax calls
		public function email_verify_code(){
			//get customer email address using customer id
			$customerid = get_option('bwcustomerid');
			$customer_email_obj = $this->getpaywhirlInfo('getCustomerInfo');
			$customer_email = $customer_email_obj->email;
			//generate random code and create a new wp transient
			if($customer_email){
				$subject = 'Email Verification Code';
				$code_to_verify = rand(0,999999);
				$message = $code_to_verify.' is your verification code to update address on Best Website Hosting Portal, code valid for 1 hour.';
				$headers[] = 'From: Best Website <info@bestwebiste.com>'; 
				set_transient('bw_email_verification_code', $code_to_verify, 1 * HOUR_IN_SECONDS);
				if(wp_mail($customer_email, $subject, $message)){
					echo 'success';
				}else{
					echo 'error';
				}
				die();
				
			}
		}
		
		public function verify_email(){
			$verify_code = get_transient('bw_email_verification_code');
			if($_POST['verification_code']){
				if($_POST['verification_code'] === $verify_code){
					echo 'verified';
				}else{
					echo 'invalid';
				}
			}else{
				echo 'missing code';
			}
			die();
		}
		
		public function uptime(){
			$uptime = sys_getloadavg();
			//$uptime = @file_get_contents('/proc/meminfo');
			return"
			<script>
				jQuery(document).ready(function(){
					new Chartist.Line('.cpuChart', {
				labels: ['1 Min', '5 Min', '10 Min', '15 Min', '20 Min', '25 Min'],
  				series: [
            		[".$uptime[0].", ".$uptime[1].", ".$uptime[2].", ".$uptime[0].", ".$uptime[1]*1.1.", ".$uptime[0]."]
  				]
				}, {
 				low: 0,
            	showArea: true
			});
				});
			</script>";
		//	return date('d-m-Y', time() - [1]));
		}
		public function get_server_response_time(){
			$time1 = microtime(true);
			$url = get_bloginfo('url');
			$time2 = microtime(true);
			$http_resp = @file_get_contents($url);
			$resp_time = ($time2 - $time1) * 10000;
			return number_format($resp_time, 2) .'s';
			
		}
		
		public function update_addr(){
			if($_POST){
				$customerdata = array(
					'id' => $GLOBALS['bwcustomerid'],
					'first_name' => $_POST['first_name'],
					'last_name' => $_POST['last_name'],
					'email' => $GLOBALS['bwcustomeremail'],
					'phone' => $_POST['phone'],
					'address' => $_POST['address'],
					'city' => $_POST['city'],
					'state' => $_POST['state'],
					'zip' => $_POST['zip'],
					'country' => $_POST['country'],
				);
				$customerdata = json_encode($customerdata);
				$api_url = 'https://bestwebsite.com/api/account.php?action=updateCustomer&cdata='.$customerdata;
				$ch = curl_init();
				curl_setopt_array($ch, array(
					'CURLOPT_URL' => $api_url,
					'CURLOPT_HTTPGET' => 1,
					'CURLOPT_RETURNTRANSFER' => 1,
				));
				$resp = curl_exec($ch);
				curl_close($ch);
				$update_customer = $resp;
				if($update_customer->id){
					echo 'updated';
				}else{
					echo 'error';
				}
			}
			die();
		}

		public function check_user_payment_status()
		{
			# code...
			$customerid = get_option('bwcustomerid');
			if($customerid){
			   $customer_info = $this->getpaywhirlInfo('getCustomer');
			   if($customer_info && $customer_info->email){
					if(!$this->next_billing_date){
					$user_subcription = $this->getpaywhirlInfo('getSubscriptions');
					$this->next_billing_date = date('Y-m-d', $user_subcription[0]->current_period_end);
					}
				
					$nextbilldate = $this->checkDate();
					if($nextbilldate <= -1){
						$paymentSttaus = 'unpaid';
						add_filter('login_message', array($this,'payment_pending_notice'));
						wp_logout();
						add_filter('login_message', array($this,'payment_pending_notice'));
						//wp_redirect('/wp-login.php');
					}
			    }	
			}
		}

		public function getpaywhirlInfo($action)
		{
			# code...
			if(get_option('bwcustomerid')){
				$api_url = 'https://bestwebsite.com/api/account.php?action='.$action.'&cid='.get_option('bwcustomerid'); 
				$ch = curl_init();
				curl_setopt_array($ch, array(
					'CURLOPT_URL' => $this->api_url,
					'CURLOPT_HTTPGET' => 1,
					'CURLOPT_RETURNTRANSFER' => 1,
 				));
 				$resp = curl_exec($ch);
 				curl_close($ch);
 				return $resp;
 			}else {
 				return '';
 			}
		}

		public function payment_pending_notice($message)
		{
			# code...
			return '<p style="color:#FFF; padding:10px; font-size:1rem; background-color:#f33a00;">We are unable to process your request because of a past due balance on your hosting account. To make a payment, please login and update your billing details in your <a href="https://bestwebsite.com/accounts/" target="_blank" style="color:#fff;">hosting account</a>.</p>';
		}
 	}
 } 

 $GLOBALS['best-website-payments'] = new BestwebsitePayments();
