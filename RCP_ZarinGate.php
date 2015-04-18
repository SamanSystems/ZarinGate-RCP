<?php
/*
Plugin Name: درگاه پرداخت زرین گیت برای Restrict Content Pro
Version: 1.0.0
Requires at least: 3.5
Description: درگاه پرداخت <a href="http://www.zarinpal.com/" target="_blank"> زرین گیت </a> برای افزونه Restrict Content Pro
Plugin URI: http://webforest.ir/
Author: حنّان ابراهیمی ستوده
Author URI: http://hannanstd.ir/
License: GPL 2
*/
if (!defined('ABSPATH')) exit;
require_once('HANNANStd_Session.php');
if (!class_exists('RCP_ZarinGate') ) {
	class RCP_ZarinGate {
	
		public function __construct() {
			add_action('init', array($this, 'ZarinGate_Verify_By_HANNANStd'));
			add_action('rcp_payments_settings', array($this, 'ZarinGate_Setting_By_HANNANStd'));
			add_action('rcp_gateway_ZarinGate', array($this, 'ZarinGate_Request_By_HANNANStd'));
			add_filter('rcp_payment_gateways', array($this, 'ZarinGate_Register_By_HANNANStd'));
			if (!function_exists('RCP_IRAN_Currencies_By_HANNANStd') && !function_exists('RCP_IRAN_Currencies'))
				add_filter('rcp_currencies', array($this, 'RCP_IRAN_Currencies_By_HANNANStd'));
		}

		public function RCP_IRAN_Currencies_By_HANNANStd( $currencies ) {
			unset($currencies['RIAL']);
			$currencies['تومان'] = __('تومان', 'rcp_zaringate');
			$currencies['ریال'] = __('ریال', 'rcp_zaringate');
			return $currencies;
		}
				
		public function ZarinGate_Register_By_HANNANStd($gateways) {
			global $rcp_options;
			$gateways['ZarinGate'] = $rcp_options['zaringate_name'] ? $rcp_options['zaringate_name'] : __( 'زرین گیت', 'rcp_zaringate');
			return $gateways;
		}

		public function ZarinGate_Setting_By_HANNANStd($rcp_options) {
		?>	
			<hr/>
			<table class="form-table">
				<?php do_action( 'RCP_ZarinGate_before_settings', $rcp_options ); ?>
				<tr valign="top">
					<th colspan=2><h3><?php _e( 'تنظیمات زرین گیت', 'rcp_zaringate' ); ?></h3></th>
				</tr>				
				<tr valign="top">
					<th>
						<label for="rcp_settings[zaringate_server]"><?php _e( 'سرور زرین گیت', 'rcp_zaringate' ); ?></label>
					</th>
					<td>
						<select id="rcp_settings[zaringate_server]" name="rcp_settings[zaringate_server]">
							<option value="German" <?php selected('German', $rcp_options['zaringate_server']); ?>><?php _e( 'آلمان', 'rcp_zaringate' ); ?></option>
							<option value="Iran" <?php selected('Iran', $rcp_options['zaringate_server']); ?>><?php _e( 'ایران', 'rcp_zaringate' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="rcp_settings[zaringate_merchant]"><?php _e( 'مرچنت زرین گیت', 'rcp_zaringate' ); ?></label>
					</th>
					<td>
						<input class="regular-text" id="rcp_settings[zaringate_merchant]" style="width: 300px;" name="rcp_settings[zaringate_merchant]" value="<?php if( isset( $rcp_options['zaringate_merchant'] ) ) { echo $rcp_options['zaringate_merchant']; } ?>"/>
					</td>
				</tr>				
				<tr valign="top">
					<th>
						<label for="rcp_settings[zaringate_query_name]"><?php _e( 'نام لاتین درگاه', 'rcp_zaringate' ); ?></label>
					</th>
					<td>
						<input class="regular-text" id="rcp_settings[zaringate_query_name]" style="width: 300px;" name="rcp_settings[zaringate_query_name]" value="<?php echo $rcp_options['zaringate_query_name'] ? $rcp_options['zaringate_query_name'] : 'ZarinGate'; ?>"/>
						<div class="description"><?php _e( 'این نام در هنگام بازگشت از بانک در آدرس بازگشت از بانک نمایان خواهد شد . از به کاربردن حروف زائد و فاصله جدا خودداری نمایید . این نام باید با نام سایر درگاه ها متفاوت باشد .', 'rcp_zaringate' ); ?></div>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label for="rcp_settings[zaringate_name]"><?php _e( 'نام نمایشی درگاه', 'rcp_zaringate' ); ?></label>
					</th>
					<td>
						<input class="regular-text" id="rcp_settings[zaringate_name]" style="width: 300px;" name="rcp_settings[zaringate_name]" value="<?php echo $rcp_options['zaringate_name'] ? $rcp_options['zaringate_name'] : __( 'زرین گیت', 'rcp_zaringate'); ?>"/>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label><?php _e( 'تذکر ', 'rcp_zaringate' ); ?></label>
					</th>
					<td>
						<div class="description"><?php _e( 'از سربرگ مربوط به ثبت نام در تنظیمات افزونه حتما یک برگه برای بازگشت از بانک انتخاب نمایید . ترجیحا نامک برگه را لاتین قرار دهید .<br/> نیازی به قرار دادن شورت کد خاصی در برگه نیست و میتواند برگه ی خالی باشد .', 'rcp_zaringate' ); ?></div>
					</td>
				</tr>
				<?php do_action( 'RCP_ZarinGate_after_settings', $rcp_options ); ?>
			</table>
			<?php
		}
		
		public function ZarinGate_Request_By_HANNANStd($subscription_data) {
			
			global $rcp_options;
			
			$query = $rcp_options['zaringate_query_name'] ? $rcp_options['zaringate_query_name'] : 'ZarinGate';
			$amount = $subscription_data['price'];
			//fee is just for paygate recurring or ipn gateway ....
			//$amount = $subscription_data['price'] + $subscription_data['fee']; 
			$zaringate_payment_data = array(
				'user_id'             => $subscription_data['user_id'],
				'subscription_name'     => $subscription_data['subscription_name'],
				'subscription_key'	 => $subscription_data['key'],
				'amount'           => $amount
			);			
			
			$HANNANStd_session = HANNAN_Session::get_instance();
			@session_start();
			$HANNANStd_session['zaringate_payment_data'] = $zaringate_payment_data;
			$_SESSION["zaringate_payment_data"] = $zaringate_payment_data;	
			
			//Action For ZarinGate or RCP Developers...
			do_action( 'RCP_Before_Sending_to_ZarinGate', $subscription_data );	
		
			if ($rcp_options['currency'] == 'ریال' || $rcp_options['currency'] == 'RIAL' || $rcp_options['currency'] == 'ریال ایران' || $rcp_options['currency'] == 'Iranian Rial (&#65020;)')
				$amount = $amount/10;
			
			//Start of ZarinGate
			$MerchantID = $rcp_options['zaringate_merchant'];
			$Amount = intval($amount);
			$Email = isset($subscription_data['user_email']) ? $subscription_data['user_email'] : '-'; 
			$CallbackURL =  add_query_arg('gateway', $query, $subscription_data['return_url']);
			$Description = sprintf(__('خرید اشتراک %s برای کاربر %s', 'rcp_zaringate'), $subscription_data['subscription_name'],$subscription_data['user_name']);
			$Mobile ='-'; 
			
			
			//Filter For ZarinGate or RCP Developers...
			$Description = apply_filters( 'RCP_ZarinGate_Description', $Description, $subscription_data );
			$Mobile = apply_filters( 'RCP_Mobile', $Mobile, $subscription_data );
			
			
			if( isset( $rcp_options['zaringate_server'] ) && ($rcp_options['zaringate_server'] == 'Iran') )
			{	
				$WebServiceUrl = 'https://ir.zarinpal.com/pg/services/WebGate/wsdl';
			}
			else 
			{
				$WebServiceUrl = 'https://de.zarinpal.com/pg/services/WebGate/wsdl';
			}	

			$client = new SoapClient( $WebServiceUrl , array('encoding' => 'UTF-8')); 
			$result = $client->PaymentRequest(
				array(
						'MerchantID' 	=> $MerchantID,
						'Amount' 	=> $Amount,
						'Description' 	=> $Description,
						'Email' 	=> $Email,
						'Mobile' 	=> $Mobile,
						'CallbackURL' 	=> $CallbackURL
					)
			);
	
			if($result->Status == 100)
			{
				Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'ZarinGate');
			} 
			else
			{	
				wp_die( sprintf(__('متاسفانه پرداخت به دلیل خطای زیر امکان پذیر نمی باشد . <br/><b> %s </b>', 'rcp_zaringate'), $this->Fault($result->Status)) );
			}
			//End of ZarinGate
				
			exit;
		}
		
		public function ZarinGate_Verify_By_HANNANStd() {
			
			if ( !class_exists('RCP_Payments') )
				return;
			
			if (!isset($_GET['gateway']))
				return;
			
			global $rcp_options, $wpdb, $rcp_payments_db_name;
			@session_start();
			$HANNANStd_session = HANNAN_Session::get_instance();
			if (isset($HANNANStd_session['zaringate_payment_data']))
				$zaringate_payment_data = $HANNANStd_session['zaringate_payment_data'];
			else 
				$zaringate_payment_data = isset($_SESSION["zaringate_payment_data"]) ? $_SESSION["zaringate_payment_data"] : '';
			
			$query = $rcp_options['zaringate_query_name'] ? $rcp_options['zaringate_query_name'] : 'ZarinGate';
						
			if 	( ($_GET['gateway'] == $query) && $zaringate_payment_data )
			{
				
				$user_id 			= $zaringate_payment_data['user_id'];
				$subscription_name 	= $zaringate_payment_data['subscription_name'];
				$subscription_key 	= $zaringate_payment_data['subscription_key'];
				$amount 			= $zaringate_payment_data['amount'];
				
				/*
				$subscription_price = intval(number_format( (float) rcp_get_subscription_price( rcp_get_subscription_id( $user_id ) ), 2)) ;
				*/
				
				$subscription_id    = rcp_get_subscription_id( $user_id );
				$user_data          = get_userdata( $user_id );
				$payment_method =  $rcp_options['zaringate_name'] ? $rcp_options['zaringate_name'] : __( 'زرین گیت', 'rcp_zaringate');
				
				if( ! $user_data || ! $subscription_id || ! rcp_get_subscription_details( $subscription_id ) )
					return;
				
				$new_payment = 1;
				if( $wpdb->get_results( $wpdb->prepare("SELECT id FROM " . $rcp_payments_db_name . " WHERE `subscription_key`='%s' AND `payment_type`='%s';", $subscription_key, $payment_method ) ) )
					$new_payment = 0;

				unset($GLOBALS['zaringate_new']);
				$GLOBALS['zaringate_new'] = $new_payment;
				global $new;
				$new = $new_payment;
				
				if ($new_payment == 1) {
				
					//Start of ZarinGate
					$MerchantID = $rcp_options['zaringate_merchant'];
					$Amount = intval($amount);
					
					if ($rcp_options['currency'] == 'ریال' || $rcp_options['currency'] == 'RIAL' || $rcp_options['currency'] == 'ریال ایران' || $rcp_options['currency'] == 'Iranian Rial (&#65020;)')
						$Amount = $Amount/10;
					
					$Authority = $_GET['Authority'];
					
					if( isset( $rcp_options['zaringate_server'] ) && ($rcp_options['zaringate_server'] == 'Iran') )
					{
						$WebServiceUrl = 'https://ir.zarinpal.com/pg/services/WebGate/wsdl';
					}
					else 
					{
						$WebServiceUrl = 'https://de.zarinpal.com/pg/services/WebGate/wsdl';
					}	
					
					if($_GET['Status'] == 'OK'){
						
						$client = new SoapClient( $WebServiceUrl , array('encoding' => 'UTF-8')); 
						$result = $client->PaymentVerification(
							array(
								'MerchantID'	 => $MerchantID,
								'Authority' 	 => $Authority,
								'Amount'	 => $Amount
							)
						);
						
						if($result->Status == 100){
							$payment_status = 'completed';
							$fault = 0;
							$transaction_id = $result->RefID;
						}
						else {
							$payment_status = 'failed';
							$fault = $result->Status;
							$transaction_id = 0;
						}
					} 
					else {
						$payment_status = 'cancelled';
						$fault = 0;
						$transaction_id = 0;
					}
					//End of ZarinGate
				
				
				
					unset($GLOBALS['zaringate_payment_status']);
					unset($GLOBALS['zaringate_transaction_id']);
					unset($GLOBALS['zaringate_fault']);
					unset($GLOBALS['zaringate_subscription_key']);
					$GLOBALS['zaringate_payment_status'] = $payment_status;
					$GLOBALS['zaringate_transaction_id'] = $transaction_id;
					$GLOBALS['zaringate_subscription_key'] = $subscription_key;
					$GLOBALS['zaringate_fault'] = $fault;
					global $zaringate_transaction;
					$zaringate_transaction = array();
					$zaringate_transaction['zaringate_payment_status'] = $payment_status;
					$zaringate_transaction['zaringate_transaction_id'] = $transaction_id;
					$zaringate_transaction['zaringate_subscription_key'] = $subscription_key;
					$zaringate_transaction['zaringate_fault'] = $fault;
				
		
					if ($payment_status == 'completed') 
					{
				
						$payment_data = array(
							'date'             => date('Y-m-d g:i:s'),
							'subscription'     => $subscription_name,
							'payment_type'     => $payment_method,
							'subscription_key' => $subscription_key,
							'amount'           => $amount,
							'user_id'          => $user_id,
							'transaction_id'   => $transaction_id
						);
					
						//Action For ZarinGate or RCP Developers...
						do_action( 'RCP_ZarinGate_Insert_Payment', $payment_data, $user_id );
					
						$rcp_payments = new RCP_Payments();
						$rcp_payments->insert( $payment_data );
					
					
						rcp_set_status( $user_id, 'active' );
						rcp_email_subscription_status( $user_id, 'active' );
				
						if( ! isset( $rcp_options['disable_new_user_notices'] ) ) {
							wp_new_user_notification( $user_id );
						}
					
					
						update_user_meta( $user_id, 'rcp_signup_method', 'live' );
						//rcp_recurring is just for paygate or ipn gateway
						update_user_meta( $user_id, 'rcp_recurring', 'no' ); 
					
						$subscription = rcp_get_subscription_details( rcp_get_subscription_id( $user_id ) );
						$member_new_expiration = date( 'Y-m-d H:i:s', strtotime( '+' . $subscription->duration . ' ' . $subscription->duration_unit . ' 23:59:59' ) );
						rcp_set_expiration_date( $user_id, $member_new_expiration );	
						delete_user_meta( $user_id, '_rcp_expired_email_sent' );
									
						$log_data = array(
							'post_title'    => __( 'تایید پرداخت', 'rcp_zaringate' ),
							'post_content'  =>  __( 'پرداخت با موفقیت انجام شد . کد تراکنش : ', 'rcp_zaringate' ).$transaction_id.__( ' .  روش پرداخت : ', 'rcp_zaringate' ).$payment_method,
							'post_parent'   => 0,
							'log_type'      => 'gateway_error'
						);

						$log_meta = array(
							'user_subscription' => $subscription_name,
							'user_id'           => $user_id
						);
						
						$log_entry = WP_Logging::insert_log( $log_data, $log_meta );
				

						//Action For ZarinGate or RCP Developers...
						do_action( 'RCP_ZarinGate_Completed', $user_id );				
					}	
					
					
					if ($payment_status == 'cancelled')
					{
					
						$log_data = array(
							'post_title'    => __( 'انصراف از پرداخت', 'rcp_zaringate' ),
							'post_content'  =>  __( 'تراکنش به دلیل انصراف کاربر از پرداخت ، ناتمام باقی ماند .', 'rcp_zaringate' ).__( ' روش پرداخت : ', 'rcp_zaringate' ).$payment_method,
							'post_parent'   => 0,
							'log_type'      => 'gateway_error'
						);

						$log_meta = array(
							'user_subscription' => $subscription_name,
							'user_id'           => $user_id
						);
						
						$log_entry = WP_Logging::insert_log( $log_data, $log_meta );
					
						//Action For ZarinGate or RCP Developers...
						do_action( 'RCP_ZarinGate_Cancelled', $user_id );	

					}	
					
					if ($payment_status == 'failed') 
					{
									
						$log_data = array(
							'post_title'    => __( 'خطا در پرداخت', 'rcp_zaringate' ),
							'post_content'  =>  __( 'تراکنش به دلیل خطای رو به رو ناموفق باقی باند :', 'rcp_zaringate' ).$this->Fault($fault).__( ' روش پرداخت : ', 'rcp_zaringate' ).$payment_method,
							'post_parent'   => 0,
							'log_type'      => 'gateway_error'
						);

						$log_meta = array(
							'user_subscription' => $subscription_name,
							'user_id'           => $user_id
						);
						
						$log_entry = WP_Logging::insert_log( $log_data, $log_meta );
					
						//Action For ZarinGate or RCP Developers...
						do_action( 'RCP_ZarinGate_Failed', $user_id );	
					
					}
			
				}
				add_filter( 'the_content', array($this,  'ZarinGate_Content_After_Return_By_HANNANStd') );
				//session_destroy();	
			}
		}
		 
		
		public function ZarinGate_Content_After_Return_By_HANNANStd( $content ) { 
			
			global $zaringate_transaction, $new;
			
			$HANNANStd_session = HANNAN_Session::get_instance();
			@session_start();
			
			$new_payment = isset($GLOBALS['zaringate_new']) ? $GLOBALS['zaringate_new'] : $new;
			
			$payment_status = isset($GLOBALS['zaringate_payment_status']) ? $GLOBALS['zaringate_payment_status'] : $zaringate_transaction['zaringate_payment_status'];
			$transaction_id = isset($GLOBALS['zaringate_transaction_id']) ? $GLOBALS['zaringate_transaction_id'] : $zaringate_transaction['zaringate_transaction_id'];
			$fault = isset($GLOBALS['zaringate_fault']) ? $this->Fault($GLOBALS['zaringate_fault']) : $this->Fault($zaringate_transaction['zaringate_fault']);
			
			if ($new_payment == 1) 
			{
			
				$zaringate_data = array(
					'payment_status'             => $payment_status,
					'transaction_id'     => $transaction_id,
					'fault'     => $fault
				);
				
				$HANNANStd_session['zaringate_data'] = $zaringate_data;
				$_SESSION["zaringate_data"] = $zaringate_data;	
			
			}
			else
			{
				if (isset($HANNANStd_session['zaringate_data']))
					$zaringate_payment_data = $HANNANStd_session['zaringate_data'];
				else 
					$zaringate_payment_data = isset($_SESSION["zaringate_data"]) ? $_SESSION["zaringate_data"] : '';
			
				$payment_status = isset($zaringate_payment_data['payment_status']) ? $zaringate_payment_data['payment_status'] : '';
				$transaction_id = isset($zaringate_payment_data['transaction_id']) ? $zaringate_payment_data['transaction_id'] : '';
				$fault = isset($zaringate_payment_data['fault']) ? $this->Fault($zaringate_payment_data['fault']) : '';
			}
			
			$message = '';
			
			if ($payment_status == 'completed') {
				$message = '<br/>'.__( 'پرداخت با موفقیت انجام شد . کد تراکنش : ', 'rcp_zaringate' ).$transaction_id.'<br/>';
			}
			
			if ($payment_status == 'cancelled') {
				$message = '<br/>'.__( 'تراکنش به دلیل انصراف شما نا تمام باقی ماند .', 'rcp_zaringate' );
			}
			
			if ($payment_status == 'failed') {
				$message = '<br/>'.__( 'تراکنش به دلیل خطای زیر ناموفق باقی باند :', 'rcp_zaringate' ).'<br/>'.$fault.'<br/>';
			}
			
			return $content.$message;
		}
		
		private static function Fault($error) {
			$response	= '';
			switch($error){
			
                case '-1' :
					$response	=  __( 'اطلاعات ارسال شده ناقص است .', 'rcp_zaringate' );
				break;

				case '-2' :
					$response	=  __( 'آی پی یا مرچنت زرین گیت اشتباه است .', 'rcp_zaringate' );
				break;

				case '-3' :
					$response	=  __( 'با توجه به محدودیت های شاپرک امکان پرداخت با رقم درخواست شده میسر نمیباشد .', 'rcp_zaringate' );
				break;
                                                
				case '-4' :
					$response	=  __( 'سطح تایید پذیرنده پایین تر از سطح نقره ای میباشد .', 'rcp_zaringate' );
				break;
										
				case '-11' :
					$response	=  __( 'درخواست مورد نظر یافت نشد .', 'rcp_zaringate' );
				break;
												
				case '-21' :
					$response	=  __( 'هیچ نوع عملیات مالی برای این تراکنش یافت نشد .', 'rcp_zaringate' );
				break;
												
				case '-22' :
					$response	=  __( 'تراکنش نا موفق میباشد .', 'rcp_zaringate' );
                break;
												
				case '-33' :
					$response	=  __( 'رقم تراکنش با رقم وارد شده مطابقت ندارد .', 'rcp_zaringate' );
				break;
												
				case '-40' :
					$response	=  __( 'اجازه دسترسی به متد مورد نظر وجود ندارد .', 'rcp_zaringate' );
				break;
												
				case '-54' :
					$response	=  __( 'درخواست مورد نظر آرشیو شده است .', 'rcp_zaringate' );
				break;
												
				case '100' :
					$response	=  __( 'اتصال با زرین گیت به خوبی برقرار شد و همه چیز صحیح است .', 'rcp_zaringate' );
				break;		
												
				case '101' :
					$response	=  __( 'تراکنش با موفقیت به پایان رسیده بود و تاییدیه آن نیز انجام شده بود .', 'rcp_zaringate' );
				break;		
			
			}
			
			return $response;
		}
		
	}
}
new RCP_ZarinGate();
?>