<?php

namespace Bitfumes\Multiauth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Bitfumes\Multiauth\Model\Admin;

//previous data
// use App\Http\Controllers\Controller;
use App\Models\UserRegistration;
use Datatables;
use DB;
use Response;
use Session;
use Mail;
use App\Http\Requests;
use App\Models\Homepage;
use App\Models\LikeUnlike;
use App\Models\PostComments;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AdminLogin;

use Alfasoft\Reporting\Library\ReportingLibrary;
use Alfasoft\Reporting\Library\Utils;
use App\Models\Chat;
use App\Models\Login;
use App\Models\tb_email;
use App\Models\PaymentInfo; 
use App\Models\UnAssign; 
use App\Models\Test; 
use App\Models\CheckoutContactInformation;	
use App\Models\CheckoutBillingInformation;	
use App\Models\CheckoutPaymentInformation;	
use App\Models\states;
use App\Models\countries;
use Datetime;
use Auth;
use Carbon\Carbon;

// include(public_path('recurly/recurly/lib/recurly.php'));
// include(public_path('recurly/auth.php'));
require_once(base_path('vendor/recurly/recurly-client/lib/recurly.php'));
require_once(base_path('vendor/recurly/recurly-client/lib/auth.php'));
  use Recurly_Coupon;
  use Recurly_Subscription;
  use Recurly_ValidationError; 
  use Recurly_Client;
  use Recurly_Account;
  use Recurly_NotFoundError;
  use Recurly_CouponRedemption;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role:super', ['only'=>'show']);
    }

    private function parseXMLField($field) {
        if (!is_array($field)) {
            return $field;
        } else if (isset($field[0])) {
            return $field[0];                        
        }
        return "";
    }    
    
    private function parseOntraportDataToDatabase($array_1) {
        $ontra_created_date = date("Y-m-d H:i:s", $this->parseXMLField($array_1['contact']['@attributes']['date']));
        $ontra_dlm = date("Y-m-d H:i:s", $this->parseXMLField($array_1['contact']['@attributes']['dlm']));
        $ontra_score = $this->parseXMLField($array_1['contact']['@attributes']['score']);
        $ontra_purl = $this->parseXMLField($array_1['contact']['@attributes']['purl']);
        $ontra_bulk_mail = $this->parseXMLField($array_1['contact']['@attributes']['bulk_mail']);
        $ontra_first_name = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][1]);
        $ontra_last_name = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][2]);
        $ontra_email = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][3]);
        $ontra_title = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][4]);
        $ontra_account_type = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][6]);
        $ontra_account_status = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][7]);
        $ontra_def = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][7]);
        $ontra_address = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][10]);
        $ontra_address2 = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][11]);
        $ontra_city = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][12]);
        $ontra_state = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][13]);
        $ontra_zip = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][14]);
        $ontra_country = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][15]);
        $ontra_fax = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][17]);
        $ontra_sms_number = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][18]);
        $ontra_birthday = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][19]);
        $ontra_company = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][20]);
        $ontra_offc_phone = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][21]);
        $ontra_website = $this->parseXMLField($array_1['contact']['Group_Tag'][0]['field'][22]);
        $ontra_spent = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][0]);
        $ontra_date_modified = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][3]);
        $ontra_ip_address = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][4]);
        $ontra_last_activity = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][6]);
        $ontra_last_note = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][7]);
        $ontra_is_agree = $this->parseXMLField($array_1['contact']['Group_Tag'][3]['field'][8]);
        $ontra_paypal_address = $this->parseXMLField($array_1['contact']['Group_Tag'][4]['field'][1]);
        $ontra_no_of_sales = $this->parseXMLField($array_1['contact']['Group_Tag'][4]['field'][1]);
        $ontra_last_total_invoice = $this->parseXMLField($array_1['contact']['Group_Tag'][4]['field'][2]);
        $ontra_last_invoice_no = $this->parseXMLField($array_1['contact']['Group_Tag'][5]['field'][0]);
        $ontra_last_charge = $this->parseXMLField($array_1['contact']['Group_Tag'][5]['field'][1]);
        $ontra_last_total_invoice2 = $this->parseXMLField($array_1['contact']['Group_Tag'][5]['field'][2]);
        $ontra_total_amount_unpaid = $this->parseXMLField($array_1['contact']['Group_Tag'][5]['field'][3]);
        $ontra_card_type = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][0]);
        $ontra_card_number = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][1]);
        $ontra_card_expiry_month = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][2]);
        $ontra_last_cc_status = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][3]);
        $ontra_card_expiry_year = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][4]);
        $ontra_card_expiry_date = $this->parseXMLField($array_1['contact']['Group_Tag'][6]['field'][5]);
        $ontra_date_added = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][0]);
        $ontra_trading_experience = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][1]);
        $ontra_trading_strategy = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][2]);
        $ontra_traded_live_before = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][3]);
        $ontra_still_trading_live = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][4]);
        $ontra_accounts_traded_live = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][5]);
        $ontra_avg_trades_per_day = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][6]);
        $ontra_time_in_trade = $this->parseXMLField($array_1['contact']['Group_Tag'][7]['field'][7]);
        /*
        if(!is_array($array_1['contact']['Group_Tag'][7]['field'][8])) {
            $ontra_5_day_statement = $array_1['contact']['Group_Tag'][7]['field'][8];
        }
    
        if(!is_array($array_1['contact']['Group_Tag'][7]['field'][9])) {
            $ontra_user_ip = $array_1['contact']['Group_Tag'][7]['field'][9];
        }
    
        if(!is_array($array_1['contact']['Group_Tag'][7]['field'][10])) {
            $ontra_about_trader = $array_1['contact']['Group_Tag'][7]['field'][10];
        }
        */
        $ontra_live_user_id = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][0]);
        $ontra_live_account_id = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][1]);
        $ontra_password_live = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][2]);
        $ontra_live_activation_date = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][3]);
        $ontra_live_expiration_date = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][4]);
        $ontra_live_account_balance = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][5]);
        /*$ontra_live_account_balance=$array_1['contact']['Group_Tag'][8]['field'][5];*/
        $ontra_live_termination = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][6]);
        $ontra_status = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][7]);
        $ontra_live_trading_status = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][8]);
        $ontra_termination_reason = $this->parseXMLField($array_1['contact']['Group_Tag'][8]['field'][9]);
        $ontra_demo_user_id = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][0]);
        $ontra_demo_account_id = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][1]);
        $ontra_demo_password = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][2]);
        $ac = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][4]);
        $ontra_activation_date = DateTime::createFromFormat('m-d-Y', $ac)->format('Y-m-d');
        $ex = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][5]);
        $ontra_expiration_date = DateTime::createFromFormat('m-d-Y', $ex)->format('Y-m-d');
        $ontra_termination_date = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][6]);
        $ontra_questionnaire = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][7]);
        $ontra_contest_start = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][8]);
        $ontra_contest_end = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][9]);
        $ontra_contest_confirmed = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][10]);
        $ontra_trading_status = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][11]);
        $ontra_ending_account_balance = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][12]);
        $ontra_demo_results = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][13]);
        $ontra_demo_fail_reasons = $this->parseXMLField($array_1['contact']['Group_Tag'][9]['field'][14]);
        $ontra_products_traded = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][0]);
        $ontra_trading_platform = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][1]);
        $ontra_professional_background = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][2]);
        $ontra_trading_style = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][3]);
        $ontra_why_trading = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][4]);
        $ontra_daily_preparation = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][5]);
        $ontra_short_term_goals = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][6]);
        $ontra_long_term_goals = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][7]);
        $ontra_strengths = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][8]);
        $ontra_weaknesses = $this->parseXMLField($array_1['contact']['Group_Tag'][10]['field'][9]);
        $ontra_last_inbound_sms = $this->parseXMLField($array_1['contact']['Group_Tag'][11]['field']);
        $ontra_iB_id = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][0]);
        $ontra_account_value = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][1]);
        $ontra_min_account_balance = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][2]);
        $ontra_fcm_id = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][3]);
        $ontra_days = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][6]);
        $ontra_rms_buy_limit = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][7]);
        $ontra_rms_sell_limit = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][8]);
        $ontra_rms_loss_limit = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][9]);
        $ontra_rms_max_order = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][10]);
        $ontra_send_to_rithmic = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][11]);
        $ontra_commision_fill_rate = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][12]);
        $ontra_daily_loss_limit = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][13]);
        $ontra_max_down = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][14]);
        $ontra_profit_target = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][15]);
        $ontra_target_days = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][16]);
        $ontra_update_date_time = $this->parseXMLField($array_1['contact']['Group_Tag'][12]['field'][18]);
        
        
        $ontra_contact_id = $this->parseXMLField($array_1['contact']['@attributes']['id']);
        
        
        date_default_timezone_set("America/Chicago");
        $Uupdated_at = date('Y-m-d H:i:s');
        $curr_time = date("h:i:s");
        
        $dataToSaveInDatabase = ['updated_at' => $Uupdated_at,
                'ontra_contact_id' => $ontra_contact_id,
                'ontra_created_date' => $ontra_created_date,
                'ontra_dlm' => $ontra_dlm,
                'ontra_score' => $ontra_score,
                'ontra_purl' => $ontra_purl,
                'ontra_bulk_mail' => $ontra_bulk_mail,
                'ontra_first_name' => $ontra_first_name,
                'ontra_last_name' => $ontra_last_name,
                'ontra_email' => $ontra_email,
                'ontra_account_type' => $ontra_account_type,
                'ontra_company' => $ontra_company,
                'ontra_title' => $ontra_title,
                'ontra_account_status' => $ontra_account_status,
                'ontra_address' => $ontra_address,
                'ontra_address2' => $ontra_address2,
                'ontra_city' => $ontra_city,
                'ontra_state' => $ontra_state,
                'ontra_zip' => $ontra_zip,
                'ontra_country' => $ontra_country,
                'ontra_fax' => $ontra_fax,
                'ontra_sms_number' => $ontra_sms_number,
                'ontra_offc_phone' => $ontra_offc_phone,
                'ontra_birthday' => $ontra_birthday,
                'ontra_website' => $ontra_website,
                'ontra_spent' => $ontra_spent,
                'ontra_date_modified' => $ontra_date_modified,
                'ontra_ip_address' => $ontra_ip_address,
                'ontra_last_activity' => $ontra_last_activity,
                'ontra_last_note' => $ontra_last_note,
                'ontra_is_agree' => $ontra_is_agree,
                'ontra_paypal_address' => $ontra_paypal_address,
                'ontra_no_of_sales' => $ontra_no_of_sales,
                'ontra_last_total_invoice' => $ontra_last_total_invoice,
                'ontra_last_invoice_no' => $ontra_last_invoice_no,
                'ontra_last_total_invoice2' => $ontra_last_total_invoice2,
                'ontra_total_amount_unpaid' => $ontra_total_amount_unpaid,
                'ontra_card_type' => $ontra_card_type,
                'ontra_card_number' => $ontra_card_number,
                'ontra_card_expiry_month' => $ontra_card_expiry_month,
                'ontra_last_cc_status' => $ontra_last_cc_status,
                'ontra_card_expiry_year' => $ontra_card_expiry_year,
                'ontra_card_expiry_date' => $ontra_card_expiry_date,
                'ontra_date_added' => $ontra_date_added,
                'ontra_trading_experience' => $ontra_trading_experience,
                'ontra_trading_strategy' => $ontra_trading_strategy,
                'ontra_traded_live_before' => $ontra_traded_live_before,
                'ontra_still_trading_live' => $ontra_still_trading_live,
                'ontra_accounts_traded_live' => $ontra_accounts_traded_live,
                'ontra_avg_trades_per_day' => $ontra_avg_trades_per_day,
                'ontra_time_in_trade' => $ontra_time_in_trade,
                //'ontra_5_day_statement' => $ontra_5_day_statement,
                //'ontra_user_ip' => $ontra_user_ip,
                //'ontra_about_trader' => $ontra_about_trader,
                'ontra_live_user_id' => $ontra_live_user_id,
                'ontra_live_account_id' => $ontra_live_account_id,
                'ontra_password_live' => $ontra_password_live,
                'ontra_live_activation_date' => $ontra_live_activation_date,
                'ontra_live_expiration_date' => $ontra_live_expiration_date,
                'ontra_live_account_balance' => $ontra_live_account_balance,
                'ontra_live_termination' => $ontra_live_termination,
                'ontra_status' => $ontra_status,
                'ontra_live_trading_status' => $ontra_live_trading_status,
                'ontra_termination_reason' => $ontra_termination_reason,
                'ontra_demo_user_id' => $ontra_demo_user_id,
                'ontra_demo_account_id' => $ontra_demo_account_id,
                'ontra_demo_password' => $ontra_demo_password,
                'ontra_acc_def' => $ontra_def,
                'ontra_activation_date' => $ontra_activation_date,
                'ontra_expiration_date' => $ontra_expiration_date,
                'ontra_time' => $curr_time,
                'ontra_termination_date' => $ontra_termination_date,
                'ontra_questionnaire' => $ontra_questionnaire,
                'ontra_contest_start' => $ontra_contest_start,
                'ontra_contest_end' => $ontra_contest_end,
                'ontra_contest_confirmed' => $ontra_contest_confirmed,
                'ontra_trading_status' => $ontra_trading_status,
                'ontra_ending_account_balance' => $ontra_ending_account_balance,
                'ontra_demo_results' => $ontra_demo_results,
                'ontra_demo_fail_reasons' => $ontra_demo_fail_reasons,
                'ontra_products_traded' => $ontra_products_traded,
                'ontra_trading_platform' => $ontra_trading_platform,
                'ontra_professional_background' => $ontra_professional_background,
                'ontra_trading_style' => $ontra_trading_style,
                'ontra_why_trading' => $ontra_why_trading,
                'ontra_daily_preparation' => $ontra_daily_preparation,
                'ontra_short_term_goals' => $ontra_short_term_goals,
                'ontra_long_term_goals' => $ontra_long_term_goals,
                'ontra_strengths' => $ontra_strengths,
                'ontra_weaknesses' => $ontra_weaknesses,
                'ontra_last_inbound_sms' => $ontra_last_inbound_sms,
                'ontra_iB_id' => $ontra_iB_id,
                'ontra_account_value' => $ontra_account_value,
                'ontra_min_account_balance' => $ontra_min_account_balance,
                'ontra_fcm_id' => $ontra_fcm_id,
                'ontra_rms_buy_limit' => $ontra_rms_buy_limit,
                'ontra_rms_sell_limit' => $ontra_rms_sell_limit,
                'ontra_rms_loss_limit' => $ontra_rms_loss_limit,
                'ontra_rms_max_order' => $ontra_rms_max_order,
                'ontra_commision_fill_rate' => $ontra_commision_fill_rate,
                'ontra_days' => $ontra_days,
                'ontra_send_to_rithmic' => $ontra_send_to_rithmic,
                'ontra_update_date_time' => $ontra_update_date_time,
                'ontra_daily_loss_limit' => $ontra_daily_loss_limit,
                'ontra_max_down' => $ontra_max_down,
                'ontra_profit_target' => $ontra_profit_target,
                'ontra_target_days' => $ontra_target_days,
                'account_type' => 'trial',
                'account_type_from_ontra' => 'OUP TRIAL14DAY'];
                    
                    
        return $dataToSaveInDatabase;
	}
	
    public function index()
    {
        return view('multiauth::admin.home');
    }

    public function show()
    {
        $admins = Admin::where('id', '!=', auth()->id())->get();

        return view('multiauth::admin.show', compact('admins'));
    }

    public function showChangePasswordForm()
    {
        return view('multiauth::admin.passwords.change');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'oldPassword'   => 'required',
            'password'      => 'required|confirmed',
        ]);
        auth()->user()->update(['password' => bcrypt($data['password'])]);

        return redirect(route('admin.home'))->with('message', 'Your password is changed successfully');
    }







    
    public function testing(){

        return view('multiauth::admin.testing');

    }

	 public function details($id){
			
		$users = tb_email::where('id', $id)->first();
		
		return view('multiauth::admin.edit-email',['users'=>$users]);
		
	}

	public function detailsss($id){
		
		$users = tb_email::where('id', $id)->first();
	 	return view('multiauth::admin.view-email',['users'=>$users]);
	
	}

	public function update(Request $request,$id){
		
	 	// $email = $request->input('title');
	 	 if($id == 1){
	 	 	$head = $request->input('head');
			 $user_name = $request->input('editor1');
			 $user_name2 = $request->input('editor2');
			 $name = $request->input('email');
			 
		 
		 
			 DB::table('tb_email')
	            ->where('id', $id)
	            ->update(['head' => $head,'content'=> $user_name,'s_content'=> $user_name2, 'email'=>$name ]);
	 	 }else if($id == 4){
	 	 	$head = $request->input('head');
			 $user_name = $request->input('editor1');
			 $user_name2 = $request->input('editor2');
			 $name = $request->input('email');
			 
		 
		 
			 DB::table('tb_email')
	            ->where('id', $id)
	            ->update(['head' => $head,'content'=> $user_name,'s_content'=> $user_name2, 'email'=>$name ]);
	 	 }else if($id == 6){
			 $user_name = $request->input('editor1');
			 $user_name2 = $request->input('editor2');
			 $name = $request->input('email');
			 
		 
		 
			 DB::table('tb_email')
	            ->where('id', $id)
	            ->update(['content'=> $user_name,'s_content'=> $user_name2, 'email'=>$name ]);
	 	 }else if($id == 7){
			 $user_name = $request->input('editor1');
			 $user_name2 = $request->input('editor2');
			 $user_name3 = $request->input('editor3');
			 $name = $request->input('email');
			 
		 
		 
			 DB::table('tb_email')
	            ->where('id', $id)
	            ->update(['head' => $user_name,'content'=> $user_name2,'s_content'=> $user_name3, 'email'=>$name ]);
	 	 }else{
	 	 	$head = $request->input('head');
			 $user_name = $request->input('editor1');
			 $name = $request->input('email');
			 
		 
		 
			 DB::table('tb_email')
	            ->where('id', $id)
	            ->update(['head' => $head,'content'=> $user_name, 'email'=>$name ]);
	 	 }
	 	 
			
		
			 return redirect('admin/edit-email/'.$id )
			->with('message','Content Updated Successfully')
			->with('status','success');	
			
	 }

	public function ViewPostF(Request $request){

		$check = DB::table('WallPost')->where('wall_id','=',$request->wall_id)->where('delete_status', '=', '0')->count();
		if($check != 0){
				$UserDetails=Homepage::leftjoin('users' , 'WallPost.user_id', '=', 'users.user_id')
						->leftjoin('Category', 'WallPost.cat_id', '=', 'Category.id')
						->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
						->select('WallPost.*', 'users.name', 'users.first_name', 'users.last_name', 'users.ImageUrl','users.NewImageUrl','Category.cat_name','SubCategory.sub_cat_name')
						->where('WallPost.delete_status', '=', '0')
						->where('WallPost.wall_id', '=', $request->wall_id)
						->get();

        	return view('ViewPost',['WallDetails'=>$UserDetails]);
		}else{
			
        	return redirect('admin/post-list');
		}
	

	}


	public function ViewChatF($user_id,$cat_id){

		$ChatDetails = Chat::leftjoin('users', 'Chat.user_id', '=' , 'users.user_id')
						->select('Chat.*', 'users.ImageUrl','users.NewImageUrl')
						->where('Chat.message','!=','')
						->where('delete_status','0')
						->where('Chat.user_id',$user_id)
						->where('Chat.category',$cat_id)
						->get();

		return view('multiauth::admin.ViewChat',['ChatDetails'=>$ChatDetails,'cat_id'=>$cat_id,'sub_cat_id'=>'']);


	}


	public function ViewChatFF($user_id,$cat_id,$sub_cat_id){

		$ChatDetails = Chat::leftjoin('users', 'Chat.user_id', '=' , 'users.user_id')
						->select('Chat.*', 'users.ImageUrl','users.NewImageUrl')
						->where('Chat.message','!=','')
						->where('delete_status','0')
						->where('Chat.user_id',$user_id)
						->where('Chat.category',$cat_id)
						->where('Chat.sub_category',$sub_cat_id)
						->get();

		return view('multiauth::admin.ViewChat',['ChatDetails'=>$ChatDetails,'cat_id'=>$cat_id,'sub_cat_id'=>$sub_cat_id]);

	}


	
    public function UserList(){
       $Evaluation = DB::table('tblevaluation')->get();
	   $active_accounts = DB::table('AS_R_User')->where('Status',1)->count();
	   $inactive_accounts = DB::table('AS_R_User')->where('Status',0)->count();
       return view('multiauth::admin.user-list',['inactive_accounts'=>$inactive_accounts,'active_accounts'=>$active_accounts,'evaluation_list' => json_decode(json_encode($Evaluation),true)]);


    }


    public function GetUserList(Request $request)
    {
        $offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'id';
                break;
            case 'User ID':
                $sort = 'ontra_demo_user_id';
                break;
            case 'Username':
                $sort = 'name';
                break;
            case 'Name':
                $sort = 'first_name';
                break;
            case 'Email':
                $sort = 'email';
                break;
            case 'Registered On':
                $sort = 'created_at';
                break;
            case 'Account Type':
                $sort = 'account_type';
                break;
            case 'Ontraport Account Type':
                $sort = 'account_type_from_ontra';
                break;
            case 'Account Valid Upto':
                $sort = 'ontra_expiration_date';
                break;
            default:
                $sort = 'id';
        }

        $data=array();
        $rows=array();

        $columns=['ontra_demo_user_id', 'name', 'first_name', 'last_name','email', 'created_at', 'account_type', 'account_type_from_ontra', 'ontra_expiration_date'];

        $users = DB::table('users')

            ->select('ontra_demo_user_id','name','first_name','last_name','email','created_at','account_type','account_type_from_ontra','ontra_expiration_date','status','user_id')
            ->where('del_state', '=', 0)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        $users_total = DB::table('users')
            ->where('del_state', '=', 0)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->count();

        $data['total']=$users_total;
        $data['rows']=$users;

        return response()->json($data);

    }


    public function adminUpgrade($user_id){
				

			$message='';
			$created_date = '';
			$account_type = '';
			$updated_time1 = '';		
			$updated_time = '';
			$ontra_account_status = '';
			$Validity_date = '';
			
			$name = UserRegistration::where('users.user_id',$user_id)->first();
			$results = countries::orderBy('name','asc')->get();
			
			
			$curl=curl_init('http://api.ontraport.com/1/objects?objectID=70');

			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			   "Api-Key: ".decrypt(config('app.Hkey')->ont_ky),
               "Api-Appid:".decrypt(config('app.Hkey')->ont_apky)
			)
			);

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$auth = curl_exec($curl);
			$info = curl_getinfo($curl);
			//print_r($info);
			//echo"<pre>";
			$res = json_decode($auth);
			$pay_gateway = $res->data;
			
			$offer = DB::table('offer')->where('id', '=', 1)->first();
			return view('multiauth::admin.admin-upgrade',['user' => $results,'account_type' => $name->account_type,'exp_date'=>$Validity_date,'modal_time' =>$updated_time,'ontra_account_status'=>$ontra_account_status,'name'=>$name,'pay_gateway'=>$pay_gateway,'offer'=>$offer]);				
						
			
		}

	/*public function postUpgrade1(Request $request){

		echo $request->total_amt;
	}*/

    public function postUpgrade(Request $request){
		Log::info('Admin PostUpgrade - PostUpgrade started');

			$uidd = $request->adUser;
			$get_ontra_contact_id = UserRegistration::where('user_id','=',$uidd)->first();

            $last_date = $get_ontra_contact_id->updated_at;

            $type = $get_ontra_contact_id->account_type;

            $last_updated_at = date("Y-m-d", strtotime($last_date));
            
            $now_date = date('Y-m-d');

            /*if($type == 'demo' && $now_date == $last_updated_at)
            {
                echo "cannot";
            }else{*/
            	
				$uidd = $request->adUser;
				$transactionID = 'Admin Upgrade';
				$invoice_id = 'NONE';
				$address='';          
				$post_data='';          
					
				Log::info('Admin PostUpgrade - User Id: ' . $uidd);
				
				DB::table('users')           
					->where('user_id', $uidd)          
					->update(['temp_account_type' => trim($request->plan_price)]);

				//$cntry = DB::table('countries')->where('name', $request->billing_country)->first();  
				
				$fr_address = str_replace(array(','), ' ' , $request->billing_address);
	            $fr_address2 = str_replace(array(','), ' ' , $request->billing_address_2);
	            $fr_city = str_replace(array(','), ' ' , $request->billing_city);


				Log::info('Admin PostUpgrade - Updatind address.');

				DB::table('users')
				->where('user_id', $uidd)
				->update(['address' => $fr_address,
				'city' => $fr_city,
				'state' => trim($request->billing_state),
				'zip' => trim($request->billing_zip),
				'country' => trim($request->billing_country)]);
				
				Log::info('Admin PostUpgrade - Creating new CheckoutContactInformation');

				 $CheckoutContactInformation = new CheckoutContactInformation;           
	            $CheckoutContactInformation->user_id = $uidd;          
	            $CheckoutContactInformation->first_name = trim($request->first_name);           
	            $CheckoutContactInformation->last_name = trim($request->last_name);         
	            $CheckoutContactInformation->email = trim($request->email);         
	            $CheckoutContactInformation->created_at = date('Y-m-d H:i:s');          
	            $CheckoutContactInformation->updated_at = date('Y-m-d H:i:s');          
	            $CheckoutContactInformation->account_type = 'demo';
	            $CheckoutContactInformation->account_type_status = 'pending';
	            $CheckoutContactInformation->save();            
				
				Log::info('Admin PostUpgrade - Creating new CheckoutBillingInformation');

	            $CheckoutBillingInformation = new CheckoutBillingInformation;           
	            $CheckoutBillingInformation->user_id = $uidd;          
	            $CheckoutBillingInformation->billing_address = trim($request->billing_address);         
	            $CheckoutBillingInformation->billing_address2 = trim($address);         
	            $CheckoutBillingInformation->billing_city = trim($request->billing_city);           
	            $CheckoutBillingInformation->billing_state = trim($request->billing_state);         
	            $CheckoutBillingInformation->billing_country = trim($request->billing_country);            
	            $CheckoutBillingInformation->billing_zip = trim($request->billing_zip);         
	            $CheckoutBillingInformation->created_at = date('Y-m-d H:i:s');          
	            $CheckoutBillingInformation->updated_at = date('Y-m-d H:i:s');
	            $CheckoutBillingInformation->account_type = 'demo';         
	            $CheckoutBillingInformation->account_type_status = 'pending';
	            $CheckoutBillingInformation->rflag = 0;
	            $CheckoutBillingInformation->save();            
							
				Log::info('Admin PostUpgrade - Creating new CheckoutPaymentInformation');

	            $CheckoutPaymentInformation = new CheckoutPaymentInformation;           
	            $CheckoutPaymentInformation->user_id = $uidd;          
	            $CheckoutPaymentInformation->TransactionID = $transactionID;              
	            $CheckoutPaymentInformation->item_name = trim($request->ItemName[0]);           
	            $CheckoutPaymentInformation->item_description = trim($request->ItemDesc[0]);            
	            $CheckoutPaymentInformation->total_amount = trim($request->total_amt);          
	            $CheckoutPaymentInformation->sub_total_amount = trim($request->total_amt);          
	            $CheckoutPaymentInformation->plan_price = trim($request->plan_price);           
	            $CheckoutPaymentInformation->created_at = date('Y-m-d H:i:s');          
	            $CheckoutPaymentInformation->updated_at = date('Y-m-d H:i:s');
	            $CheckoutPaymentInformation->account_type = 'demo';
	            $CheckoutPaymentInformation->account_type_status = 'pending';
	            $CheckoutPaymentInformation->payment_status = false;                     
	            $CheckoutPaymentInformation->save(); 

	            $curr_date = date('Y-m-d');
	            $daystosum = '30';
	            $ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));

	            if ( strpos( $request->total_amt, '.' ) === false ){
	               $am = $request->total_amt.'.00';
	            }else{
	                $am = $request->total_amt;
	            }

				Log::info('Admin PostUpgrade - Amount: ' . $am);

	            // $PaymentInfo = new PaymentInfo;           
	            // $PaymentInfo->user_id = $uidd;          
	            // $PaymentInfo->transaction_id = $transactionID;
	            // $PaymentInfo->invoice_id = $invoice_id;           
	            // $PaymentInfo->amount = $am;         
	            // $PaymentInfo->payment_date = $curr_date;           
	            // $PaymentInfo->expiry_date = $ex_date;
	            // $PaymentInfo->type = 'Sale';             
	            // $PaymentInfo->save();  

	            $UnAssign = new UnAssign;           
	            $UnAssign->user_id = $uidd;          
	            $UnAssign->email = trim($request->email);
	            $UnAssign->date = date('Y-m-d');           
	            $UnAssign->status = false;        
	            $UnAssign->save();   

	            $get_ontra_contact_id = UserRegistration::where('user_id','=',$uidd)->first(); 
	            $ontra_contact_id = $get_ontra_contact_id->ontra_contact_id;

	            $data='{
	                  "objectID": 0,
	                  "id": '.$ontra_contact_id.',
	                  "f1548": "0"
					}';
					
					Log::info('Admin PostUpgrade - Send object to Ontraport: ' . print_r($data, 1));

	                $curl=curl_init('http://api.ontraport.com/1/objects');

	                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	                  'Content-Type: application/json',
	                   "Api-Key: ".decrypt(config('app.Hkey')->ont_ky),
                       "Api-Appid:".decrypt(config('app.Hkey')->ont_apky)
	                )
	                );

	                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
	                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	                $auth = curl_exec($curl);
					$info = curl_getinfo($curl);    
					
					Log::info('Admin PostUpgrade - Ontraport object result: ' . print_r($auth, 1));
	             
	            $u_userid = $uidd;
				$CheckoutPaymentInformation = CheckoutPaymentInformation::where('user_id', '=', $u_userid)->orderBy('id', 'desc')->first();	

				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['payment_status'=>true]);
				
				$UserRegistration = UserRegistration::leftjoin('states','users.state','=','states.id')
				->leftjoin('countries','users.country','=','countries.id')
				->select('users.*', 'states.name as sname', 'countries.name as cname')			
				->where('users.user_id','=',$u_userid)
				->first();
				
				
				$plan_price = $UserRegistration->temp_account_type;
				
				if($plan_price == '$25,000'){
					$ontra_account_type='OUP EVAL25K';
					$acc_def = '$25,000 Evaluation';
					$d_account_value	= '25000';
					$MABalance = '23500';
					$AL_MA_Balance = '25000';
					$AL_Threshold = '1500';
					$RMS_buy = '3';
					$RMS_sell = '3';
					$RMS_loss = '2500';
					$RMS_max = '9';
					$daily_loss = '500';
					$max_drawdown = '1500';
					$profit_target = '1500';
				}elseif($plan_price == '$50,000'){
					$ontra_account_type='OUP EVAL50K';
					$acc_def = '$50,000 Evaluation';
					$d_account_value	= '50000';
					$MABalance = '47500';
					$AL_MA_Balance = '50000';
					$AL_Threshold = '2500';
					$RMS_buy = '6';
					$RMS_sell = '6';
					$RMS_loss = '1250';
					$RMS_max = '18';
					$daily_loss = '2500';
					$max_drawdown = '2500';
					$profit_target = '3000';
				}elseif($plan_price == '$100,000'){
					$ontra_account_type='OUP EVAL100K';
					$acc_def = '$100,000 Evaluation';
					$d_account_value	= '100000';
					$MABalance = '96500';
					$AL_MA_Balance = '100000';
					$AL_Threshold = '3500';
					$RMS_buy = '12';
					$RMS_sell = '12';
					$RMS_loss = '2500';
					$RMS_max = '36';
					$daily_loss = '2500';
					$max_drawdown = '3500';
					$profit_target = '6000';
				}elseif($plan_price == '$150,000'){
					$ontra_account_type='OUP EVAL150K';
					$acc_def = '$150,000 Evaluation';
					$d_account_value	= '150000';
					$MABalance = '145000';
					$AL_MA_Balance = '150000';
					$AL_Threshold = '5000';
					$RMS_buy = '15';
					$RMS_sell = '15';
					$RMS_loss = '4000';
					$RMS_max = '45';
					$daily_loss = '3500';
					$max_drawdown = '5000';
					$profit_target = '9000';
				}elseif($plan_price == '$250,000'){
					$ontra_account_type='OUP EVAL250K';
					$acc_def = '$250,000 Evaluation';
					$d_account_value	= '250000';
					$MABalance = '244500';
					$AL_MA_Balance = '250000';
					$AL_Threshold = '5500';
					$RMS_buy = '25';
					$RMS_sell = '25';
					$RMS_loss = '5000';
					$RMS_max = '75';
					$daily_loss = '4500';
					$max_drawdown = '5500';
					$profit_target = '15000';
				}
				$last_account_type = $UserRegistration->account_type;
				$last_ontra_account_type = $UserRegistration->account_type_from_ontra;
				$last_demo_account_id = $UserRegistration->ontra_demo_account_id;
				$add_ontra_account_type_sequence = '';
				$remove_ontra_account_type_sequence = '';
				
				if($last_account_type == 'trial'){
					
					if($ontra_account_type == 'OUP EVAL25K'){
						
						$add_ontra_account_type_sequence = 18;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						$remove_ontra_account_type_sequence = 11;
					}	
					
					
				}elseif($last_account_type == ''){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 24;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 25;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 26;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 27;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 28;
						
					}	
					
					
				}elseif($last_account_type == 'demo'){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 18;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						
					}

					if($ontra_account_type == $last_ontra_account_type){
						$remove_ontra_account_type_sequence = '';
					}else{
						if($last_ontra_account_type == 'OUP EVAL25K'){
							$remove_ontra_account_type_sequence = 18;
							
						}elseif($last_ontra_account_type == 'OUP EVAL50K'){
							$remove_ontra_account_type_sequence = 14;
							
						}elseif($last_ontra_account_type == 'OUP EVAL100K'){
							$remove_ontra_account_type_sequence = 19;
							
						}elseif($last_ontra_account_type == 'OUP EVAL150K'){
							$remove_ontra_account_type_sequence = 21;
							
						}elseif($last_ontra_account_type == 'OUP EVAL250K'){
							$remove_ontra_account_type_sequence = 22;
							
						}
					}
					
				}
				Log::info('Admin PostUpgrade - Last account: ' . $last_account_type);
				Log::info('Admin PostUpgrade - Add account type sequence: ' . $add_ontra_account_type_sequence);
				Log::info('Admin PostUpgrade - Add account type sequence: ' . $remove_ontra_account_type_sequence);

				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['ttmp'=>$add_ontra_account_type_sequence]);
				
				
				$ontra_first_name=$ontra_last_name=$ontra_email=$ontra_contact_no=$ontra_address=$ontra_city=$ontra_state=$ontra_country=$ontra_billing_zip=$ontra_demo_password = '';

				$ontraCNT = DB::table('countries')->where('name', '=', trim($UserRegistration['cname']))->first();
				
				$ontra_contact_id = trim($UserRegistration['ontra_contact_id']);
				$ontra_first_name = trim($UserRegistration['first_name']);
				$ontra_last_name = trim($UserRegistration['last_name']);
				$ontra_email = trim($UserRegistration['email']);
				$ontra_contact_no = trim($UserRegistration['contact_no']);
				$ontra_address = trim($UserRegistration['address']);
				$ontra_city = trim($UserRegistration['city']);
				$ontra_state = trim($UserRegistration['sname']);
				$ontra_country = trim($ontraCNT->ontra_name);
				$ontra_billing_zip = trim($UserRegistration['zip']);
				$ontra_demo_password = trim($UserRegistration['ontra_demo_password']);


				$contact = '';									
				$data1 = '<search>
					<equation>
						<field>E-mail</field>
						<op>e</op>
						<value>'.$UserRegistration->email.'</value>
					</equation>
				</search>';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.ontraport.com/cdata.php");
				curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS,"appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&reqType=Search&return_id=1&data=".$data1);
    

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				
				$auth1 = curl_exec($ch);
				$xml1 = new \SimpleXMLElement($auth1);
				$re = json_decode(json_encode((array)$xml1), TRUE);
				
				Log::info('Admin PostUpgrade - Ontraport search reslt: ' . print_r($xml1, 1));

				//$contact = $re['contact']['@attributes']['id'];
	            if (empty($re)) {
	                $contact = '';
	             }else{
	                $contact = $re['contact']['@attributes']['id'];
	             }
				
				date_default_timezone_set("America/Chicago");
				$dateValue = date('Y-m-d');
				$time=strtotime($dateValue);
				$day = date("d",$time);
				$month=date("m",$time);
				$year=date("y",$time);
				$random =  (mt_rand(1000,9999));
				date_default_timezone_set("America/Chicago");
	            $curr_date = date('Y-m-d');
	            $daystosum = '30';
	            $ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));

				$NewDemoAccountId = trim($UserRegistration['first_name']).trim($UserRegistration['last_name']).'OUP'.$month.$day.$random;
				
				$post_data = '';
				
				if($ontra_contact_id != '')
				{
					 $post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
					$post_data.="<contact id='$ontra_contact_id'>";
					
				}else{

					if($contact != ''){

						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";		
						$post_data.="<contact id='$contact'>";
						
					}else{
						 $post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
						$post_data.='<contact>';
					}
					//$post_data.="appid=2_103625_EYUcpSP3e&key=zHxjY8WRbBwXYXq&return_id=1&reqType=add&data=XML";			
					//$post_data.='<contact>';
				}
				
				// $post_data.='<Group_Tag name="Sequences and Tags">';
				// if(!empty($remove_ontra_account_type_sequence))
				// {
				// 	$post_data.='<field name="Sequences" action="remove">'.trim($remove_ontra_account_type_sequence).'</field>';
				// }		
				// $post_data.='<field name="Sequences">'.trim($add_ontra_account_type_sequence).'</field>';
				// $post_data.='</Group_Tag>';
				
				$post_data.='<Group_Tag name="Contact Information">			
				<field name="First Name">'.trim($ontra_first_name).'</field>
				<field name="Last Name">'.trim($ontra_last_name).'</field>
				<field name="Email">'.trim($ontra_email).'</field>
				<field name="Office Phone">'.trim($ontra_contact_no).'</field>
				<field name="Address">'.trim($ontra_address).'</field>
				<field name="City">'.trim($ontra_city).'</field>
				<field name="State">'.trim($ontra_state).'</field>
				<field name="Country">'.trim($ontra_country).'</field>
				<field name="Zip Code">'.trim($ontra_billing_zip).'</field>
				<field name="Account Type">'.trim($ontra_account_type).'</field>
				<field name="Account Status">Enabled</field>
				<field name="Account Definition">'.trim($acc_def).'</field>
				</Group_Tag>
				<Group_Tag name="ACCOUNT SETTINGS">			
				<field name="IB Id">OneUpTrader</field>
				<field name="Account Value">'.$d_account_value.'</field>
	            <field name="Min Account Balance">'.$MABalance.'</field>
	            <field name="FCM ID">OneUpTrader</field>
	            <field name="Auto Liquidate Max Min Account Balance">'.$AL_MA_Balance.'</field>
	            <field name="Auto Liquidate Threshold">'.$AL_Threshold.'</field>
	            <field name="Days">30</field>
	            <field name="RMS Buy Limit">'.$RMS_buy.'</field>
	            <field name="RMS Sell Limit">'.$RMS_sell.'</field>
	            <field name="RMS Loss Limit">'.$RMS_loss.'</field>
	            <field name="RMS Max Order Qty">'.$RMS_max.'</field>
	            <field name="Risk Algorithm">Limited Trailing Minimum Account Balance</field>
	            <field name="Commission Fill Rate">2.5</field>
	            <field name="Daily Loss Limit">'.$daily_loss.'</field>
	            <field name="Max Drawdown">'.$max_drawdown.'</field>
	            <field name="Profit Target">'.$profit_target.'</field>
	            <field name="Target Days">15</field>	
				</Group_Tag>';
				
				if(!empty($ontra_demo_password)){
					
					$post_data.='<Group_Tag name="Demo Account Information">';
					if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					}

					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}
					$post_data.='</Group_Tag>';
				}else{
					$post_data.='<Group_Tag name="Demo Account Information">';

					if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					}

					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}

					$ontra_passwd_characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';
					$OntraPasswd = '';
					$max = strlen($ontra_passwd_characters) - 1;
					
					for ($i = 0; $i < 7; $i++) {
						$OntraPasswd.= $ontra_passwd_characters[mt_rand(0, $max)];
					}
					$ontra_demo_password = $OntraPasswd;
					$post_data.='<field name="Password">'.$OntraPasswd.'</field>';
					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					$post_data.='</Group_Tag>';
				}
				
				$post_data.='</contact>';
				
				Log::info('Admin PostUpgrade - Seding data to Ontraport. Data: ' . print_r($post_data, 1));

				$curl = curl_init('https://api.ontraport.com/cdata.php');
				curl_setopt( $curl, CURLOPT_POST, true );			
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data);			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
				$auth = curl_exec($curl);			
				$info = curl_getinfo($curl);			
				//print_r($info);
				// echo"<br><br>";			
				//print_r($auth);			
				$res=json_decode($auth);			
			//echo"<pre>";			
				//print_r($res);						
				
				Log::info('Admin PostUpgrade - Data received from Ontraport. Data: ' . print_r($auth, 1));

				$xml = new \SimpleXMLElement($auth);			
				
				
				$array_1 = json_decode(json_encode((array)$xml), TRUE);

					$ontra_contact_id='';
					$ontra_created_date='';
					$ontra_dlm='';
					$ontra_score='';
					$ontra_purl='';
					$ontra_bulk_mail='';
					$ontra_first_name='';
					$ontra_last_name='';
					$ontra_email='';
					$ontra_title='';
					$ontra_company='';
					$oontra_account_type='';
					$ontra_account_status='';
					$ontra_def='';
					$ontra_address='';
					$ontra_address2='';
					$ontra_city='';
					$oontra_state='';
					$ontra_zip='';
					$oontra_country='';
					$ontra_fax='';
					$ontra_sms_number='';
					$ontra_offc_phone='';
					$ontra_birthday='';
					$ontra_website='';
					$ontra_spent='';
					$ontra_date_modified='';
					$ontra_ip_address='';
					$ontra_last_activity='';
					$ontra_last_note='';
					$ontra_is_agree='';
					$ontra_paypal_address='';
					$ontra_no_of_sales='';
					$ontra_last_total_invoice='';
					$ontra_last_invoice_no='';
					$ontra_last_charge='';
					$ontra_last_total_invoice2='';
					$ontra_total_amount_unpaid='';
					$ontra_card_type='';
					$ontra_card_number='';
					$ontra_card_expiry_month='';
					$ontra_last_cc_status='';
					$ontra_card_expiry_year='';
					$ontra_card_expiry_date='';
					$ontra_date_added='';
					$ontra_trading_experience='';
					$ontra_trading_strategy='';
					$ontra_traded_live_before='';
					$ontra_still_trading_live='';
					$ontra_accounts_traded_live='';
					$ontra_avg_trades_per_day='';
					$ontra_time_in_trade='';
					$ontra_5_day_statement='';
					$ontra_user_ip='';
					$ontra_about_trader='';
					$ontra_live_user_id='';
					$ontra_live_account_id='';
					$ontra_password_live='';
					$ontra_live_activation_date='';
					$ontra_live_expiration_date='';
					$ontra_live_account_balance='';
					$ontra_live_termination='';
					$ontra_status='';
					$ontra_live_trading_status='';
					$ontra_termination_reason='';
					$ontra_demo_user_id='';
					$ontra_demo_account_id='';
					$oontra_demo_password='';
					$ontra_activation_date='';
					$ontra_expiration_date='';
					$ontra_termination_date='';
					$ontra_questionnaire='';
					$ontra_contest_start='';
					$ontra_contest_end='';
					$ontra_contest_confirmed='';
					$ontra_trading_status='';
					$ontra_ending_account_balance='';
					$ontra_demo_results='';
					$ontra_demo_fail_reasons='';
					$ontra_products_traded='';
					$ontra_trading_platform='';
					$ontra_professional_background='';
					$ontra_trading_style='';
					$ontra_why_trading='';
					$ontra_daily_preparation='';
					$ontra_short_term_goals='';
					$ontra_long_term_goals='';
					$ontra_strengths='';
					$ontra_weaknesses='';
					$ontra_last_inbound_sms='';
					$ontra_iB_id='';
					$ontra_account_value='';
					$ontra_min_account_balance='';
					$ontra_fcm_id='';
					$ontra_rms_buy_limit='';
					$ontra_rms_sell_limit='';
					$ontra_rms_loss_limit='';
					$ontra_rms_max_order='';
					$ontra_commision_fill_rate='';
					$ontra_days='';
					$ontra_send_to_rithmic='';
					$ontra_update_date_time='';
					$ontra_daily_loss_limit='';
					$ontra_max_down='';
					$ontra_profit_target='';
					$ontra_target_days='';
					
					if(!is_array($array_1['contact']['@attributes']['id'])) {
						$ontra_contact_id = $array_1['contact']['@attributes']['id'];
						

						//add remove sequene code here 
                            if(!empty($remove_ontra_account_type_sequence))
                            {
                               
                                $ch = curl_init();

                                curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/sequence');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                                curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&remove_list=".$remove_ontra_account_type_sequence."&ids=".$ontra_contact_id."");

                                $headers = array();
                                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                                $headers[] = 'Accept: application/json';
                                $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                                $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                                $result = curl_exec($ch);
                                curl_close($ch);

                               
                            }		


                            //new sequence add code place here

                            $ch = curl_init();

                            curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/subscribe');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                            curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&add_list=".$add_ontra_account_type_sequence."&ids=".$ontra_contact_id."&sub_type=Sequence");

                            $headers = array();
                            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                            $headers[] = 'Accept: application/json';
                            $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                            $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                            $result = curl_exec($ch);
                            // if (curl_errno($ch)) {
                            // echo 'Error:' . curl_error($ch);
                            // }
                            curl_close($ch);
					}

					$dataToSaveInDatabase = [];
				
					Log::info('Admin PostUpgrade - Parsing XML comming from Ontraport. Data: ' . print_r($array_1, 1));
					
					try {
						if (sizeof($array_1) > 0) {
							$dataToSaveInDatabase = $this->parseOntraportDataToDatabase($array_1);
						}
					} catch (\Throwable $th) {
						//throw $th;

						Log::info('Admin PostUpgrade - Error fill user ontraport data');
						Log::info('');
						Log::info('');
						
						Log::info($th->getMessage());
						Log::info($th->getTraceAsString());
						
						// todo - redirect to homepage - done by miguel alfaiate
						die("Please contact administrator");
					}
					
					Log::info('Admin PostUpgrade - Save data comming from Ontraport. Data: ' . print_r($dataToSaveInDatabase, 1));

					date_default_timezone_set("America/Chicago");	
					$Uupdated_at = date('Y-m-d H:i:s');
					$curr_time = date("h:i:s");
					DB::table('users')
					->where('user_id', $u_userid)
					->update($dataToSaveInDatabase);

					$tranID = $CheckoutPaymentInformation['TransactionID'];
					$tranDate = $CheckoutPaymentInformation['created_at'];
					$tranAmount = $CheckoutPaymentInformation['total_amount'];
					$emailTB = DB::table('tb_email')->where('id','=', 3)->first();
					$to=$UserRegistration['email'];

					 $data = array("emailTB"=>$emailTB,"tranDate"=>$tranDate,"tranAmount"=>$tranAmount);

                
            //payment success email
                try{
                        Mail::send('auth.emails.PaymentSuccess', $data, function($message) use ($to,$emailTB) {
                            $message->to($to);
                            $message->from($emailTB->email,'OneUp Trader');
                            $message->subject('Payment Successful');
                           
                         });
                 } catch(\Exception $e){
                //echo $e;
                }	
					
					if(($last_ontra_account_type == 'OUP TRIAL14DAY') || ($last_ontra_account_type == '')){


						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type_from_ontra' => trim($ontra_account_type),'account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}else{
						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}	

					$ontra_acc = UserRegistration::where('user_id','=',$u_userid)->first();
					$onValue = $ontra_acc['temp_account_type'];
					if($onValue == '$25,000'){
						$account_value = 25000;
	                    $profit_target = 1500;	
	                    $target_days = 15;
					}elseif($onValue == '$50,000'){
						$account_value = 50000;
	                    $profit_target = 3000;	
	                    $target_days = 15;
					}elseif($onValue == '$100,000'){
						$account_value = 100000;
	                    $profit_target = 6000;	
	                    $target_days = 15;
					}elseif($onValue == '$150,000'){
						$account_value = 150000;
	                    $profit_target = 9000;	
	                    $target_days = 15;
					}elseif($onValue == '$250,000'){
						$account_value = 250000;
	                    $profit_target = 15000;	
	                    $target_days = 15;
					}
					date_default_timezone_set("America/Chicago");
					$curr_date1 = date('Y-m-d');
	                $daystosum1 = '30';
	                $ex_date1 = date('Y-m-d', strtotime($curr_date1.' + '.$daystosum1.' days'));

						DB::table('ontra_account')->insert([					
	                    'user_id' => $u_userid,		 			
						'account_id' => $NewDemoAccountId,
						'account_type' => $ontra_account_type,
						'acc_def' => $acc_def,
						'ontra_account_value' => $account_value,
	                    'ontra_profit_target' => $profit_target,
	                    'ontra_target_days' => $target_days,
	                    'ontra_rms_buy_limit' => $ontra_acc['ontra_rms_buy_limit'],
	                    'ontra_daily_loss_limit' => $ontra_acc['ontra_daily_loss_limit'],
	                    'ontra_max_down' => $ontra_acc['ontra_max_down'],
	                    'ontra_activation_date' => $curr_date1,
	                    'ontra_expiration_date' => $ex_date1,						
						'updated_at' => date('Y-m-d H:i:s')				
						]); 

					DB::table('unassign')->where('user_id', $u_userid)->update(['status'=>true]);	

					if($last_account_type == ''){

						/*$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}	

						//Start post CSV for NEW CONTACT USER/ ACCOUNT
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'],
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'demo_password' => trim($csvUser['ontra_demo_password']),
				                    'demo_termination_date' => '',
				                    'days' => trim($csvUser['ontra_days']),
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'country' => trim($con['ontra_name']),
				                    'account_status' => trim($csvUser['ontra_account_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'login_expiration' => '',
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Account_type' => trim($csvUser['ontra_account_type']),
				                    'Account_Threshold' => $Account_Threshold,
				                );
				             
				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => "http://oneuptrader.net/ontraport/MES_new/new_contact.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
				        //End post CSV for NEW CONTACT USER/ ACCOUNT

				        //Start post CSV for NEW CONTACT PARAMETER
				           date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
				            $run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
				            DB::table('csv_act')->insert([                    
				                'IB_id' => $csvUser['ontra_iB_id'],
				                'User_ID' => $csvUser['ontra_demo_user_id'],
				                'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                'first_name' => trim($csvUser['first_name']),
				                'last_name' => trim($csvUser['last_name']),
				                'email' => trim($csvUser['email']),
				                'demo_password' => trim($csvUser['ontra_demo_password']),
				                'days' => trim($csvUser['ontra_days']),
				                'trading_status' => trim($csvUser['ontra_trading_status']),
				                'address' => trim($csvUser['address']),
				                'city' => trim($csvUser['city']),
				                'state' => trim($stName),
				                'zip' => trim($csvUser['zip']),
				                'country' => trim($con['ontra_name']),
				                'account_status' => trim($csvUser['ontra_account_status']),
				                'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                'account_value' => $account_value,
				                'RMS_buy_limit' => $RMS_buy_limit,
				                'RMS_sell_limit' => $RMS_sell_limit,
				                'RMS_loss_limit' => $RMS_loss_limit,
				                'RMS_max_order_qty' => $RMS_max_order_qty,
				                'min_account_balance' => $min_account_balance,
				                'Commission_fill_rate' => $Commission_fill_rate, 
				                'curr_time' => $curr_time,
				                'run_time' => $run_time          
				            ]);

				            
				        //End post CSV for NEW CONTACT PARAMETER   

				            $user_id = $csvUser['user_id'];
				            $u_name = $csvUser['name'];
				            $email = $csvUser['email'];
				            $tos = 'support@oneuptrader.com';
				            //$tos = 'sohan.constacloud@gmail.com';
			                $subjects = "OneUp Trader New User Signup";
			                $messages='<html>
			                            <head>
			                              <title>OneUp Trader New User Signup</title>
			                            </head>
			                            <body>
			                              <table>
			                             <tr><td>User ID    :</td><td>'.$user_id.'</td></tr>
			                             <tr><td>User Name  :</td><td>'.$u_name.'</td></tr>
			                             <tr><td>Email      :</td><td>'.$email.'</td></tr>
			                             <tr><td>Created at :</td><td>'.date('Y-m-d H:i:s').'</td></tr>
			                              </table>
			                            </body>
			                            </html>';
			                $urls = 'https://api.sendgrid.com/';
			                 $users = env('MAIL_USERNAME');
 							 $passs = env('MAIL_PASSWORD');
			                $paramss = array(
			                'api_user'  => $users,
			                'api_key'   => $passs,
			                'to'        => $tos,
			                'subject'   => $subjects,
			                'html'      => $messages,
			                'from'      => 'support@oneuptrader.com',
			                'fromname'  => "OneUp Trader",

			                 );
			                $requests =  $urls.'api/mail.send.json';
			                $sessions = curl_init($requests);
							curl_setopt ($sessions, CURLOPT_POST, true);
							curl_setopt($sessions, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $passs));
			                curl_setopt ($sessions, CURLOPT_POSTFIELDS, $paramss);
			                curl_setopt($sessions, CURLOPT_HEADER, false);
			                curl_setopt($sessions, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
			                curl_setopt($sessions, CURLOPT_RETURNTRANSFER, true);
			                $responses = curl_exec($sessions);
			                curl_close($sessions);*/
					}
					else{

						$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}

						//Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 1
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'], 
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'days' => trim($csvUser['ontra_days']),
				                    'country' => trim($con['ontra_name']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Last_Account_ID' => trim($last_demo_account_id),
				                    'Account_Threshold' => $Account_Threshold,
				                );
				             
				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/ud_1.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
				        //End post CSV for UPGRADE / DOWNGRADE BALANCE Part 1

				        //Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
				            date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
				            $run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
				            //dd($csvUser);
				           	DB::table('csv_run')->insert([                    
								'IB_id' => $csvUser['ontra_iB_id'],
			                    'User_ID' => $csvUser['ontra_demo_user_id'],
			                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
			                    'trading_status' => trim($csvUser['ontra_trading_status']),
			                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
			                    'account_value' => $account_value,
			                   	'RMS_buy_limit' => $RMS_buy_limit,
			                    'RMS_sell_limit' => $RMS_sell_limit,
			                    'RMS_loss_limit' => $RMS_loss_limit,
			                    'RMS_max_order_qty' => $RMS_max_order_qty,
			                    'min_account_balance' => $min_account_balance,
			                    'Commission_fill_rate' => $Commission_fill_rate,
			                    'days' => trim($csvUser['ontra_days']),
			                    'state' => trim($stName),
								'Last_Account_ID' => trim($last_demo_account_id),
								'Account_type' => $ontra_account_type,
			                    'curr_time' => $curr_time,
			                    'run_time' => $run_time           
							]);
				        
				        //End post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
					} 
					

					//disabling all account id keep latest 3       
                        try{
								
                                $old_ontra_accounts = DB::table('ontra_account')->select('account_id')->where('user_id',$u_userid)->count();

                                    if($old_ontra_accounts > 3){

                                        $get_olds = DB::table('ontra_account')->select('account_id')->where('user_id',$u_userid)
                                        ->orderBy('id','desc')->take($old_ontra_accounts)->skip(3)->get();

                                        foreach($get_olds as $oa => $old){
                                            $username = $old->account_id;
                                            $diable_status = '0';

                                                $check_first = DB::table('AS_R_User')->where('Username', $username)->count();
                                                    if($check_first != 0){
                                                            $ut = new Utils();
                                                            $ut->SignalUser($username,$diable_status);
                                                    }

                                        }
                                        
                                        
                                    }

							}catch(\Exception $e){
								
							}
				

	    	echo 'success';
    	//}
    }

    public function request(){

    	$subDetails = DB::table('users')
            ->join('unsubscribe', 'users.user_id', '=', 'unsubscribe.user_id')
            //->where('unsubscribe.subscribe', '=', 1)
            ->select('users.*','unsubscribe.type')
            ->get();

       return view('multiauth::admin.request',['subDetails'=>$subDetails]);

    }

    public function Offer(){
    	$offer = DB::table('offer')->where('id', '=', 1)->first();

    	
    	
       return view('multiauth::admin.offer',['offer'=>$offer]);

    }
    
     public function gateway(){

     	$gate = DB::table('offer')->where('id', '=', 2)->first();
    	
    	$curl=curl_init('http://api.ontraport.com/1/objects?objectID=70');

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		  'Content-Type: application/json',
		   "Api-Key: ".decrypt(config('app.Hkey')->ont_ky),
           "Api-Appid:".decrypt(config('app.Hkey')->ont_apky)
		)
		);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$auth = curl_exec($curl);
		$info = curl_getinfo($curl);
		//print_r($info);
		//echo"<pre>";
		$res = json_decode($auth);
		$pay_gateway = $res->data;

		date_default_timezone_set("America/Chicago");
        $curr_date = date('Y-m-d');
        $today = DB::table('payment_info')->where('payment_date', '=', $curr_date)->where('type', '!=' , 'Recurring')->count();
    	
       return view('multiauth::admin.gateway',['pay_gateway'=>$pay_gateway,'gate'=>$gate,'today' => $today]);

    }

     public function gatewayTypePost(Request $request){

    	$gateway1 = $request->gateway1;
    	$gateway2 = $request->gateway2;
    	$gateOld = DB::table('offer')->where('id', '=', 2)->first();
    	if($gateOld->offer == $gateway1 && $gateOld->offer1 == $gateway2){
    		$parameters = ['message' => 'Payment gateway updated successfully', 'gate' => $gateOld];
				return redirect()->back()->with($parameters)->with('status' ,'success');
    	}else{

    		$check = DB::table('offer')
	            ->where('id', 2)
	            ->update(['admin_email' => Session::get('adminid'),'offer'=> $gateway1,'offer1'=> $gateway2]);
	            $gate = DB::table('offer')->where('id', '=', 2)->first();    	
	    	//echo $offer;
		    if($check){

				$parameters = ['message' => 'Payment gateway updated successfully', 'gate' => $gate];
				return redirect()->back()->with($parameters)->with('status' ,'success');
		    }else{
		    	$parameters = ['message' => 'Payment gateway not updated try again', 'gate' => $gate];
				return redirect()->back()->with($parameters)->with('status' ,'error');
		    }
	    }
    	
    }

    public function gateway2Post(Request $request){


    	$first_gat = $request->first_gat;
    	if($first_gat > 10 || $first_gat < 0){
    		$parameters = ['message' => 'Enter Ratio out of 10 only, (Ex. 8 : 2) '];
			return redirect()->back()->with($parameters)->with('status' ,'error');
    	}else{
    		if($first_gat < 10){
	    		$second_gat = 10 - $first_gat;
	    	}else{
	    		$second_gat = 0;
	    	}
	    	
	    	
	    	$check = DB::table('offer')
		            ->where('id', 2)
		            ->update(['gate1_per'=> $first_gat,'gate2_per'=> $second_gat]);
		    $gate = DB::table('offer')->where('id', '=', 2)->first();
	    	
	    	//echo $offer;
		    if($check){
		    	//return redirect()->back()->with('message' ,'Offer updated successfully')->with('status' ,'success');
	 
				$parameters = ['message' => 'Payment Distribution Ratio Updated'];
				return redirect()->back()->with($parameters)->with('status' ,'success');
		    }else{
		    	//return redirect()->back()->with('message' ,'Offer not updated try again')->with('status' ,'error');

		    	$parameters = ['message' => 'Error try again!'];
				return redirect()->back()->with($parameters)->with('status' ,'error');
		    }
    	}
    }

    public function offerPost(Request $request){

    	$offer = $request->offer;
    	$check = DB::table('offer')
	            ->where('id', 1)
	            ->update(['admin_email' => Session::get('adminid'),'offer'=> $offer]);
	    $offer1 = DB::table('offer')->where('id', '=', 1)->first();
    	
    	//echo $offer;
	    if($check){
	    	//return redirect()->back()->with('message' ,'Offer updated successfully')->with('status' ,'success');
 
			$parameters = ['message' => 'Offer updated successfully', 'offer' => $offer1];
			return redirect()->back()->with($parameters)->with('status' ,'success');
	    }else{
	    	//return redirect()->back()->with('message' ,'Offer not updated try again')->with('status' ,'error');

	    	$parameters = ['message' => 'Offer not updated try again', 'offer' => $offer1];
			return redirect()->back()->with($parameters)->with('status' ,'error');
	    }
    	
       

    }

    public function coupon(){
    	$coupon_type = DB::table('coupon_type')->orderBy('id', 'ASC')->get();

    	$coupon_list = DB::table('coupon_list')->where('del_status', '=', 0 )->orderBy('id', 'ASC')->get();

    	$coupon_list = DB::table('coupon_list')->leftjoin('coupon_type','coupon_type.id','=','coupon_list.type')								
				->select('coupon_list.*','coupon_type.type')->OrderBy('id','asc')->where('del_status', '=', 0 )
				->orderBy('id', 'ASC')->get();

    	
    
       return view('multiauth::admin.coupon',['coupon_type'=>$coupon_type,'coupon_list' => $coupon_list]);

    }

    public function couponList(Request $request){
    	$cp_id = $request->cp;
    	$coupon_list = DB::table('coupon_list')->where('id', '=', $cp_id )->first();
       return view('multiauth::admin.coupon-list',['cp_id'=>$cp_id,'coupon_name'=>$coupon_list->c_code]);

    }

 public function checkCoupon(Request $request){
						$code = $request->code;
						$plan_id = $request->plan_id;

          /* $count = DB::table('coupon_list')->where('c_code', '=', $code)->where('del_status', '=', 0)->where('status', '=', 0)->count();
            $coupon = DB::table('coupon_list')->where('c_code', '=', $code)->where('del_status', '=', 0)->where('status', '=', 0)->first();
            date_default_timezone_set("America/Chicago");
            $cDate = date('Y-m-d');
            if($count == 0){
                return \Response::json('invalid');
            }elseif($coupon->status == 1){
			    return \Response::json('invalid');
			}elseif($cDate > $coupon->valid_to){
                return \Response::json('expire');
            }elseif($coupon->used == 1){
                 return \Response::json('used');
            }else{
                return \Response::json($coupon->dis);
			}
		 */

			$api_key =  Recurly_Client::$apiKey;
			$subdomain =  Recurly_Client::$subdomain;
		
			$url = "https://".$subdomain.".recurly.com/v2/coupons/".$code;
			//setting the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			// Following line is compulsary to add as it is:
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"GET");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			$headers = array();
			$headers[] = "Accept: application/xml";
			$headers[] = "X-Api-Version: 2.14";
			$headers[] = "Content-Type: application/xml; charset=utf-8";
			$headers[] = "Authorization: Basic ".base64_encode($api_key);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$data = curl_exec($ch);
			$http =  curl_getinfo($ch);
			curl_close($ch);
			
			//convert the XML result into array
			$array_data = json_decode(json_encode(simplexml_load_string($data)), true);
		
			if($http['http_code'] == 200){
				$state = $array_data['state'];


				//for reset payments
			if($plan_id == ''){		
				$applies_to_non_plan_charges = $array_data['applies_to_non_plan_charges'];
			
				if($applies_to_non_plan_charges == 'false'){
					return \Response::json(array("discount_state"=>'invalid'));
				}
			
			}

		//for subscription plans

		 if($plan_id != ''){		
			$applies_to_all_plans = $array_data['applies_to_all_plans'];
		
			if($applies_to_all_plans == 'false'){
			
			$plans_array = $array_data['plan_codes']['plan_code'];
			//returns direct value for one plan instead array but for multiple plans it returns an array
			if(is_array($plans_array)){
					if(!in_array($plan_id,$plans_array)){
         	return \Response::json(array("discount_state"=>'invalid'));
        	}
			}else{
					if($plan_id != $plans_array){
         	return \Response::json(array("discount_state"=>'invalid'));
        }
			}

			}
		}
			
				if($state == 'redeemable'){
					$discount_type = $array_data['discount_type'];
					if($discount_type == 'percent'){
						$discount_value = $array_data['discount_percent'];
					}

					if($discount_type == 'dollars'){
						$discount = $array_data['discount_in_cents']['USD'];
						$discount_value = $discount / 100;
					}

					return \Response::json(array("discount_state"=>$state,"discount_type"=>$discount_type,"discount_value"=>$discount_value));

				}elseif($state == 'expired'){

					return \Response::json(array("discount_state"=>$state));

				}elseif($state == 'maxed_out'){

					return \Response::json(array("discount_state"=>$state));
				
				}elseif($state == 'inactive'){

					return \Response::json(array("discount_state"=>$state));
					
				}else{

					return \Response::json(array("discount_state"=>'invalid'));
				}

			}else{
				   return \Response::json(array("discount_state"=>'invalid'));
			}
				//  echo $array_data['discount_in_cents']['USD'];
		

            

    }
    
    public function userCoupons(Request $request){
            $user_id = $request->d1;
            

            //$list = DB::table('coupon_use')->where('user_id', '=', $user_id)->get();

            $list = DB::table('coupon_use')->leftjoin('users','users.user_id','=','coupon_use.user_id')
                    ->leftjoin('coupon_type','coupon_type.id','=','coupon_use.c_type')
                    ->select('coupon_use.*','coupon_type.type','users.email','users.ontra_demo_user_id')
                    ->where('coupon_use.user_id', '=', $user_id)
                    ->OrderBy('coupon_use.id', 'DESC')->get();
           
            return \Response::json($list);   

    }
     public function getCoupon(Request $request){
            $user_id = $request->list_id;
            

            $coupon_list = DB::table('coupon_list')->leftjoin('coupon_type','coupon_type.id','=','coupon_list.type')                
            ->select('coupon_list.*','coupon_type.type')->OrderBy('id','asc')->where('del_status', '=', 0 )
            ->where('coupon_list.id', '=', $user_id )->first();
           
            return \Response::json($coupon_list);   

        }
        
         public function couponUpdate(Request $request){
			$coupon_id = $request->list_id;
			$newDate = $request->new_date;
			$coupon_n = $request->coupon_n;
			
			$coupon = DB::table('coupon_list')->where('id', $coupon_id)->first();
	   
			date_default_timezone_set("America/Chicago");
			$validTo = date('Y-m-d', strtotime($newDate));
			
			try {
			  $account = Recurly_Coupon::get($coupon->c_code);
			  $account->name = $coupon_n;
			  $account->redeem_by_date = $validTo;
			  $account->update();
	   
			  $check = DB::table('coupon_list')->where('id', $coupon_id)->update(['name'=>$coupon_n,'valid_to' => $validTo]);
		   
			  return \Response::json('success'); 
			} catch (Recurly_NotFoundError $e) {
			  return \Response::json('error'); 
			  
			}
        }


     public function GetCouponList(Request $request)
    {
    	$id = $request->cp;
        $offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'id';
                break;
            case 'User Name':
                $sort = 'name';
                break;
             case 'User ID':
                $sort = 'ontra_demo_user_id';
                break;
             case 'First Name':
                $sort = 'first_name';
                break;
             case 'Last Name':
                $sort = 'last_name';
                break;        
            case 'Email':
                $sort = 'email';
                break;
            case 'Evaluation Type':
                $sort = 'item_name';
                break;    
            case 'Applied Date':
                $sort = 'apply_date';
                break;
            case 'Discounted Price':
                $sort = 'paid_amount';
                break;
            case 'Discount %':
                $sort = 'dis';
                break;
            default:
                $sort = 'id';
        }

        $data=array();
        $rows=array();

        $columns=['id','name','ontra_demo_user_id','first_name','last_name','email','item_name','apply_date', 'paid_amount', 'dis'];

        /*$users = DB::table('users')

            ->select('ontra_demo_user_id','name','first_name','last_name','email','created_at','account_type','account_type_from_ontra','ontra_expiration_date','status','user_id')
            ->where('del_state', '=', 0)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->orderBy($sort, $order)->skip($offset)->take($limit)->get();
			$users_total = DB::table('users')
            ->where('del_state', '=', 0)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->count();

            */

        $users = DB::table('users')->leftjoin('coupon_use','coupon_use.user_id', '=', 'users.user_id')	
        ->leftjoin('coupon_list','coupon_list.id','=','coupon_use.coupon_id')
        ->select('users.*', 'coupon_use.c_code','coupon_use.paid_amount','coupon_use.item_name','coupon_use.apply_date','coupon_list.dis')
        ->where('coupon_use.coupon_id', '=', $id)
        ->where(function ($query) use($search, $columns){
            if($search!='')
            {
                $query->where($columns[0], 'like', '%'.$search.'%');
                for($i=1;$i< count($columns);$i++)
                {
                    $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                }
            }
        })->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        $users_total = DB::table('users')->leftjoin('coupon_use','coupon_use.user_id', '=', 'users.user_id')
            ->leftjoin('coupon_list','coupon_list.id','=','coupon_use.coupon_id')
            ->where('coupon_use.coupon_id', '=', $id)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->count();

        $data['total']=$users_total;
        $data['rows']=$users;

        return response()->json($data);

    }

    public function couponPost(Request $request){
    	$cCode = $request->cCode;
    	$coupon_name = $request->coupon_name;
    	$type = $request->type;
    	$dis = $request->dis;
		$des = $request->des;
		
		if($type == 1){
			$duration = 'forever';
		}else{
			$duration = 'single_use';
		}

    	$valid = $request->valid;
	  
		date_default_timezone_set("America/Chicago");
    	$validTo = date('Y-m-d', strtotime($valid));
    	
    	if($validTo <= date('Y-m-d')){
			return redirect('admin/coupon')->with('message', 'Redeem date must be a future date')->with('status','error');	
		}
		
		date_default_timezone_set("America/Chicago");
    	$cDate = date('Y-m-d H:i:s');

		try {
			$coupon = new Recurly_Coupon();
			$coupon->coupon_code = $cCode;
			$coupon->duration = $duration;
			//$coupon->redemption_resource = 'subscription';
if($type != 1){
			$coupon->max_redemptions = 1;
			$coupon->max_redemptions_per_account = 1;
		}else{
			$coupon->max_redemptions_per_account = null;
		}			
			$coupon->coupon_type = 'single_code';
			$coupon->description = $des;
			$coupon->redeem_by_date = $validTo;
		  
			// ...or 10% off.
			$coupon->name = $coupon_name;
			$coupon->discount_type = 'percent';
			$coupon->discount_percent = $dis;
		  
			// Limit to gold and platinum plans only.
			//$coupon->applies_to_all_plans = true;
			$coupon->applies_to_non_plan_charges = true;
		  
			$coupon->create();

			$check = DB::table('coupon_list')->insert([                    
				'c_code' => $cCode,
				'name' => $coupon_name,
		        'type' => $type,
		        'valid_to' => $validTo,
		        'dis' => $dis,
		        'des' => $des,
		        'created_at' => $cDate           
			]);
			
			return redirect('admin/coupon')->with('message', 'Coupon Created Successfully')->with('status','success');

		  } catch (Recurly_ValidationError $e) {
			$error = $e->getMessage();
			return redirect('admin/coupon')->with('message', $error)->with('status','error');	
		  }

    	
    }

    public function deleteCoupon(Request $request){
		$id = $request->list_id;
		$coupon = DB::table('coupon_list')->where('id', $id)->first();

		try {
			$coupon = Recurly_Coupon::get($coupon->c_code);
			$coupon->delete();
		
			$check = DB::table('coupon_list')->where('id', $id)->update(['del_status' => '1']);
			return \Response::json('success');	

		} catch (Recurly_NotFoundError $e) {
			return \Response::json('error');
		}

    }

    public function couponStatus(Request $request){
    	$type = $request->type;
    	$id = $request->list_id;

    	if($type == "disable"){
    		$check = DB::table('coupon_list')->where('id', $id)->update(['status' => '1']);
    	}else{
    		$check = DB::table('coupon_list')->where('id', $id)->update(['status' => '0']);
    	}
    	
    	
		if($check){
			return \Response::json('success');	
		}else{
			return \Response::json('error');	
		}	

    }

   //  public function gatePost(Request $request){

   //  	$gateway = $request->gateway;
   //  	$check = DB::table('offer')
	  //           ->where('id', 2)
	  //           ->update(['admin_email' => Session::get('adminid'),'offer'=> $gateway]);
	  //   $gateway1 = DB::table('offer')->where('id', '=', 2)->first();
    	
   //  	//echo $offer;
	  //   if($check){
	  //   	//return redirect()->back()->with('message' ,'Offer updated successfully')->with('status' ,'success');
 
			// $parameters = ['message' => 'Payment gateway updated successfully', 'gate' => $gateway1];
			// return redirect()->back()->with($parameters)->with('status' ,'success');
	  //   }else{
	  //   	//return redirect()->back()->with('message' ,'Offer not updated try again')->with('status' ,'error');

	  //   	$parameters = ['message' => 'Payment gateway not updated try again', 'gate' => $gateway1];
			// return redirect()->back()->with($parameters)->with('status' ,'error');
	  //   }
    	
    	
   //  }

    public function requestPost(Request $request){
    	$user_id = $request->approve1;
    	$UserDetails = UserRegistration::where('user_id','=',$user_id)->first();

    	$account_type = $UserDetails->temp_account_type;
    	$contact_id = $UserDetails->ontra_contact_id;
    	if($account_type == "$25,000"){
    		$pay_status = "25";
    	}else if($account_type == "$50,000"){
    		$pay_status = "50";
    	}else if($account_type == "$100,000"){
    		$pay_status = "100";
    	}else if($account_type == "$150,000"){
    		$pay_status = "150";
    	}else{
    		$pay_status = "250";
    	}

    	$data='{
			  "objectID": 0,
			  "id": '.$contact_id.',
			  "f1548": "'.$pay_status.'"
			}';
			$curl=curl_init('http://api.ontraport.com/1/objects');

			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			   "Api-Key: ".decrypt(config('app.Hkey')->ont_ky),
               "Api-Appid:".decrypt(config('app.Hkey')->ont_apky)
			)
			);

			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$auth = curl_exec($curl);
			$info = curl_getinfo($curl);
			//print_r($info['http_code']);
			if($info['http_code'] == 200){

				$ch = DB::table('CheckoutPaymentInformation')->where('user_id', $user_id)->update(['activate'=> 0]);
		    	if($ch){
		    		//DB::table('unsubscribe')->where('user_id', $user_id)->update(['subscribe'=> 0]);
		    		DB::table('unsubscribe')->where('user_id', '=', $user_id)->delete();
		    		return redirect('admin/request')->with('message','User request successfully approved')				
							->with('status','success');	
		    	}else{
		    		return redirect('admin/request')->with('message','Error approving request please try again')				
							->with('status','error');	
		    	}

			}else{
				return redirect('admin/request')->with('message','Error approving request please try again')				
							->with('status','error');	
			}
    	
    }

	public function EditUser(Request $request)
    {
		$UserDetails = UserRegistration::where('user_id',$request->user_id)->first();

		$pwds_count = DB::table('pwds')->where('user_id',$request->user_id)->where('act',1)->count();
		$pwds = DB::table('pwds')->where('user_id',$request->user_id)->where('act',1)->first();

       return view('multiauth::admin.edit-user',['UserDetails'=>$UserDetails,'pwds_count'=>$pwds_count,'pwds'=>$pwds]);

    }

	public function PostUser(Request $request){

		$UserRegistration = UserRegistration::where('user_id', '=', $request->user_id)->first();
		$acc_response = 0;
		$sub_response = 0;

		//if user set blank it will not update for security purpose
		
		if($request->rec_account_code != ''){
			if($request->rec_account_code == $UserRegistration->recurly_acc_id){
				$acc_response = 1;
			}else{
				$num = UserRegistration::where('recurly_acc_id', $request->rec_account_code)->count();
				
				if($num == 0){
				DB::table('users')
                ->where('user_id', $request->user_id)
				->update(['recurly_acc_id' => $request->rec_account_code]);
				$acc_response = 1;
				}else{
					$acc_response = 0;	
				}
			}
		}else{
			$acc_response = 1;
		}

		


		if($request->subscription_id != ''){
			if($request->subscription_id == $UserRegistration->subscription_uuid){
				$sub_response = 1;
			}else{
				$num = UserRegistration::where('subscription_uuid', $request->subscription_id)->count();
				
				if($num == 0){
				DB::table('users')
                ->where('user_id', $request->user_id)
				->update(['subscription_uuid' => $request->subscription_id]);
				$sub_response = 1;
				}else{
					$sub_response = 0;	
				}
			}

			
		}else{
			$sub_response = 1;
		}

		
	
		if($acc_response == 0){
			return redirect()->back()->with('message' ,'Account Code already exist')->with('status' ,'error');
		}
		
		if($sub_response == 0){
			return redirect()->back()->with('message' ,'Subscription ID already exist')->with('status' ,'error');
		}

		//email
		$results_em = UserRegistration::where('email', $request->uemail)->where('user_id','!=', $request->user_id)->count();
    	if($results_em == 0){
        
            DB::table('users')
                ->where('user_id', $request->user_id)
                ->update(['email' => $request->uemail]);
              
        }else{
            return redirect()->back()->with('message' ,'Email already exist')->with('status' ,'error');
		}

		//display name
    	$results = UserRegistration::where('name', $request->disName)->where('user_id','!=', $request->user_id)->count();
    	if($results == 0){
        	$UserRegistration = UserRegistration::where('user_id', '=', $request->user_id)->first();
        	
        	$log = DB::table('user_name')->insert([ 
                'user_id' => $request->user_id,
                'name' => $UserRegistration->name,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            DB::table('users')
                ->where('user_id', $request->user_id)
                ->update([
                    'name' => $request->disName,
                    'phone_number_validation_required' => $request->phone_number_validation_required
                ]);
            return redirect()->back()->with('message' ,'Account details Updated Successfully')->with('status' ,'success');    
        }else{
            return redirect()->back()->with('message' ,'This Community Display Name not Available')->with('status' ,'error');
		}
		
	
		
    	if($acc_response == 1 AND $sub_response == 1){
			return redirect()->back()->with('message' ,'Account details Updated Successfully')->with('status' ,'success');    
		}
    }
	

	public function EditUserProcess(Request $request){

		
		$account_type_permission_date = date('Y-m-d');
					
		$UserRegistration = UserRegistration::leftjoin('states','users.state','=','states.id')
							->leftjoin('countries','users.country','=','countries.id')
							->select('users.*', 'states.name as sname', 'countries.name as cname')			
							->where('users.user_id','=',$request->user_id)
							->first();
							
		$current_account_type = $request->account;			
		$plan_price = $request->account_type;
		
		if($current_account_type == 'demo'){
					
				if($plan_price == '$25,000'){
					$ontra_account_type='OUP EVAL25k';	
				}
				elseif($plan_price == '$50,000'){
					$ontra_account_type='OUP EVAL50k';
				}
				elseif($plan_price == '$100,000'){
					$ontra_account_type='OUP EVAL100k';
				}
				elseif($plan_price == '$150,000'){
					$ontra_account_type='OUP EVAL150k';
				}
				elseif($plan_price == '$250,000'){
					$ontra_account_type='OUP EVAL250k';
				}
					
		}elseif($current_account_type == 'live'){
		
				if($plan_price == '$25,000'){
					$ontra_account_type='OUP LIVE25k';	
				}
				elseif($plan_price == '$50,000'){
					$ontra_account_type='OUP LIVE50k';
				}
				elseif($plan_price == '$100,000'){
					$ontra_account_type='OUP LIVE100k';
				}
				elseif($plan_price == '$150,000'){
					$ontra_account_type='OUP LIVE150k';
				}
				elseif($plan_price == '$250,000'){
					$ontra_account_type='OUP LIVE250k';
				}			
		
		}
		
					
		$last_account_type = $UserRegistration->account_type;
		$last_ontra_account_type = $UserRegistration->account_type_from_ontra;
		$add_ontra_account_type_sequence = '';
		$remove_ontra_account_type_sequence = '';
					
					if($last_account_type == 'trial'){
						
						
						if($ontra_account_type == 'OUP EVAL25k')
						{
							$add_ontra_account_type_sequence = 24;
							$remove_ontra_account_type_sequence = 11;
						
						}elseif($ontra_account_type == 'OUP EVAL50k')
						{
							$add_ontra_account_type_sequence = 25;
							$remove_ontra_account_type_sequence = 11;
						
						}elseif($ontra_account_type == 'OUP EVAL100k')
						{
							$add_ontra_account_type_sequence = 26;
							$remove_ontra_account_type_sequence = 11;
						
						}elseif($ontra_account_type == 'OUP EVAL150k')
						{
							$add_ontra_account_type_sequence = 27;
							$remove_ontra_account_type_sequence = 11;
						
						}elseif($ontra_account_type == 'OUP EVAL250k')
						{
							$add_ontra_account_type_sequence = 28;
							$remove_ontra_account_type_sequence = 11;
						}	
					
					
					}elseif($last_account_type == ''){
						
						
						if($ontra_account_type == 'OUP EVAL25k')
						{
							$add_ontra_account_type_sequence = 24;
						
						}elseif($ontra_account_type == 'OUP EVAL50k')
						{
							$add_ontra_account_type_sequence = 25;
						
						}elseif($ontra_account_type == 'OUP EVAL100k')
						{
							$add_ontra_account_type_sequence = 26;
						
						}elseif($ontra_account_type == 'OUP EVAL150k')
						{
							$add_ontra_account_type_sequence = 27;
						
						}elseif($ontra_account_type == 'OUP EVAL250k')
						{
							$add_ontra_account_type_sequence = 28;
							
						}	
						
					
					}elseif($last_account_type == 'demo'){
						
						
						if($last_ontra_account_type == 'OUP EVAL25k')
						{
							$add_ontra_account_type_sequence = 18;
							$remove_ontra_account_type_sequence = 24;
						
						}elseif($last_ontra_account_type == 'OUP EVAL50k')
						{
							$add_ontra_account_type_sequence = 14;
							$remove_ontra_account_type_sequence = 25;
						
						}elseif($last_ontra_account_type == 'OUP EVAL100k')
						{
							$add_ontra_account_type_sequence = 19;
							$remove_ontra_account_type_sequence = 26;
						
						}elseif($last_ontra_account_type == 'OUP EVAL150k')
						{
							$add_ontra_account_type_sequence = 21;
							$remove_ontra_account_type_sequence = 27;
						
						}elseif($last_ontra_account_type == 'OUP EVAL250k')
						{
							$add_ontra_account_type_sequence = 28;
							$remove_ontra_account_type_sequence = 24;
							
						}	
					
					}
					
		$ontra_Account_ID=$ontra_first_name=$ontra_last_name=$ontra_email=$ontra_contact_no=$ontra_address=$ontra_city=$ontra_state=$ontra_country=$ontra_billing_zip=$ontra_demo_password = '';
					
		$ontra_Account_ID = trim($UserRegistration->first_name.$UserRegistration->last_name.'OUP'.$UserRegistration->Account_ID);
		$ontra_contact_id = trim($UserRegistration['ontra_contact_id']);
		$ontra_first_name = trim($UserRegistration['first_name']);
		$ontra_last_name = trim($UserRegistration['last_name']);
		$ontra_email = trim($UserRegistration['email']);
		$ontra_contact_no = trim($UserRegistration['contact_no']);
		$ontra_address = trim($UserRegistration['address']);
		$ontra_city = trim($UserRegistration['city']);
		$ontra_state = trim($UserRegistration['sname']);
		$ontra_country = trim($UserRegistration['cname']);
		$ontra_billing_zip = trim($UserRegistration['zip']);
		$ontra_demo_password = trim($UserRegistration['ontra_demo_password']);
					
					
		$post_data = '';
					
					if($ontra_contact_id != '')
					{
						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
						$post_data.="<contact id='$ontra_contact_id'>";
					
					}else{
						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
						$post_data.='<contact>';
					}
					
					
					// $post_data.='<Group_Tag name="Sequences and Tags">';
					// if(!empty($remove_ontra_account_type_sequence))
					// {
					// 	$post_data.='<field name="Sequences" action="remove">'.trim($remove_ontra_account_type_sequence).'</field>';
					// }		
					// $post_data.='<field name="Sequences">'.trim($add_ontra_account_type_sequence).'</field>';
					// $post_data.='</Group_Tag>';
					
					$post_data.='<Group_Tag name="Contact Information">			
								 <field name="First Name">'.trim($ontra_first_name).'</field>
								 <field name="Last Name">'.trim($ontra_last_name).'</field>
								 <field name="Email">'.trim($ontra_email).'</field>
								 <field name="Office Phone">'.trim($ontra_contact_no).'</field>
								 <field name="Address">'.trim($ontra_address).'</field>
								 <field name="City">'.trim($ontra_city).'</field>
								 <field name="State">'.trim($ontra_state).'</field>
								 <field name="Country">'.trim($ontra_country).'</field>
								 <field name="Zip Code">'.trim($ontra_billing_zip).'</field>
								 <field name="Account Type">'.trim($ontra_account_type).'</field>
								 <field name="Account Status">Enabled</field>
								 </Group_Tag>
								 <Group_Tag name="ACCOUNT SETTINGS">			
								 <field name="Days">30</field>		
								 </Group_Tag>';
					
					if(!empty($ontra_demo_password)){
						
						$post_data.='<Group_Tag name="Demo Account Information">
									<field name="Password">'.$ontra_demo_password.'</field>
									</Group_Tag>';
					}
					$post_data.='</contact>';
					
					$curl = curl_init('https://api.ontraport.com/cdata.php');
					curl_setopt( $curl, CURLOPT_POST, true );			
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data);			
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
					$auth = curl_exec($curl);			
					$info = curl_getinfo($curl);			
					//print_r($info);
					// echo"<br><br>";			
					//print_r($auth);			
					$res=json_decode($auth);			
					//echo"<pre>";			
					//print_r($res);						
								
					$xml = new \SimpleXMLElement($auth);			
					
					//print_r($xml);
					$array_1 = json_decode(json_encode((array)$xml), TRUE);
					
					
					
					
					if(empty($ontra_contact_id)){
						
						$ontra_contact_id='';
						$ontra_created_date='';
						$ontra_dlm='';
						$ontra_score='';
						$ontra_purl='';
						$ontra_bulk_mail='';
						$ontra_first_name='';
						$ontra_last_name='';
						$ontra_email='';
						$ontra_title='';
						$ontra_company='';
						$oontra_account_type='';
						$ontra_account_status='';
						$ontra_address='';
						$ontra_address2='';
						$ontra_city='';
						$oontra_state='';
						$ontra_zip='';
						$oontra_country='';
						$ontra_fax='';
						$ontra_sms_number='';
						$ontra_offc_phone='';
						$ontra_birthday='';
						$ontra_website='';
						$ontra_spent='';
						$ontra_date_modified='';
						$ontra_ip_address='';
						$ontra_last_activity='';
						$ontra_last_note='';
						$ontra_is_agree='';
						$ontra_paypal_address='';
						$ontra_no_of_sales='';
						$ontra_last_total_invoice='';
						$ontra_last_invoice_no='';
						$ontra_last_charge='';
						$ontra_last_total_invoice2='';
						$ontra_total_amount_unpaid='';
						$ontra_card_type='';
						$ontra_card_number='';
						$ontra_card_expiry_month='';
						$ontra_last_cc_status='';
						$ontra_card_expiry_year='';
						$ontra_card_expiry_date='';
						$ontra_date_added='';
						$ontra_trading_experience='';
						$ontra_trading_strategy='';
						$ontra_traded_live_before='';
						$ontra_still_trading_live='';
						$ontra_accounts_traded_live='';
						$ontra_avg_trades_per_day='';
						$ontra_time_in_trade='';
						$ontra_5_day_statement='';
						$ontra_user_ip='';
						$ontra_about_trader='';
						$ontra_live_user_id='';
						$ontra_live_account_id='';
						$ontra_password_live='';
						$ontra_live_activation_date='';
						$ontra_live_expiration_date='';
						$ontra_live_account_balance='';
						$ontra_live_termination='';
						$ontra_status='';
						$ontra_live_trading_status='';
						$ontra_termination_reason='';
						$ontra_demo_user_id='';
						$ontra_demo_account_id='';
						$oontra_demo_password='';
						$ontra_activation_date='';
						$ontra_expiration_date='';
						$ontra_termination_date='';
						$ontra_questionnaire='';
						$ontra_contest_start='';
						$ontra_contest_end='';
						$ontra_contest_confirmed='';
						$ontra_trading_status='';
						$ontra_ending_account_balance='';
						$ontra_demo_results='';
						$ontra_demo_fail_reasons='';
						$ontra_products_traded='';
						$ontra_trading_platform='';
						$ontra_professional_background='';
						$ontra_trading_style='';
						$ontra_why_trading='';
						$ontra_daily_preparation='';
						$ontra_short_term_goals='';
						$ontra_long_term_goals='';
						$ontra_strengths='';
						$ontra_weaknesses='';
						$ontra_last_inbound_sms='';
						$ontra_iB_id='';
						$ontra_account_value='';
						$ontra_min_account_balance='';
						$ontra_fcm_id='';
						$ontra_rms_buy_limit='';
						$ontra_rms_sell_limit='';
						$ontra_rms_loss_limit='';
						$ontra_rms_max_order='';
						$ontra_commision_fill_rate='';
						$ontra_days='';
						$ontra_send_to_rithmic='';
						$ontra_update_date_time='';

						if(!is_array($array_1['contact']['@attributes']['id'])) {
							 $ontra_contact_id = $array_1['contact']['@attributes']['id'];

							 //add remove sequene code here 
                            if(!empty($remove_ontra_account_type_sequence))
                            {
								Log::info('Admin PostUpgrade - Ontraport, remove list by id: ' . "objectID=0&remove_list=".$remove_ontra_account_type_sequence."&ids=".$ontra_contact_id."");

                                $ch = curl_init();

                                curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/sequence');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                                curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&remove_list=".$remove_ontra_account_type_sequence."&ids=".$ontra_contact_id."");

                                $headers = array();
                                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                                $headers[] = 'Accept: application/json';
                                $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                                $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                                $result = curl_exec($ch);
								curl_close($ch);
								
								Log::info('Admin PostUpgrade - Ontraport, remove list result: ' . print_r($result, 1));
                            }		


                            //new sequence add code place here
							Log::info('Admin PostUpgrade - Ontraport, add list by id: ' . $add_ontra_account_type_sequence."&ids=".$ontra_contact_id."&sub_type=Sequence");
                            $ch = curl_init();

                            curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/subscribe');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                            curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&add_list=".$add_ontra_account_type_sequence."&ids=".$ontra_contact_id."&sub_type=Sequence");

                            $headers = array();
                            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                            $headers[] = 'Accept: application/json';
                            $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                            $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                            $result = curl_exec($ch);
                            // if (curl_errno($ch)) {
                            // echo 'Error:' . curl_error($ch);
                            // }
							curl_close($ch);
							
							Log::info('Admin PostUpgrade - Ontraport, add list result: ' . print_r($result));
						}

						$dataToSaveInDatabase = [];
                
						try {
							if (sizeof($array_1) > 0) {
								$dataToSaveInDatabase = $this->parseOntraportDataToDatabase($array_1);
							}
						} catch (\Throwable $th) {
							//throw $th;

							Log::info('Error fill user ontraport data');
							Log::info('');
							Log::info('');
							
							Log::info($th->getMessage());
							Log::info($th->getTraceAsString());
							
							// todo - redirect to homepage - done by miguel alfaiate
							die("Please contact administrator");
						}

						
							
						$Uupdated_at = date('Y-m-d H:i:s');
							
						DB::table('users')
							->where('user_id', Auth::user()->user_id)
							->update($dataToSaveInDatabase);
					
					}
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					
					if(empty($last_account_type) || ($last_account_type == 'demo')){
						
						$plan_price = $UserRegistration->temp_account_type;
					
						if($plan_price == '$25,000'){
							$ontra_account_type='OUP EVAL25k';	
						}
						elseif($plan_price == '$50,000'){
							$ontra_account_type='OUP EVAL50k';
						}
						elseif($plan_price == '$100,000'){
							$ontra_account_type='OUP EVAL100k';
						}
						elseif($plan_price == '$150,000'){
							$ontra_account_type='OUP EVAL150k';
						}
						elseif($plan_price == '$250,000'){
							$ontra_account_type='OUP EVAL250k';
						}
						
						$get_DemoAccountId = UserRegistration::where('user_id','=',$request->user_id)
																->first();
						$DemoAccountId = $get_DemoAccountId->first_name.$get_DemoAccountId->last_name.'OUP'.$get_DemoAccountId->Account_ID;
						
						$ontra_contact_id = $get_DemoAccountId->ontra_contact_id;
						$postt_data = '';
						
						$postt_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
						$postt_data.="<contact id='$ontra_contact_id'>";
						$postt_data.='<Group_Tag name="Contact Information">	
									 <field name="Account Type">'.trim($ontra_account_type).'</field>
									 </Group_Tag>';
						if(empty($ontra_demo_password))
						{	
							$ontra_passwd_characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';
							$OntraPasswd = '';
							$max = strlen($ontra_passwd_characters) - 1;
							
							for ($i = 0; $i < 7; $i++) {
								$OntraPasswd.= $ontra_passwd_characters[mt_rand(0, $max)];
							}
							
							$postt_data.='<Group_Tag name="Demo Account Information">
										 <field name="Demo Account Id">'.$DemoAccountId.'</field>
										 <field name="Password">'.$OntraPasswd.'</field>
										 </Group_Tag>';
							$ontra_demo_password = $OntraPasswd;		 
						
						}else{
							$postt_data.='<Group_Tag name="Demo Account Information">
										 <field name="Demo Account Id">'.$DemoAccountId.'</field>
										 <field name="Password">'.$ontra_demo_password.'</field>
										 </Group_Tag>';
						}
					
						$postt_data.='</contact>';
						

						Log::info('ResetBalance - Sent contact to Ontraport. Data: ' . print_r($postt_data, 1));


						$curl = curl_init('https://api.ontraport.com/cdata.php');			
						curl_setopt( $curl, CURLOPT_POST, true );			
						curl_setopt( $curl, CURLOPT_POSTFIELDS, $postt_data);			
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
						$auth = curl_exec($curl);			
						$info = curl_getinfo($curl);	
						
						Log::info('ResetBalance - Contact Ontraport result: ' . print_r($auth, 1));

						//print_r($info);
						// echo"<br><br>";			
						//print_r($auth);			
						$res=json_decode($auth);			
						//echo"<pre>";			
						//print_r($res);						
									
						$xml = new \SimpleXMLElement($auth);			
						//print_r($xml);
						
						DB::table('users')
							->where('user_id', $request->user_id)
							->update(['ontra_demo_password' => $ontra_demo_password]);
					}
					
					
				
					/* for email */
					$to = $UserRegistration['email'];		
					$subject='Regarding Ontraport Rithmic Webapp Password';			
					$message='Thank You for your Payment in OntraportRedhmicWebapp. Enjoy our services.';			
					$from='support@OntraportRedhmicWebapp.com';			
					//$headers .= 'From: <support@constacloud.com>' . "\r\n";			
					//$headers .= 'Cc: myboss@example.com' . "\r\n";			
					//$headers = "From: ".$from."\r\n";			
					//$headers .= "Reply-To:".$from."\r\n";			
					//$headers .= "Content-Type: text/html";						
					//mail($to,$subject,$message,$headers);		
					
					Log::info('ResetBalance - Updating account type');
					
					if(($last_ontra_account_type == 'OUP TRIAL 14DAY') || ($last_ontra_account_type == '')){
						DB::table('users')			
							->where('user_id', $request->user_id)			
							->update(['account_type_from_ontra' => trim($ontra_account_type),'account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s')]);
					}else{
						DB::table('users')			
							->where('user_id', $request->user_id)			
							->update(['account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s')]);
					}	
						
					Log::info('ResetBalance - Updating done!');
		
		return ('updated');
			
    }

    public function resetBalance(Request $request){

    		$re_user = $request->user_id;

            $get_ontra_contact_id = UserRegistration::where('user_id','=',$re_user)->first();
            
			$last_date = $get_ontra_contact_id->updated_at;
			
			$array_log = Array(
				'user id: ' => $re_user,
				'Ontra contact id: ' => $get_ontra_contact_id,
				'last date: ' => $last_date,
			);
		   Log::info('ResetBalance - Data: ' . print_r($array_log, 1));
		   
            $last_updated_at = date("Y-m-d", strtotime($last_date));
            
            $now_date = date('Y-m-d');
            $last_demo_account_id = $get_ontra_contact_id->ontra_demo_account_id;
			$ontra_account_type = $get_ontra_contact_id->ontra_account_type;

			$ontra_contact_id = $get_ontra_contact_id->ontra_contact_id;
			$first_name = $get_ontra_contact_id->first_name;
			$last_name = $get_ontra_contact_id->last_name;

			$state = "";
			if($request->billing_state != "" || $request->billing_state != null){
				$st = DB::table('states')->where('id', $request->billing_state)->first();
				$state = $st->sortname;
			}else{
				$state = "";
			}
			Log::info('ResetBalance - State: ' . print_r($state, 1));
			$cn = DB::table('countries')->where('id', $request->billing_country)->first();

        
		    		        
			        
			        $dateValue = date('Y-m-d');
					$time=strtotime($dateValue);
					$day = date("d",$time);
					$month=date("m",$time);
					$year=date("y",$time);
					$random =  (mt_rand(1000,9999));
					$NewDemoAccountId = trim($first_name).trim($last_name).'OUP'.$month.$day.$random;
			        
					$post_data = "";
			        $post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";
					$post_data.="<contact id='$ontra_contact_id'>";
					// $post_data.='<Group_Tag name="Sequences and Tags">';
					// $post_data.='<field name="Sequences">30</field>';
					// $post_data.='</Group_Tag>';
					$post_data.='<Group_Tag name="Demo Account Information">
								<field name="Demo Account Id">'.$NewDemoAccountId.'</field>
								<field name="Last Account Id">'.$last_demo_account_id.'</field>
								</Group_Tag>';
					$post_data.='</contact>';
					
					$curl1 = curl_init('https://api.ontraport.com/cdata.php');
					curl_setopt( $curl1, CURLOPT_POST, true );			
					curl_setopt( $curl1, CURLOPT_POSTFIELDS, $post_data);			
					curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);			
					$auth1 = curl_exec($curl1);			
					$info1 = curl_getinfo($curl1);			
					$http_code=$info1['http_code'];					
					//print_r($info1);
					// echo"<br><br>";			
					//print_r($auth1);	
					
					if($http_code == 200){
						Log::info('ResetBalance - Data comming from Ontraport: ' . print_r($auth1, 1));

							// $res1=json_decode($auth1);			
										
							//print_r($res1);		
							
							$xml = new \SimpleXMLElement($auth1);			
							
							$array_1 = json_decode(json_encode((array)$xml), TRUE); 
							Log::info('XML parsed: ' . print_r($array_1, 1));
							//add add sequence code here and remove above.

						$ch = curl_init();

						curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/subscribe');
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

						curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&add_list=30&ids=".$ontra_contact_id."&sub_type=Sequence");

						$headers = array();
						$headers[] = 'Content-Type: application/x-www-form-urlencoded';
						$headers[] = 'Accept: application/json';
						$headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
						$headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

						$result = curl_exec($ch);
						// if (curl_errno($ch)) {
						// echo 'Error:' . curl_error($ch);
						// }
						curl_close($ch);
						
						Log::info('Raw data received of the Ontraport: ' . print_r($result, 1));
						

							$curr_date = date('Y-m-d');
					        $daystosum = '30';
							$ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));
							

				
					        $PaymentInfo = new PaymentInfo;           
					        $PaymentInfo->user_id = $re_user;          
					        $PaymentInfo->transaction_id = 'None';  
					        $PaymentInfo->invoice_id = 'None';          
					        $PaymentInfo->amount = '$100.00';         
					        $PaymentInfo->payment_date = $curr_date;           
					        $PaymentInfo->expiry_date = $ex_date;
					        $PaymentInfo->type = 'Reset Account';             
					        $PaymentInfo->save();   

							$Uupdated_at = date('Y-m-d H:i:s');
							DB::table('users')			
								->where('user_id', $re_user)			
								->update(['ontra_demo_account_id' => $NewDemoAccountId,'updated_at' => $Uupdated_at]);

							$csvUser = UserRegistration::where('user_id','=',$re_user)->first();	
							$demoUserID = $csvUser['ontra_demo_user_id'];
							//Start post CSV for RESET BALANCE Part 1
							//$csvUser = UserRegistration::where('user_id','=',Auth::user()->user_id)->first();
							$csValue = $csvUser['temp_account_type'];

							if($csValue == '$25,000'){
								$account_value = 25000;
					            $auto_liquidate = 25000;	
					            $Account_Threshold = 1500;
					            $RMS_buy_limit = 3;
					            $RMS_sell_limit = 3;
					            $RMS_loss_limit = 500;
					            $RMS_max_order_qty = 9;
					            $min_account_balance = 23500;
					            $Commission_fill_rate = 2.5;
					            $day = 30;

					            $profit_target = 1500;	
					            $target_days = 15;
							}elseif($csValue == '$50,000'){
								$account_value = 50000;
					            $auto_liquidate = 50000;	
					            $Account_Threshold = 2500;
					             $RMS_buy_limit = 6;
					            $RMS_sell_limit = 6;
					            $RMS_loss_limit = 1250;
					            $RMS_max_order_qty = 18;
					            $min_account_balance = 47500;
					            $Commission_fill_rate = 2.5;
					            $day = 30;

					             $profit_target = 3000;	
					            $target_days = 15;
							}elseif($csValue == '$100,000'){
								$account_value = 100000;
					            $auto_liquidate = 100000;	
					            $Account_Threshold = 3500;
					             $RMS_buy_limit = 12;
					            $RMS_sell_limit = 12;
					            $RMS_loss_limit = 2500;
					            $RMS_max_order_qty = 36;
					            $min_account_balance = 96500;
					            $Commission_fill_rate = 2.5;
					            $day = 30;

					             $profit_target = 6000;	
					            $target_days = 15;
							}elseif($csValue == '$150,000'){
								$account_value = 150000;
					            $auto_liquidate = 150000;	
					            $Account_Threshold = 5000;
					             $RMS_buy_limit = 15;
					            $RMS_sell_limit = 15;
					            $RMS_loss_limit = 4000;
					            $RMS_max_order_qty = 45;
					            $min_account_balance = 145000;
					            $Commission_fill_rate = 2.5;
					            $day = 30;

					             $profit_target = 9000;	
					            $target_days = 15;
							}elseif($csValue == '$250,000'){
								$account_value = 250000;
					            $auto_liquidate = 250000;	
					            $Account_Threshold = 5500;
					             $RMS_buy_limit = 25;
					            $RMS_sell_limit = 25;
					            $RMS_loss_limit = 5000;
					            $RMS_max_order_qty = 75;
					            $min_account_balance = 244500;
					            $Commission_fill_rate = 2.5;
					            $day = 30;

					             $profit_target = 15000;	
					            $target_days = 15;
							}
					             
					          if($csvUser['state'] != ''){
					            $sta = states::where('id','=',$csvUser['state'])->first();
					            $stName = $sta['name'];
					          }else{
					            $stName = '';
							  }
							  




//uncomment this when live
								


					            $data = array(
					                    'IB_id' => $csvUser['ontra_iB_id'],
					                    'User_ID' => $csvUser['ontra_demo_user_id'],
					                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
					                    'trading_status' => trim($csvUser['ontra_trading_status']),
					                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
					                    'account_value' => $account_value,
					                    'RMS_buy_limit' => $RMS_buy_limit,
					                    'RMS_sell_limit' => $RMS_sell_limit,
					                    'RMS_loss_limit' => $RMS_loss_limit,
					                    'RMS_max_order_qty' => $RMS_max_order_qty,
					                    'min_account_balance' => $min_account_balance,
					                    'Commission_fill_rate' => $Commission_fill_rate,
					                    'days' => $day,
					                    'state' => trim($stName),
					                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
					                    'auto_liquidate' => $auto_liquidate,
					                    'Last_Account_ID' => trim($last_demo_account_id),
					                    'Account_Threshold' => $Account_Threshold,
					                );
					             Log::info('ResetBalance - CSV sent to reset: ' . print_r($http_code, 1));
					            $ch = curl_init();
					            $curlConfig = array(
					                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/rb_1.php",
					                CURLOPT_POST           => true,
					                CURLOPT_RETURNTRANSFER => true,
					                CURLOPT_POSTFIELDS     => $data
					            );
					            curl_setopt_array($ch, $curlConfig);
					            $result = curl_exec($ch);
					            curl_close($ch);
					        //End post CSV for RESET BALANCE Part 1


					        //Start post CSV for RESET BALANCE Part 2
					           date_default_timezone_set("America/Chicago");
					            $curr_time = date('Y-m-d H:i:s');
					            $run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));

								$reset_data_data = [
									'IB_id' => $csvUser['ontra_iB_id'],
					                'User_ID' => $csvUser['ontra_demo_user_id'],
					                'demo_account_id' => $csvUser['ontra_demo_account_id'],
					                'trading_status' => $csvUser['ontra_trading_status'],
					                'FCM_ID' => $csvUser['ontra_fcm_id'],
					                'account_value' => $account_value,
					               	'RMS_buy_limit' => $RMS_buy_limit,
					                'RMS_sell_limit' => $RMS_sell_limit,
					                'RMS_loss_limit' => $RMS_loss_limit,
					                'RMS_max_order_qty' => $RMS_max_order_qty,
					                'min_account_balance' => $min_account_balance,
					                'Commission_fill_rate' => $Commission_fill_rate,
					                'days' => $csvUser['ontra_days'],
					                'state' => trim($stName),
					                'risk_algorithm' => $csvUser['ontra_send_to_rithmic'],
					                'auto_liquidate' => $auto_liquidate,
					                'Last_Account_ID' => trim($last_demo_account_id),
					                'Account_Threshold' => $Account_Threshold,
					                'curr_time' => $curr_time,
					                'run_time' => $run_time,
                                    'Account_type' => $ontra_account_type
							];
							Log::info('ResetBalance - csv_reset Save data in DB ' . print_r($reset_data_data, 1));

								DB::table('csv_reset')->insert($reset_data_data);
	


			

						
								


								$ontra_account_data = [					
									'user_id' => $re_user,					
									'account_id' => $NewDemoAccountId,
									'account_type' => $ontra_account_type,
									'acc_def' => 'Reset Account',
									'ontra_account_value' => $account_value,
									'ontra_profit_target' => $profit_target,
									'ontra_target_days' => $target_days,
									'ontra_rms_buy_limit' => $csvUser['ontra_rms_buy_limit'],
									'ontra_daily_loss_limit' => $csvUser['ontra_daily_loss_limit'],
									'ontra_max_down' => $csvUser['ontra_max_down'],
									'ontra_activation_date' => $csvUser['ontra_activation_date'],
									'ontra_expiration_date' => $csvUser['ontra_expiration_date'],							
									'updated_at' => date('Y-m-d H:i:s')				
								];
					        //End post CSV for RESET BALANCE Part 2  
							Log::info('ResetBalance - ontra_account Save data in DB ' . print_r($ontra_account_data, 1));
					            $curr_date = date('Y-m-d');
					            $daystosum = '14';
					            $ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));
								DB::table('ontra_account')->insert($ontra_account_data);


								//disabling all account id keep latest 3

							 try{
								
                                $old_ontra_accounts = DB::table('ontra_account')->select('account_id')->where('user_id',$re_user)->count();

                                    if($old_ontra_accounts > 3){

                                        $get_olds = DB::table('ontra_account')->select('account_id')->where('user_id',$re_user)
                                        ->orderBy('id','desc')->take($old_ontra_accounts)->skip(3)->get();

                                        foreach($get_olds as $oa => $old){
                                            $username = $old->account_id;
                                            $diable_status = '0';

                                                $check_first = DB::table('AS_R_User')->where('Username', $username)->count();
                                                    if($check_first != 0){
                                                            $ut = new Utils();
                                                            $ut->SignalUser($username,$diable_status);
                                                    }

                                        }
                                        
                                        
                                    }

							}catch(\Exception $e){
								Log::info('ResetBalance - Error try executing SignalUser utility. Error: ' . print_r($e, 1));
							}


							//Reset confirmtaion email
									
												$emailTB = DB::table('tb_email')->where('id','=', 6)->first();
												$subject = 'Account Reset Confirmation';
												$to = $get_ontra_contact_id->email;
												
											
												$data = array("emailTB"=>$emailTB,"demoUserID"=>$demoUserID,"account_value"=>$account_value);

							Log::info('ResetBalance - Sending confirmation email');
							try{
									Mail::send('auth.emails.AccountReset', $data, function($message) use ($to,$emailTB) {
										$message->to($to);
										$message->from($emailTB->email,'OneUp Trader');
										$message->subject('Account Reset Confirmation');
									
									});
								} catch(\Exception $e){
								//echo $e;
									Log::info('ResetBalance - mail not sent, error: ' . print_r($e, 1));
								}

							Log::info('ResetBalance - Done!');

					            return \Response::json('success');
					}else{
						Log::info('ResetBalance - Error try connect to Ontraport, Http status code: ' . $http_code);
						return \Response::json('error');
					}            	
		
			
    }


	public function ChangeUserStatus(Request $request){

		$status = $request->d1;
		$user_id = $request->d2;

		DB::table('users')->where('user_id', $user_id)->update(['status' => $status]);

		return ($status);

	}

	public function DelUser(Request $request){

		$user_id = $request->uid;

		DB::table('users')->where('user_id', $user_id)->update(['del_state' => 1,'status'=>0]);

		return redirect()->back()->with('message' ,'User deleted successfully')->with('status' ,'success');

	}

	public function ChangeCommentStatus(Request $request){

		$status = $request->d1;
		$comment_id = $request->d2;

		DB::table('CommentDetails')->where('id', $comment_id)->update(['Cstatus' => $status]);

		return ($status);

	}


	public function DeleteUserMsg(Request $request){

		DB::table('Chat')->where('id', $request->d1)->update(['delete_status' => '1']);

		return ($request->d1);			

	}	

	public function DeleteUserPost(Request $request){

		DB::table('WallPost')->where('wall_id', $request->d1)->update(['delete_status' => '1']);

		return ($request->d1);			

	}


	public function DeleteUserPostMail(Request $request){

		DB::table('WallPost')->where('wall_id', $request->d1)->update(['delete_status' => '1']);

		$userID = DB::table('WallPost')->where('wall_id','=',$request->d1)->first();

		$userReg = UserRegistration::where('user_id','=',$userID->user_id)->first();
  
		$to = $userReg->email;
		$message = '<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8">
		          <link href="http://fonts.googleapis.com/css?family=Roboto:400,300,700&amp;subset=latin,cyrillic,greek" rel="stylesheet" type="text/css" />
		          </head>
		          <body  style="font-size:12px; width:100%; height:100%;">
					<table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #efefef; width: 800px; max-width: 800px; outline: rgb(239, 239, 239) solid 1px; box-shadow: rgb(224, 224, 224) 0px 0px 5px; margin: 0px auto;"><!--START TOP NAVIGATION ​LAYOUT--><tbody><tr><td valign="top">
					            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ecebeb; margin: 0px auto;"><!-- START CONTAINER NAVIGATION --><tbody><tr><td align="center" valign="top">
					                  <!-- start top navigation container -->
					                  <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="padding-left: 20px; padding-right: 20px; background-color: #ecebeb; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                        <!-- start top navigaton -->
					                        <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start space --><tbody><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                          </tr><!-- end space --><tr><td valign="middle">
					                              <table align="left" border="0" cellspacing="0" cellpadding="0" class="container2" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="140" style="width: 140px;">
					                                    <a href="https://oneuptrader.com" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0"><img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170212021144_oneup.png" width="140" style="max-width: 140px; display: block !important; width: 140px; height: auto;" alt="" border="0" hspace="0" vspace="0" height="auto"></a>
					                                  </td>
					                                </tr><!-- start space --><tr><td valign="top" class="increase-Height-20">
					                                  </td>
					                                </tr><!-- end space --></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table border="0" align="right" cellpadding="0" cellspacing="0" class="container2" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!--start call us --><tbody><tr><td valign="middle" align="center">
					                                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="clear-align" style="height: 100%; margin: 0px auto;" width="auto"><tbody><tr><td style="font-size: 13px; color: #a3a2a2; font-weight: 300; text-align: center; font-family: Roboto, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 21px;" align="center"><span style="color: #999999; text-decoration: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="https://app.oneuptrader.net" data-mce-href="https://app.oneuptrader.net" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">MY ACCOUNT</font></span></font></a><a href="http://oneuptrader.com/site/about-us/" style="color: #a3a2a2; text-decoration: none !important; border-style: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="http://oneuptrader.com/site/about-us/" target="_blank" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">&nbsp; &nbsp;</font></span></font></a> <a href="http://help.oneuptrader.com" style="color: #a3a2a2; text-decoration: none !important; border-style: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="http://help.oneuptrader.com" target="_blank" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">SUPPORT</font></span></font></a></font></span></td>
					                                      </tr></tbody></table></td>
					                                </tr><!--end call us --></tbody></table></td>
					                          </tr><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                          </tr><!-- end space --></tbody></table><!-- end top navigaton --></td>
					                    </tr></tbody></table><!-- end top navigation container --></td>
					              </tr><!-- END CONTAINER NAVIGATION --></tbody></table></td>
					        </tr><!--END TOP NAVIGATION ​LAYOUT--></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
					                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
					              </tr></tbody></table></td>
					        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
					                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
					              </tr></tbody></table></td>
					        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
					                              <!-- start button -->
					                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; text-decoration: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #333333; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">YOUR POST HAS BEEN</font></span> FLAGGED</font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
					                                </tr></tbody></table><!-- end button --></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
					                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
					              </tr></tbody></table></td>
					        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
					                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
					              </tr></tbody></table></td>
					        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START LAYOUT 2--><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start layout-2 container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start layout-2 container width 600px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start image and content --><tbody><tr><td valign="top" width="100%">
					                        
					                        <table width="270" border="0" cellspacing="0" cellpadding="0" align="left" class="full-width" style="width: 270px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="bottom" align="center" width="165" style="width: 165px;">
					                              
					                                <img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170227230031_003-warning.png" width="165" alt="" style="max-width: 165px; display: block !important; width: 165px; height: auto;" border="0" hspace="0" vspace="0" height="auto">
					                            </td>
					                          </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table class="remove" width="1" border="0" cellpadding="0" cellspacing="0" align="left" style="font-size: 0px; line-height: 0; border-collapse: collapse; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td width="0" height="2" style="border-collapse: collapse; width: 0px; height: 2px; line-height: 2px; font-size: 2px;">
					                              <p style="padding-left: 20px;">&nbsp;</p>
					                            </td>
					                          </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="270" border="0" cellspacing="0" cellpadding="0" align="right" class="container" style="width: 270px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!--start space height --><tbody><tr><td height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                          </tr><!--end space height --><!--start space height --><tr><td height="1" class="remove" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                          </tr><!--end space height --><!-- start text content --><tr><td valign="top">
					                              <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 22px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #555555; font-weight: 300; text-align: left; word-break: break-word; line-height: 30px;" align="left"><span style="color: #333333; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">Your post was flagged for violation of our community rules and guidelines.</font></span></td>
					                                </tr><!--start space height --><tr><td height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                </tr><!--end space height --><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #808080; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">This is an official notice and warning that your post on the community dashboard has been flagged as inappropirate and has violated the community guidelines. &nbsp;Your post has been deleted and any future or repeated posts violating the community rules will be subject to a permanent ban from the OneUp Trader community.<br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"></font></span></font></span></td>
					                                </tr><!--start space height --><tr><td height="6" style="height: 6px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                </tr><!--end space height --><tr><td valign="top" width="auto">
					                                    <!-- start button -->
					                                    <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
					                                          </td>
					                                      </tr></tbody></table><!-- end button --></td>
					                                </tr></tbody></table></td>
					                          </tr><!-- end text content --><!--start space height --><tr><td height="24" style="height: 24px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                          </tr><!--end space height --></tbody></table></td>
					                    </tr><!-- end image and content --></tbody></table><!-- end layout-2 container width 600px --></td>
					              </tr></tbody></table><!-- end layout-2 container width 600px --></td>
					        </tr><!-- END LAYOUT 2  --></tbody><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
					                              <!-- start button -->
					                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; text-decoration: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">COMMUNITY&nbsp;<span style="color: #000000; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">RULES &amp; GUIDELINES</font></span></font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
					                                </tr></tbody></table><!-- end button --></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
					                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
					              </tr></tbody></table></td>
					        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--><!-- START LAYOUT-1/2 --><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!-- start text content --><tbody><tr dup="0"><td valign="top">
					                              <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!--start space height --><tbody><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                </tr><!--end space height --><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, Arial; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">COMMUNITY GENERAL RULES &amp; GUIDELINES</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Do not do any of the following:</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Flame or insult other members</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Bypass any filters</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Abuse or encourage abuse of the Reputation, or Post Reporting Systems</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post personally identifiable information (i.e. address,phone number, etc.)</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Bump threads</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Derail a thread’s topic</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post links to phishing sites</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post spam or Re-post Closed, Modified, Deleted Content</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Repetitively post in the incorrect Channel</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Openly argue with a moderator</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">OFF-LIMIT TOPICS/REPLIES</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Do not post any topics/replies containing the following:</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Inappropriate or offensive content (i.e. Pornography)</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Threats of violence or harassment, even as a joke</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Posted copyright material</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Soliciting, begging, auctioning, raffling, selling, advertising, referrals</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Racism, discrimination</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Abusive language, including swearing</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Drugs and alcohol</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Religious, political, and other “prone to huge arguments” threads</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">NO BACKSEAT MODERATING</strong></font></span></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Let the moderators do the moderating. Backseat moderating is when people who are not moderators try to enforce the forum rules. If you see a person breaking the rules, take advantage of the Report (￼) button or simply ignore the offensive post(s), thread, or review.</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">REPORT POSTS TO MODERATORS</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Should you observe a fellow Community member breaking these rules please report the post or item by clicking the Report button located on every item, post, and review.</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">REPEATED OFFENDERS</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Repeated offenders of the above rules and guidelines will be banned from The OneUp Trader Community. Any moderator has the ability to ban a user for violating the rules at their discretion.</font></span></div></td>
					                                </tr><!--start space height --><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                </tr><!--end space height --></tbody></table></td>
					                          </tr><!-- end text content --><tr><td valign="top" width="100%" align="center">
					                              <!-- start button -->
					                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top">
					                                    </td>
					                                </tr></tbody></table><!-- end button --></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --><!--start space height --><tr><td height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-1/2 --><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
					                              <!-- start button -->
					                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">CONTACT <span style="color: #000000; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">SUPPORT</font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
					                                </tr></tbody></table><!-- end button --></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START LAYOUT-9 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start heading --><!--start space height --><tbody><tr><td height="20" valign="top" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                    </tr><!--end space height --><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="left" valign="top" width="39" style="padding-right: 10px; width: 39px;">
					                              <table width="39" border="0" cellspacing="0" cellpadding="0" align="left" style="width: 39px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr valign="top"><td align="left" valign="middle" width="64" style="width: 64px;">
					                                    <img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170224021534_customer-service.png" width="64" alt="" style="max-width: 64px; display: block !important; width: 64px; height: auto;" border="0" hspace="0" vspace="0" height="auto"></td>
					                                </tr></tbody></table></td>
					                            <td align="left" style="font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #555555; font-weight: 300; text-align: left; word-break: break-word; line-height: 26px;"><span style="color: #555555; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"> <a href="#" style="color: #555555; text-decoration: none !important; border-style: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="#" border="0"><font face="Roboto, Arial, Helvetica, sans-serif">Contact Our Support Team. &nbsp;We&#39;re Here To Help!</font></a></font></span></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end heading --><!--start space height --><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --><!-- start text content --><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><span style="color: #808080; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">We’re friendly and available to chat, Reach out to us anytime and we’ll happily answer your questions. Simply login to your account dashboard or visit our help desk to have all your questions answered. &nbsp;Our current Support hours are 8:30 am to 5:00 pm CST</font></span></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --><!--start space height --><tr><td height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --><!-- start button text --><tr><td valign="top">
					                        <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
					                              <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 12px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #d05d68; font-weight: 300; text-align: center; word-break: break-word; line-height: 20px;" align="center"></td>
					                                  
					                                </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="20" align="left" border="0" cellspacing="0" cellpadding="0" style="width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
					                                  </td>
					                                </tr></tbody></table></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end button text --><!--start space height --><tr><td height="9" style="height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-9 --></tbody><!-- START LAYOUT-1/2 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start container width 560px -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
					                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!-- start text content --><tbody><!-- end text content --><tr><td valign="top" width="100%" align="center">
					                              <!-- start button -->
					                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top">
					                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" dup="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><!-- start space width --><td valign="top" width="10" style="width: 10px;">
					                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
					                                              </td>
					                                            </tr></tbody></table></td>
					                                        <!--end space width -->
					                                        <td width="auto" align="center" valign="middle" height="32" style="background-color: #d05d68; border-radius: 5px; background-clip: padding-box; font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #ffffff; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 21px;" bgcolor="#d05d68"><span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="https://app.oneuptrader.net" data-mce-href="https://app.oneuptrader.net" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">My Account</font></span></font></a></font></span></td>
					                                        <!-- start space width -->
					                                        <td valign="top" width="10" style="width: 10px;">
					                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
					                                              </td>
					                                            </tr></tbody></table></td>
					                                        <!--end space width -->
					                                      </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                      </tr><!--end space height --></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" dup="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><!-- start space width --><td valign="top" width="10" style="width: 10px;">
					                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
					                                              </td>
					                                            </tr></tbody></table></td>
					                                        <!--end space width -->
					                                        <td width="auto" align="center" valign="middle" height="32" style="background-color: #ffffff; border-radius: 5px; border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #d05d68; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 21px;" bgcolor="#ffffff"><span style="color: #d05d68; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="http://help.oneuptrader.com" data-mce-href="http://help.oneuptrader.com" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">Help Desk</font></span></font></a></font></span></td>
					                                        <!-- start space width -->
					                                        <td valign="top" width="10" style="width: 10px;">
					                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
					                                              </td>
					                                            </tr></tbody></table></td>
					                                        <!--end space width -->
					                                      </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                                      </tr><!--end space height --></tbody></table></td>
					                                </tr></tbody></table><!-- end button --></td>
					                          </tr></tbody></table></td>
					                    </tr><!-- end text content --><!--start space height --><tr><td height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
					                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
					              </tr></tbody></table><!-- end  container width 600px --></td>
					        </tr><!-- END LAYOUT-1/2 --></tbody><!-- START LAYOUT-16 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
					            <!-- start  container width 600px -->
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ecebea; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <!-- start logo footer and address -->
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!--start space height --><tbody><tr><td height="14" style="height: 14px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --><tr><td valign="top" align="center">
					                        <!--start icon socail navigation -->
					                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top" align="center">
					                              <table border="0" align="center" cellpadding="0" cellspacing="0" style="table-layout: fixed; margin: 0px auto;" width="auto"><tbody><tr>
					                                  
					                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
					                                    <a href="https://www.youtube.com/c/Oneuptrader" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
					                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-youtube-color.png" width="30" alt="icon-youtube" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
					                                  </td>
					                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
					                                    <a href="https://www.facebook.com/OneUpTrader/" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
					                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-facebook-color.png" width="30" alt="icon-facebook" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
					                                  </td>
					                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
					                                    <a href="https://twitter.com/OneUpTrader" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
					                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-twitter-color.png" width="30" alt="icon-twitter" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
					                                  </td>
					                                  
					                                  
					                                  
					                                </tr></tbody></table></td>
					                          </tr></tbody></table><!--end icon socail navigation --></td>
					                    </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --></tbody></table><!-- end logo footer and address --></td>
					              </tr></tbody></table></td>
					        </tr><!-- END LAYOUT-16--></tbody><!--  START FOOTER COPY RIGHT --><tbody><tr><td align="center" valign="top" style="background-color:#d05d68;" bgcolor="#d05d68">
					            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #d05d68; padding-left: 20px; padding-right: 20px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
					                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="width: 560px; margin: 0px auto;"><!--start space height --><tbody><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --><tr><!-- start COPY RIGHT content --><td valign="top" style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #ffffff; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">'.date('Y').' © OneUp Trader. All Rights Reserved.&nbsp;</font></span></div></td>
					                      <!-- end COPY RIGHT content -->
					                    </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
					                    </tr><!--end space height --></tbody></table></td>
					              </tr></tbody></table></td>
					        </tr><!--  END FOOTER COPY RIGHT --></tbody></table></body></html>';
		//$headers = "MIME-Version: 1.0" . "\r\n";
		//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		//$headers .= 'From: OneUp Trader<'.$emailTB->email.'>' . "\r\n";
		//mail($to,$subject,$message,$headers);
		$subject = "OneUpTrader Post Flagged";
         
        $url = 'https://api.sendgrid.com/';
        $user = env('MAIL_USERNAME');
 		$pass = env('MAIL_PASSWORD');
        $params = array(
        'api_user'  => $user,
        'api_key'   => $pass,
        'to'        => $to,
        'subject'   => $subject,
        'html'      => $message,
        'from'      => 'support@oneuptrader.com',
		'fromname'  => "OneUp Trader",

         );
        $request1 =  $url.'api/mail.send.json';
        $session = curl_init($request1);
		curl_setopt ($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $pass));
        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($session);
        curl_close($session);

		return ($request->d1);			

	}

	public function PostList(){

		

        $PostDetails = Homepage::leftjoin('users', 'WallPost.user_id', '=', 'users.user_id')
							 ->leftjoin('Category', 'WallPost.cat_id' , '=', 'Category.id')
							 ->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
							 ->select('WallPost.*', 'users.name','users.first_name','users.last_name', 'Category.cat_name', 'SubCategory.sub_cat_name')
							 ->where('WallPost.delete_status', '=', '0')
                             ->OrderBy('WallPost.id','desc')
                             ->get();

        return view('multiauth::admin.post-list',['PostDetails'=>$PostDetails]);

		}
		


		public function GetPostList(Request $request)
    {
			$post_type = $request->post_type;

			$user_id = $request->query('user_id');

		
			
		//echo "yes";die;
		$offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'WallPost.id';
                break;
            case 'Posted By':
                $sort = 'users.name';
                break;
           /* case 'Posted Image':
                $sort = 'image_url';
                break;*/
            case 'Post Date':
                $sort = 'WallPost.post_time';
                break;
			case 'Content':
				$sort = 'WallPost.description';
				break;
			case 'Category':
				$sort = 'Category.cat_name';
				break;
			case 'Sub Category':
				$sort = 'SubCategory.sub_cat_name';
				break;
			case 'Status':
				$sort = 'WallPost.status';
				break;
            default:
                $sort = 'WallPost.id';
        }

        $data=array();
        $rows=array();

        $columns=['users.name', 'WallPost.post_time', 'WallPost.description','Category.cat_name','SubCategory.sub_cat_name',
		'WallPost.status'];
		
		
        $posts = DB::table('WallPost')
			->leftjoin('users', 'WallPost.user_id', '=', 'users.user_id')
			->leftjoin('Category', 'WallPost.cat_id' , '=', 'Category.id')
			->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
			->select('WallPost.*', 'users.name','users.first_name','users.last_name', 'Category.cat_name', 'SubCategory.sub_cat_name')
			->where('WallPost.delete_status', '=', '0')
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
								}
								
						})
						->where(function ($query) use($user_id){
						if($user_id != '')
						{
							$query->where('WallPost.user_id', '=', $user_id);	
						}						
						})
						->where(function ($query) use($post_type){
						if($post_type != '' AND $post_type != 'all')
						{
										$query->where('WallPost.cat_id', '=', $post_type);							
						}
						})->orderBy($sort, $order)->skip($offset)->take($limit)->get();
			
			$posts_total = DB::table('WallPost')
				->leftjoin('users', 'WallPost.user_id', '=', 'users.user_id')
				->leftjoin('Category', 'WallPost.cat_id' , '=', 'Category.id')
				->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
				->where('WallPost.delete_status', '=', '0')
				->where(function ($query) use($search, $columns){
					if($search!='')
					{
						$query->where($columns[0], 'like', '%'.$search.'%');
						for($i=1;$i< count($columns);$i++)
						{
							$query->orWhere($columns[$i], 'like', '%'.$search.'%');
						}
					}
				})->where(function ($query) use($user_id){
						if($user_id != '')
						{
							$query->where('WallPost.user_id', '=', $user_id);	
						}						
						})
				->where(function ($query) use($post_type){
						if($post_type != '' AND $post_type != 'all')
						{
										$query->where('WallPost.cat_id', '=', $post_type);							
						}
						})->count();

			$sno = $offset+1;
				foreach($posts as $row)
        {
					$row->sno = $sno++;
				}  
			$data['total']=$posts_total;
			$data['rows']=$posts;
			
        return response()->json($data);

		}
		

			public function GetChatList(Request $request)
    {
		$chat_type = $request->chat_type;

		$offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'id';
                break;
            case 'Chat From':
                $sort = 'username';
                break;
            case 'Chat Group Category':
                $sort = 'category';
                break;
            case 'Chat Group Subcategory':
                $sort = 'sub_category';
                break;
            default:
                $sort = 'id';
        }

        $data=array();
        $rows=array();

        $columns=['username', 'category', 'sub_category'];
		
       $chats = DB::table('Chat')
			->select('id', 'user_id','username', 'category', 'sub_category')
			 ->where('message','!=', '')
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
						})
						->where(function ($query) use($chat_type){
						if($chat_type != '' AND $chat_type != 'all')
						{
										$query->where('category', '=', $chat_type);							
						}
						})->GroupBy('username','category','sub_category')->orderBy($sort, $order)->skip($offset)->take($limit)->get();
			
			$chats_total = DB::table('Chat')
                ->where('message','!=', '')
				->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
						})
						->where(function ($query) use($chat_type){
						if($chat_type != '' AND $chat_type != 'all')
						{
										$query->where('category', '=', $chat_type);							
						}
						})->GroupBy('username','category','sub_category')->get();
			
				$sno = $offset+1;
				foreach($chats as $row)
        {
					$row->sno = $sno++;
				}  
			$data['total']=count($chats_total);
			$data['rows']=$chats;
						
		
        return response()->json($data);

		}
		
		
    public function singlePost(Request $request){

    	$check = DB::table('WallPost')->where('wall_id','=',$request->wall_id)->where('delete_status', '=', '0')->count();
		if($check != 0){
				$link['UserDetails']=Homepage::leftjoin('users' , 'WallPost.user_id', '=', 'users.user_id')
						->leftjoin('Category', 'WallPost.cat_id', '=', 'Category.id')
						->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
						->select('WallPost.*', 'users.name', 'users.first_name', 'users.last_name', 'users.ImageUrl','users.NewImageUrl','Category.cat_name','SubCategory.sub_cat_name')
						->where('WallPost.delete_status', '=', '0')
						->where('WallPost.wall_id', '=', $request->wall_id)
						->get();
				$link['CommCount']= PostComments::where('CommentDetails.Cstatus','=',1)	
	            ->where('wall_id', '=', $request->wall_id)			
	            ->count();		

	            $link['likes'] = LikeUnlike::where('Lstatus','=',1)	
	             ->where('wall_id', '=', $request->wall_id)			
				->count();	
				$link['commList']=PostComments::leftjoin('users','CommentDetails.user_id','=','users.user_id')				
				                ->where('CommentDetails.wall_id','=', $request->wall_id)				
                ->select('CommentDetails.*','users.name','users.first_name','users.last_name','users.ImageUrl','users.NewImageUrl')->OrderBy('id','asc')->get();						
	           // print_r($CommCount);
        	return \Response::json($link);	
		}else{
			
        	return 'error';	
		}

    }

	public function PostListF(Request $request){

		$PostDetails = Homepage::leftjoin('users', 'WallPost.user_id', '=', 'users.user_id')
							 ->leftjoin('Category', 'WallPost.cat_id' , '=', 'Category.id')
							 ->leftjoin('SubCategory', 'WallPost.sub_cat_id', '=', 'SubCategory.id')
							 ->select('WallPost.*', 'users.name', 'users.first_name' ,'users.last_name', 'Category.cat_name', 'SubCategory.sub_cat_name')
                             ->OrderBy('WallPost.id','desc')
							 ->where('users.user_id', '=' ,$request->user_id)
							 ->where('WallPost.delete_status', '=', '0')
                             ->get();

		return view('multiauth::admin.post-list',['PostDetails'=>$PostDetails]);					 

	}



	public function ChatList(){

		$ChatDetails = Chat::where('message','!=', '')
							->GroupBy('username','category','sub_category')
							->OrderBy('id','asc')
                            ->get();

		return view('multiauth::admin.chat-list',['ChatDetails'=>$ChatDetails]);

	}

	public function ChangePostStatus(Request $request){

		$status = $request->d1;
		$wall_id = $request->d2;

		DB::table('WallPost')->where('Wall_id', $wall_id)->update(['status' => $status]);

		return ($status);

	}

	public function ChangePostStatusMail(Request $request){

		$status = $request->d1;
		$wall_id = $request->d2;

		DB::table('WallPost')->where('Wall_id', $wall_id)->update(['status' => $status]);

		$userID = DB::table('WallPost')->where('wall_id','=',$wall_id)->first();

		$userReg = UserRegistration::where('user_id','=',$userID->user_id)->first();
  
			$to = $userReg->email;
			$message = '<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8">
			          <link href="http://fonts.googleapis.com/css?family=Roboto:400,300,700&amp;subset=latin,cyrillic,greek" rel="stylesheet" type="text/css" />
			          </head>
			          <body  style="font-size:12px; width:100%; height:100%;">
						<table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #efefef; width: 800px; max-width: 800px; outline: rgb(239, 239, 239) solid 1px; box-shadow: rgb(224, 224, 224) 0px 0px 5px; margin: 0px auto;"><!--START TOP NAVIGATION ​LAYOUT--><tbody><tr><td valign="top">
						            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ecebeb; margin: 0px auto;"><!-- START CONTAINER NAVIGATION --><tbody><tr><td align="center" valign="top">
						                  <!-- start top navigation container -->
						                  <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="padding-left: 20px; padding-right: 20px; background-color: #ecebeb; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                        <!-- start top navigaton -->
						                        <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start space --><tbody><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                          </tr><!-- end space --><tr><td valign="middle">
						                              <table align="left" border="0" cellspacing="0" cellpadding="0" class="container2" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="140" style="width: 140px;">
						                                    <a href="https://oneuptrader.com" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0"><img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170212021144_oneup.png" width="140" style="max-width: 140px; display: block !important; width: 140px; height: auto;" alt="" border="0" hspace="0" vspace="0" height="auto"></a>
						                                  </td>
						                                </tr><!-- start space --><tr><td valign="top" class="increase-Height-20">
						                                  </td>
						                                </tr><!-- end space --></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table border="0" align="right" cellpadding="0" cellspacing="0" class="container2" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!--start call us --><tbody><tr><td valign="middle" align="center">
						                                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="clear-align" style="height: 100%; margin: 0px auto;" width="auto"><tbody><tr><td style="font-size: 13px; color: #a3a2a2; font-weight: 300; text-align: center; font-family: Roboto, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 21px;" align="center"><span style="color: #999999; text-decoration: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="https://app.oneuptrader.net" data-mce-href="https://app.oneuptrader.net" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">MY ACCOUNT</font></span></font></a><a href="http://oneuptrader.com/site/about-us/" style="color: #a3a2a2; text-decoration: none !important; border-style: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="http://oneuptrader.com/site/about-us/" target="_blank" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">&nbsp; &nbsp;</font></span></font></a> <a href="http://help.oneuptrader.com" style="color: #a3a2a2; text-decoration: none !important; border-style: none; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="http://help.oneuptrader.com" target="_blank" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #999999; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">SUPPORT</font></span></font></a></font></span></td>
						                                      </tr></tbody></table></td>
						                                </tr><!--end call us --></tbody></table></td>
						                          </tr><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                          </tr><!-- end space --></tbody></table><!-- end top navigaton --></td>
						                    </tr></tbody></table><!-- end top navigation container --></td>
						              </tr><!-- END CONTAINER NAVIGATION --></tbody></table></td>
						        </tr><!--END TOP NAVIGATION ​LAYOUT--></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
						                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
						              </tr></tbody></table></td>
						        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
						                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
						              </tr></tbody></table></td>
						        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
						                              <!-- start button -->
						                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; text-decoration: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #333333; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">YOUR POST HAS BEEN</font></span> FLAGGED</font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
						                                </tr></tbody></table><!-- end button --></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
						                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
						              </tr></tbody></table></td>
						        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
						                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
						              </tr></tbody></table></td>
						        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--></tbody><!-- START LAYOUT 2--><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start layout-2 container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start layout-2 container width 600px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start image and content --><tbody><tr><td valign="top" width="100%">
						                        
						                        <table width="270" border="0" cellspacing="0" cellpadding="0" align="left" class="full-width" style="width: 270px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="bottom" align="center" width="165" style="width: 165px;">
						                              
						                                <img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170227230031_003-warning.png" width="165" alt="" style="max-width: 165px; display: block !important; width: 165px; height: auto;" border="0" hspace="0" vspace="0" height="auto">
						                            </td>
						                          </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table class="remove" width="1" border="0" cellpadding="0" cellspacing="0" align="left" style="font-size: 0px; line-height: 0; border-collapse: collapse; width: 1px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td width="0" height="2" style="border-collapse: collapse; width: 0px; height: 2px; line-height: 2px; font-size: 2px;">
						                              <p style="padding-left: 20px;">&nbsp;</p>
						                            </td>
						                          </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="270" border="0" cellspacing="0" cellpadding="0" align="right" class="container" style="width: 270px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!--start space height --><tbody><tr><td height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                          </tr><!--end space height --><!--start space height --><tr><td height="1" class="remove" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                          </tr><!--end space height --><!-- start text content --><tr><td valign="top">
						                              <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 22px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #555555; font-weight: 300; text-align: left; word-break: break-word; line-height: 30px;" align="left"><span style="color: #333333; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">Your post was flagged for violation of our community rules and guidelines.</font></span></td>
						                                </tr><!--start space height --><tr><td height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                </tr><!--end space height --><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #808080; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">This is an official notice and warning that your post on the community dashboard has been flagged as inappropirate and has violated the community guidelines. &nbsp;Your post has been deleted and any future or repeated posts violating the community rules will be subject to a permanent ban from the OneUp Trader community.<br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"></font></span></font></span></td>
						                                </tr><!--start space height --><tr><td height="6" style="height: 6px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                </tr><!--end space height --><tr><td valign="top" width="auto">
						                                    <!-- start button -->
						                                    <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
						                                          </td>
						                                      </tr></tbody></table><!-- end button --></td>
						                                </tr></tbody></table></td>
						                          </tr><!-- end text content --><!--start space height --><tr><td height="24" style="height: 24px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                          </tr><!--end space height --></tbody></table></td>
						                    </tr><!-- end image and content --></tbody></table><!-- end layout-2 container width 600px --></td>
						              </tr></tbody></table><!-- end layout-2 container width 600px --></td>
						        </tr><!-- END LAYOUT 2  --></tbody><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
						                              <!-- start button -->
						                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; text-decoration: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">COMMUNITY&nbsp;<span style="color: #000000; line-height: 30px; font-size: 22px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">RULES &amp; GUIDELINES</font></span></font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
						                                </tr></tbody></table><!-- end button --></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START HEIGHT SPACE 20PX LAYOUT-1 --><tr><td valign="top" align="center" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;" class="full-width"><tbody><tr><td valign="top" height="20" width="20" style="width: 20px; height: 20px; line-height: 20px; font-size: 20px;">
						                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display: block !important; max-height: 20px; max-width: 20px;"></td>
						              </tr></tbody></table></td>
						        </tr><!-- END HEIGHT SPACE 20PX LAYOUT-1--><!-- START LAYOUT-1/2 --><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!-- start text content --><tbody><tr dup="0"><td valign="top">
						                              <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!--start space height --><tbody><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                </tr><!--end space height --><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, Arial; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">COMMUNITY GENERAL RULES &amp; GUIDELINES</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Do not do any of the following:</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Flame or insult other members</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Bypass any filters</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Abuse or encourage abuse of the Reputation, or Post Reporting Systems</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post personally identifiable information (i.e. address,phone number, etc.)</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Bump threads</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Derail a thread’s topic</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post links to phishing sites</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Post spam or Re-post Closed, Modified, Deleted Content</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Repetitively post in the incorrect Channel</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Openly argue with a moderator</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">OFF-LIMIT TOPICS/REPLIES</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Do not post any topics/replies containing the following:</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Inappropriate or offensive content (i.e. Pornography)</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Threats of violence or harassment, even as a joke</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Posted copyright material</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Soliciting, begging, auctioning, raffling, selling, advertising, referrals</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Racism, discrimination</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Abusive language, including swearing</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Drugs and alcohol</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">&nbsp;&nbsp;&nbsp; •&nbsp;&nbsp;&nbsp; Religious, political, and other “prone to huge arguments” threads</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">NO BACKSEAT MODERATING</strong></font></span></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Let the moderators do the moderating. Backseat moderating is when people who are not moderators try to enforce the forum rules. If you see a person breaking the rules, take advantage of the Report (￼) button or simply ignore the offensive post(s), thread, or review.</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">REPORT POSTS TO MODERATORS</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Should you observe a fellow Community member breaking these rules please report the post or item by clicking the Report button located on every item, post, and review.</font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"></div><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial"><strong style="font-size: 13px; font-weight: bold; font-family: Roboto, Arial, Helvetica, Arial;">REPEATED OFFENDERS</strong></font></span><br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><span style="color: #333333; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, Arial;"><font face="Roboto, Arial, Helvetica, Arial">Repeated offenders of the above rules and guidelines will be banned from The OneUp Trader Community. Any moderator has the ability to ban a user for violating the rules at their discretion.</font></span></div></td>
						                                </tr><!--start space height --><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                </tr><!--end space height --></tbody></table></td>
						                          </tr><!-- end text content --><tr><td valign="top" width="100%" align="center">
						                              <!-- start button -->
						                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top">
						                                    </td>
						                                </tr></tbody></table><!-- end button --></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --><!--start space height --><tr><td height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-1/2 --><!-- START LAYOUT-1/1 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><tbody><tr><td valign="top" width="auto" align="center">
						                              <!-- start button -->
						                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #a3a2a2; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 26px;"><span style="color: #a3a2a2; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">CONTACT <span style="color: #000000; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">SUPPORT</font></span><a href="#" data-mce-href="#" style="border-style: none; text-decoration: none !important; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"></a></font></span></font></span></td>
						                                </tr></tbody></table><!-- end button --></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-1/1 --></tbody><!-- START LAYOUT-9 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start heading --><!--start space height --><tbody><tr><td height="20" valign="top" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                    </tr><!--end space height --><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="left" valign="top" width="39" style="padding-right: 10px; width: 39px;">
						                              <table width="39" border="0" cellspacing="0" cellpadding="0" align="left" style="width: 39px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr valign="top"><td align="left" valign="middle" width="64" style="width: 64px;">
						                                    <img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170224021534_customer-service.png" width="64" alt="" style="max-width: 64px; display: block !important; width: 64px; height: auto;" border="0" hspace="0" vspace="0" height="auto"></td>
						                                </tr></tbody></table></td>
						                            <td align="left" style="font-size: 18px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #555555; font-weight: 300; text-align: left; word-break: break-word; line-height: 26px;"><span style="color: #555555; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"> <a href="#" style="color: #555555; text-decoration: none !important; border-style: none; line-height: 26px; font-size: 18px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" data-mce-href="#" border="0"><font face="Roboto, Arial, Helvetica, sans-serif">Contact Our Support Team. &nbsp;We&#39;re Here To Help!</font></a></font></span></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end heading --><!--start space height --><tr><td height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --><!-- start text content --><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><span style="color: #808080; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">We’re friendly and available to chat, Reach out to us anytime and we’ll happily answer your questions. Simply login to your account dashboard or visit our help desk to have all your questions answered. &nbsp;Our current Support hours are 8:30 am to 5:00 pm CST</font></span></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --><!--start space height --><tr><td height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --><!-- start button text --><tr><td valign="top">
						                        <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
						                              <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td style="font-size: 12px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #d05d68; font-weight: 300; text-align: center; word-break: break-word; line-height: 20px;" align="center"></td>
						                                  
						                                </tr></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="20" align="left" border="0" cellspacing="0" cellpadding="0" style="width: 20px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
						                                  </td>
						                                </tr></tbody></table></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end button text --><!--start space height --><tr><td height="9" style="height: 9px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-9 --></tbody><!-- START LAYOUT-1/2 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start container width 560px -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start text content --><tbody><tr><td valign="top">
						                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 0px auto;"><!-- start text content --><tbody><!-- end text content --><tr><td valign="top" width="100%" align="center">
						                              <!-- start button -->
						                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top">
						                                    <table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" dup="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><!-- start space width --><td valign="top" width="10" style="width: 10px;">
						                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
						                                              </td>
						                                            </tr></tbody></table></td>
						                                        <!--end space width -->
						                                        <td width="auto" align="center" valign="middle" height="32" style="background-color: #d05d68; border-radius: 5px; background-clip: padding-box; font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #ffffff; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 21px;" bgcolor="#d05d68"><span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="https://app.oneuptrader.net" data-mce-href="https://app.oneuptrader.net" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #ffffff; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">My Account</font></span></font></a></font></span></td>
						                                        <!-- start space width -->
						                                        <td valign="top" width="10" style="width: 10px;">
						                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
						                                              </td>
						                                            </tr></tbody></table></td>
						                                        <!--end space width -->
						                                      </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                      </tr><!--end space height --></tbody></table><!--[if (gte mso 9)|(IE)]></td><td valign="top" ><![endif]--><table width="auto" border="0" align="left" cellpadding="0" cellspacing="0" dup="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><!-- start space width --><td valign="top" width="10" style="width: 10px;">
						                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
						                                              </td>
						                                            </tr></tbody></table></td>
						                                        <!--end space width -->
						                                        <td width="auto" align="center" valign="middle" height="32" style="background-color: #ffffff; border-radius: 5px; border: 1px solid rgb(236, 236, 237); background-clip: padding-box; font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: #d05d68; font-weight: 300; padding-left: 18px; padding-right: 18px; word-break: break-word; line-height: 21px;" bgcolor="#ffffff"><span style="color: #d05d68; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif"><a href="http://help.oneuptrader.com" data-mce-href="http://help.oneuptrader.com" style="border-style: none; text-decoration: none !important; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color: #d05d68; line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">Help Desk</font></span></font></a></font></span></td>
						                                        <!-- start space width -->
						                                        <td valign="top" width="10" style="width: 10px;">
						                                          <table width="10" border="0" align="center" cellpadding="0" cellspacing="0" style="width: 10px; margin: 0px auto;"><tbody><tr><td valign="top">
						                                              </td>
						                                            </tr></tbody></table></td>
						                                        <!--end space width -->
						                                      </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                                      </tr><!--end space height --></tbody></table></td>
						                                </tr></tbody></table><!-- end button --></td>
						                          </tr></tbody></table></td>
						                    </tr><!-- end text content --><!--start space height --><tr><td height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
						                    </tr><!--end space height --></tbody></table><!-- end  container width 560px --></td>
						              </tr></tbody></table><!-- end  container width 600px --></td>
						        </tr><!-- END LAYOUT-1/2 --></tbody><!-- START LAYOUT-16 --><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb;" bgcolor="#ecebeb">
						            <!-- start  container width 600px -->
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ecebea; padding-left: 20px; padding-right: 20px; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <!-- start logo footer and address -->
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!--start space height --><tbody><tr><td height="14" style="height: 14px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --><tr><td valign="top" align="center">
						                        <!--start icon socail navigation -->
						                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top" align="center">
						                              <table border="0" align="center" cellpadding="0" cellspacing="0" style="table-layout: fixed; margin: 0px auto;" width="auto"><tbody><tr>
						                                  
						                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
						                                    <a href="https://www.youtube.com/c/Oneuptrader" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
						                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-youtube-color.png" width="30" alt="icon-youtube" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
						                                  </td>
						                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
						                                    <a href="https://www.facebook.com/OneUpTrader/" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
						                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-facebook-color.png" width="30" alt="icon-facebook" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
						                                  </td>
						                                  <td style="padding-left: 5px; width: 30px; height: 30px; line-height: 30px; font-size: 30px;" height="30" align="center" valign="middle" class="clear-padding" width="30">
						                                    <a href="https://twitter.com/OneUpTrader" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">
						                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-twitter-color.png" width="30" alt="icon-twitter" style="max-width: 30px; height: auto; display: block !important;" border="0" hspace="0" vspace="0" height="auto"></a>
						                                  </td>
						                                  
						                                  
						                                  
						                                </tr></tbody></table></td>
						                          </tr></tbody></table><!--end icon socail navigation --></td>
						                    </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --></tbody></table><!-- end logo footer and address --></td>
						              </tr></tbody></table></td>
						        </tr><!-- END LAYOUT-16--></tbody><!--  START FOOTER COPY RIGHT --><tbody><tr><td align="center" valign="top" style="background-color:#d05d68;" bgcolor="#d05d68">
						            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; background-color: #d05d68; padding-left: 20px; padding-right: 20px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top">
						                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="width: 560px; margin: 0px auto;"><!--start space height --><tbody><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --><tr><!-- start COPY RIGHT content --><td valign="top" style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #ffffff; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left"><div style="text-align: left; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;"><font face="Roboto, Arial, Helvetica, sans-serif">'.date('Y').' © OneUp Trader. All Rights Reserved.&nbsp;</font></span></div></td>
						                      <!-- end COPY RIGHT content -->
						                    </tr><!--start space height --><tr><td height="10" style="height: 10px; font-size: 0px; line-height: 0; border-collapse: collapse;"></td>
						                    </tr><!--end space height --></tbody></table></td>
						              </tr></tbody></table></td>
						        </tr><!--  END FOOTER COPY RIGHT --></tbody></table></body></html>';
			//$headers = "MIME-Version: 1.0" . "\r\n";
			//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			//$headers .= 'From: OneUp Trader<'.$emailTB->email.'>' . "\r\n";
			//mail($to,$subject,$message,$headers);
			$subject = "OneUpTrader Post Flagged";
             
            $url = 'https://api.sendgrid.com/';
            $user = env('MAIL_USERNAME');
            $pass = env('MAIL_PASSWORD');
            $params = array(
            'api_user'  => $user,
            'api_key'   => $pass,
            'to'        => $to,
            'subject'   => $subject,
            'html'      => $message,
            'from'      => 'support@oneuptrader.com',
			'fromname'  => "OneUp Trader",

             );
            $request =  $url.'api/mail.send.json';
            $session = curl_init($request);
			curl_setopt ($session, CURLOPT_POST, true);
			curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $pass));
            curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($session);
            curl_close($session);

		return ($status);

	}



	public function emaillist(){
	    
	    $UserDetails=tb_email::OrderBy('id','asc')->get();

       return view('multiauth::admin.email-content',['UserDetails'=>$UserDetails]);
	
	}
	public function dashboard2(){
	
		$user_detail = DB::table('users')->select('user_id','ontra_demo_user_id')->OrderBy('id','asc')->get();
		

		return view('multiauth::admin.dashboard_admin',['user_detail'=>$user_detail]);        
	    //return view('dashboard_admin');
		
	}

	public function getAdmin(){
		$UserDetails=DB::table('Admin')->where('type', '=', 'admin')->OrderBy('id','DESC')->get();

       return view('multiauth::admin.add-admin',['UserDetails'=>$UserDetails]);

	}

	public function addAdmin(Request $request){
		//$request->username;
		//$request->uemail;
		//$request->pass;

		$adminCount = AdminLogin::where('email','=',$request->uemail)->count();
		if($adminCount == 0){
			$adminSave = new AdminLogin;			
			$adminSave->name = $request->username;			
			$adminSave->email = $request->uemail;			
			$adminSave->password = $request->pass;	
			$adminSave->type = 'admin';			
			$adminSave->save();	
			return redirect('admin/add-admin')->with('message','New admin added successfully')->with('status','success');	
		}else{
			return redirect('admin/add-admin')->with('message','This email id already exist')->with('status','error');	
		}


	}

	public function editGetAdmin($id){
		$users = AdminLogin::where('id', $id)->first();
	 	return view('multiauth::admin.edit-admin',['users'=>$users]);
	}

	public function editUpdateAdmin(Request $request){
		
		$check = AdminLogin::where('id', $request->uid)->update(['name' => $request->username,'email'=> $request->uemail,'password'=> $request->pass]);
		if($check){

			return redirect('admin/add-admin')->with('message','New admin added successfully')->with('status','success');	
		}else{
			return redirect('admin/add-admin')->with('message','Update failed please try again')->with('status','error');
		}
		

	}

	public function delAdmin(Request $request){
		$id = $request->uid;
		$check = AdminLogin::where('id', '=', $id)->delete();
		if($check){

			return \Response::json('success');	
		}else{
			return \Response::json('error');	
		}
	} 

	public function askAcc(Request $request){

		$acc_detail = DB::table('ontra_account')->where('user_id', $request->acc)->get();

		return \Response::json($acc_detail);

	}

	public function userChange(Request $request){

				//$UserRegistration = UserRegistration::where('user_id','=',session()->get('userid'))->first();
				if($request->acc != ''){

						$cnt = DB::table('AS_R_User')->where('username', $request->acc)->count();
						$uid = DB::table('AS_R_User')->where('username', $request->acc)->first();
						$report = new ReportingLibrary();
				        $report->setOutput(ReportingLibrary::PHP);
				        date_default_timezone_set("America/Chicago");
						//$pred = date('Y-m-d 17:00:00', strtotime('-30 days'));
						
			$getU_count = DB::table('ontra_account')->where('account_id', $request->acc)->count();

				if($getU_count != 0){
										$getU = DB::table('ontra_account')->where('account_id', $request->acc)->first();
										$Reg_User = DB::table('users')->where('user_id', $getU->user_id)->first();
										if($request->acc != $Reg_User->ontra_demo_account_id){

											$o_acc = DB::table('ontra_account')->where('user_id', $getU->user_id)->orderBy('id', 'ASC')->get()->toArray();
											$c_cnt =  DB::table('ontra_account')->where('user_id', $getU->user_id)->count();

											for($i = 0;$i< $c_cnt;$i++){
												if($o_acc[$i]->account_id == $request->acc){
													$link['last'] = date("m/d/Y", strtotime($o_acc[$i]->updated_at));
													date_default_timezone_set("America/Chicago");
													$link['now'] = date('m/d/Y', strtotime($o_acc[$i+1]->updated_at));
													$pred = date('Y-m-d 17:00:00', strtotime('-1 days',strtotime($o_acc[$i]->updated_at)));
													$predTo = date('Y-m-d 15:00:00', strtotime($o_acc[$i+1]->updated_at));
													$last = date('Y-m-d', strtotime($o_acc[$i]->updated_at));
													$last2 = date('Y-m-d', strtotime($o_acc[$i+1]->updated_at));
												}
											}

										
									}else{

											
											$resetDate = PaymentInfo::where('user_id','=',$getU->user_id)
											->where('type','=','Reset Account')->orderBy('id', 'desc')->first();
											
											date_default_timezone_set("America/Chicago");
											$nn =  date('Y-m-d');
											if($resetDate){

												if($Reg_User->ontra_activation_date > $resetDate->payment_date){
							
													if($Reg_User->ontra_activation_date == $nn){
														$link['last'] = '00/00/0000';
														$link['now'] = '00/00/0000';
													}else{
														$link['last'] = date("m/d/Y", strtotime($Reg_User->ontra_activation_date));
														date_default_timezone_set("America/Chicago");
														$link['now'] = date('m/d/Y', strtotime('-1 days'));
														$pred = date('Y-m-d 17:00:00', strtotime('-1 days',strtotime($Reg_User->ontra_activation_date)));
														$predTo = date('Y-m-d 15:00:00', strtotime('-1 days'));
														$last = date('Y-m-d', strtotime($Reg_User->ontra_activation_date));
														$last2 = date('Y-m-d', strtotime($predTo));
													}

												}else{

													if($resetDate->payment_date == $nn){
														$link['last'] = '00/00/0000';
														$link['now'] = '00/00/0000';
													}else{
														$link['last'] = date("m/d/Y", strtotime($resetDate->payment_date));
														date_default_timezone_set("America/Chicago");
														$link['now'] = date('m/d/Y', strtotime('-1 days'));
														$pred = date('Y-m-d 17:00:00', strtotime('-1 days',strtotime($resetDate->payment_date)));
														$predTo = date('Y-m-d 15:00:00', strtotime('-1 days'));
														$last = date('Y-m-d', strtotime($resetDate->payment_date));
														$last2 = date('Y-m-d', strtotime($predTo));
													}
													
												}
											}else{
												if($Reg_User->ontra_activation_date == $nn){
														$link['last'] = '00/00/0000';
														$link['now'] = '00/00/0000';
													}else{
														$link['last'] = date("m/d/Y", strtotime($Reg_User->ontra_activation_date));
														date_default_timezone_set("America/Chicago");
														$link['now'] = date('m/d/Y', strtotime('-1 days'));
														$pred = date('Y-m-d 17:00:00', strtotime('-1 days',strtotime($Reg_User->ontra_activation_date)));
														$predTo = date('Y-m-d 15:00:00', strtotime('-1 days'));
														$last = date('Y-m-d', strtotime($Reg_User->ontra_activation_date));
														$last2 = date('Y-m-d', strtotime($predTo));
													}
											}	
										}	
										
										
										
										
									$ontra_account = DB::table('ontra_account')->where('account_id', $request->acc)->first();
										$link['ontra_account'] = $ontra_account;	

									

										if($cnt != 0){
											$d1 = new DateTime($pred);
											$d2 = new DateTime($predTo);
											date_default_timezone_set("America/Chicago");
											$preDay1 = date('Y-m-d 17:00:00', strtotime('-10 days'));
											$proSinceD = new DateTime($preDay1);
											$profitSinceDay = $report->profitSinceDay($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['profitSinceDay'] = $profitSinceDay->value;
											//print_r($preDay1);
											date_default_timezone_set("America/Chicago");
											$preDay2 = date('Y-m-d 17:00:00', strtotime('-6 days'));
											$profitCumulD = new DateTime($preDay2);
											$profitCumul = $report->profitCumulative($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['profitCumul'] = $profitCumul->value;

											$test1 = $report->tradePerWeek($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['perWeek'] = $test1->value;

											$profitPerWeek = $report->profitPerWeek($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['profitPerWeek'] = $profitPerWeek->value;


											$test2 = $report->tradePerHour($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['perHour'] = $test2->value;
											
											$proPerHr = $report->profitPerHour($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['proPerHr'] = $proPerHr->value;
											//print_r($proPerHr->value);

											$tradeDura = $report->tradeDuration($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['tradeDura'] = $tradeDura->value;

											$profitDura = $report->profitDuration($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['profitDura'] = $profitDura->value;

											$tradeInstrument = $report->tradeInstrument($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['tradeInstrument'] = $tradeInstrument->value;

											$proInst = $report->profitInstrument($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['proInst'] = $proInst->value;

											$profitType = $report->profitType($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['profitType'] = $profitType->value;

											

											$winLoss = $report->winLossPercentage($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['winLoss'] = $winLoss->value;


											$dashboardData = $report->userDashboard(array('user' => $uid->ID),new DateTime($pred),new DateTime($predTo));
											$link['dashboardData'] = $dashboardData;
											//date_default_timezone_set("America/Chicago");
											//$preDay = date('Y-m-d H:i:s', strtotime('-15 days'));
											//$winD = new DateTime($preDay);
											$win = $report->winningPercentage($uid->ID,new DateTime($pred),new DateTime($predTo));
											$link['winPer'] = $win->value;

											
											$cur_bal = $report->getBalance($uid->ID);
											$link['cur_bal'] = $cur_bal->value;

											date_default_timezone_set("America/Chicago");
											$dt = date('Y-m-d 20:00:00');
											$link['curBalance'] = AdminController::testChart($dt,$uid->ID);
											
											//Gross Cumulative P&L and Drawdown value

											$ontra_acc = DB::table('ontra_account')->where('account_id', $request->acc)->first();
											$pred1 = new DateTime($last);
											$now = new DateTime($last2);
											$interval = $pred1->diff($now);

											$list = array();

											for($i=0;$i< $interval->days;$i++){
												
												$next = date($last.' 23:00:00');
												
												$ls = new DateTime($last);
												$nw = new DateTime($next);

												$to = $ls->getTimestamp();
												$from = $nw->getTimestamp();
												$result = DB::table('AS_R_RitBalance')
												->select('Balance', 'Timestamp')
												->where('ID_User', $uid->ID) 
												->where('Timestamp','>', $to)
												->where('Timestamp', '<' ,$from)
												->orderBy('ID', 'DESC')
												->first();
												$list[$i]['startBal'] = $ontra_acc->ontra_account_value;
												$list[$i]['Profit'] = $ontra_acc->ontra_account_value + $ontra_acc->ontra_profit_target;
												if(!$result){
													if($i > 0){
														$list[$i]['balance'] = $list[$i-1]['balance'];
														$list[$i]['date'] = date("m-d-Y", strtotime($last));
													}else{
														$list[$i]['balance'] = $ontra_acc->ontra_account_value;;
														$list[$i]['date'] = date("m-d-Y", strtotime($last));
													}
													
												}else{
													$list[$i]['balance'] = $result->Balance;
													$list[$i]['date'] = date("m-d-Y", $result->Timestamp);
												}
											

												$last = date('Y-m-d',strtotime('+1 days',strtotime($last)));
												
											}
											$link['cum'] = $list;
											

											return \Response::json($link);
										}else{
											$link['status'] = "error";
											
											return \Response::json($link);
										}

						}else{
							$link['status'] = "error";
							return \Response::json($link);
						}   					

				}else{

					$link['last'] = '00/00/0000';
	        		$link['now'] = '00/00/0000';

	        		$link['status'] = "error";
					    	
					return \Response::json($link);
					
				}

		        
		}

		public function testChart($dt,$uid){	
						
			$nw = new DateTime($dt);

			
			$from = $nw->getTimestamp();

			//$Reg_User = DB::table('users')->where('user_id', session()->get('userid'))->first();
			
			$result = DB::table('AS_R_RitBalance')
                ->select('Balance', 'Timestamp')
                ->where('Timestamp','>', $from) 
                ->where('ID_User', $uid)
                ->orderBy('ID', 'DESC')
                ->first();
             if(!$result){
             	
             	$dtNew = date('Y-m-d 20:00:00', strtotime('-1 days',strtotime($dt)));

				return AdminController::testChart($dtNew,$uid);

             }else{
             	return $result->Balance;

             }    

			
			
		}

	public function confirm(){	

		$query=DB::table('unassign')->where('status', '=', false)->groupby('user_id')->distinct()->get();
		$ids = [];

		    foreach($query as $q)
		    {
		      array_push($ids, $q->user_id);

		    }
		
			$UserDetails   =DB::table('users')->whereIn('user_id', $ids)->get();
		
       return view('multiauth::admin.confirm',['UserDetails'=>$UserDetails]);
	}

	public function unverified(){	

		// $UserDetails=DB::table('users')->where('email_verified_at',NULL)->orderBy('id', 'desc')->get();
		//return view('multiauth::admin.unverified',['UserDetails'=>$UserDetails]);

       return view('multiauth::admin.unverified');
	}

	public function get_unverified_users(Request $request){
	
        $offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'id';
                break;
            case 'Username':
                $sort = 'name';
                break;
            case 'Name':
                $sort = 'first_name';
                break;
            case 'Email':
                $sort = 'email';
                break;
           
            default:
                $sort = 'id';
        }

        $data=array();
        $rows=array();

        $columns=['name', 'first_name', 'last_name','email'];

        $users = DB::table('users')

            ->select('user_id','name','first_name','last_name','email')
            ->where('email_verified_at', '=', NULL)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->orderBy($sort, $order)->skip($offset)->take($limit)->get();

        $users_total = DB::table('users')
            ->where('email_verified_at', '=', NULL)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
            })->count();

        $data['total']=$users_total;
        $data['rows']=$users;

        return response()->json($data);

    
	}

	public function PostVerify(Request $request){	

		$uid = $request->resUser;
		$timestamp = date('Y-m-d H:i:s');
		UserRegistration::where('user_id',$uid)->update(['email_verified_at' => $timestamp]);
		
        return Redirect('admin/unverified')->with('message','User verification done successfully')->with('status','success');
       
	}

	public function conPost(Request $request){	

			$u_userid = $request->uid;
			$CheckoutPaymentInformation = CheckoutPaymentInformation::where('user_id', '=', $u_userid)->orderBy('id', 'desc')->first();	

				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['payment_status'=>true]);
				
				$UserRegistration = UserRegistration::leftjoin('states','users.state','=','states.id')
				->leftjoin('countries','users.country','=','countries.id')
				->select('users.*', 'states.name as sname', 'countries.name as cname')			
				->where('users.user_id','=',$u_userid)
				->first();
				
				
				$plan_price = $UserRegistration->temp_account_type;
				
				if($plan_price == '$25,000'){
					$ontra_account_type='OUP EVAL25K';
					$acc_def = '$25,000 Evaluation';
					$d_account_value	= '25000';
					$MABalance = '23500';
					$AL_MA_Balance = '25000';
					$AL_Threshold = '1500';
					$RMS_buy = '3';
					$RMS_sell = '3';
					$RMS_loss = '2500';
					$RMS_max = '9';
					$daily_loss = '500';
					$max_drawdown = '1500';
					$profit_target = '1500';
				}elseif($plan_price == '$50,000'){
					$ontra_account_type='OUP EVAL50K';
					$acc_def = '$50,000 Evaluation';
					$d_account_value	= '50000';
					$MABalance = '47500';
					$AL_MA_Balance = '50000';
					$AL_Threshold = '2500';
					$RMS_buy = '6';
					$RMS_sell = '6';
					$RMS_loss = '1250';
					$RMS_max = '18';
					$daily_loss = '2500';
					$max_drawdown = '2500';
					$profit_target = '3000';
				}elseif($plan_price == '$100,000'){
					$ontra_account_type='OUP EVAL100K';
					$acc_def = '$100,000 Evaluation';
					$d_account_value	= '100000';
					$MABalance = '96500';
					$AL_MA_Balance = '100000';
					$AL_Threshold = '3500';
					$RMS_buy = '12';
					$RMS_sell = '12';
					$RMS_loss = '2500';
					$RMS_max = '36';
					$daily_loss = '2500';
					$max_drawdown = '3500';
					$profit_target = '6000';
				}elseif($plan_price == '$150,000'){
					$ontra_account_type='OUP EVAL150K';
					$acc_def = '$150,000 Evaluation';
					$d_account_value	= '150000';
					$MABalance = '145000';
					$AL_MA_Balance = '150000';
					$AL_Threshold = '5000';
					$RMS_buy = '15';
					$RMS_sell = '15';
					$RMS_loss = '4000';
					$RMS_max = '45';
					$daily_loss = '3500';
					$max_drawdown = '5000';
					$profit_target = '9000';
				}elseif($plan_price == '$250,000'){
					$ontra_account_type='OUP EVAL250K';
					$acc_def = '$250,000 Evaluation';
					$d_account_value	= '250000';
					$MABalance = '244500';
					$AL_MA_Balance = '250000';
					$AL_Threshold = '5500';
					$RMS_buy = '25';
					$RMS_sell = '25';
					$RMS_loss = '5000';
					$RMS_max = '75';
					$daily_loss = '4500';
					$max_drawdown = '5500';
					$profit_target = '15000';
				}
				$last_account_type = $UserRegistration->account_type;
				$last_ontra_account_type = $UserRegistration->account_type_from_ontra;
				$last_demo_account_id = $UserRegistration->ontra_demo_account_id;
				$add_ontra_account_type_sequence = '';
				$remove_ontra_account_type_sequence = '';
				
				if($last_account_type == 'trial'){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						
						$add_ontra_account_type_sequence = 18;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						$remove_ontra_account_type_sequence = 11;
					}	
					
					
				}elseif($last_account_type == ''){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 24;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 25;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 26;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 27;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 28;
						
					}	
					
					
				}elseif($last_account_type == 'demo'){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 18;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						
					}

					if($ontra_account_type == $last_ontra_account_type){
						$remove_ontra_account_type_sequence = '';
					}else{
						if($last_ontra_account_type == 'OUP EVAL25K'){
							$remove_ontra_account_type_sequence = 18;
							
						}elseif($last_ontra_account_type == 'OUP EVAL50K'){
							$remove_ontra_account_type_sequence = 14;
							
						}elseif($last_ontra_account_type == 'OUP EVAL100K'){
							$remove_ontra_account_type_sequence = 19;
							
						}elseif($last_ontra_account_type == 'OUP EVAL150K'){
							$remove_ontra_account_type_sequence = 21;
							
						}elseif($last_ontra_account_type == 'OUP EVAL250K'){
							$remove_ontra_account_type_sequence = 22;
							
						}
					}
					
				}
				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['ttmp'=>$add_ontra_account_type_sequence]);
				
				
				$ontra_first_name=$ontra_last_name=$ontra_email=$ontra_contact_no=$ontra_address=$ontra_city=$ontra_state=$ontra_country=$ontra_billing_zip=$ontra_demo_password = '';

				$ontraCNT = DB::table('countries')->where('name', '=', trim($UserRegistration['cname']))->first();
				
				$ontra_contact_id = trim($UserRegistration['ontra_contact_id']);
				$ontra_first_name = trim($UserRegistration['first_name']);
				$ontra_last_name = trim($UserRegistration['last_name']);
				$ontra_email = trim($UserRegistration['email']);
				$ontra_contact_no = trim($UserRegistration['contact_no']);
				$ontra_address = trim($UserRegistration['address']);
				$ontra_city = trim($UserRegistration['city']);
				$ontra_state = trim($UserRegistration['sname']);
				$ontra_country = trim($ontraCNT->ontra_name);
				$ontra_billing_zip = trim($UserRegistration['zip']);
				$ontra_demo_password = trim($UserRegistration['ontra_demo_password']);


				$contact = '';									
				$data1 = '<search>
					<equation>
						<field>E-mail</field>
						<op>e</op>
						<value>'.$UserRegistration->email.'</value>
					</equation>
				</search>';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.ontraport.com/cdata.php");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,"appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&reqType=Search&return_id=1&data=".$data1);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				/*$auth = curl_exec($ch);
				$server_output = simplexml_load_string($auth);
				$contact = $server_output->contact->attributes()->id;*/
				$auth1 = curl_exec($ch);
				$xml1 = new \SimpleXMLElement($auth1);
				$re = json_decode(json_encode((array)$xml1), TRUE);
				
				//$contact = $re['contact']['@attributes']['id'];
                if (empty($re)) {
                    $contact = '';
                 }else{
                    $contact = $re['contact']['@attributes']['id'];
                 }
				
				date_default_timezone_set("America/Chicago");
				$dateValue = date('Y-m-d');
				$time=strtotime($dateValue);
				$day = date("d",$time);
				$month=date("m",$time);
				$year=date("y",$time);
				$random =  (mt_rand(1000,9999));

				date_default_timezone_set("America/Chicago");
                $curr_date = date('Y-m-d');
                $daystosum = '30';
                $ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));

				$NewDemoAccountId = trim($UserRegistration['first_name']).trim($UserRegistration['last_name']).'OUP'.$month.$day.$random;
				
				$post_data = '';
				
				if($ontra_contact_id != '')
				{
					$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
					$post_data.="<contact id='$ontra_contact_id'>";
					
				}else{

					if($contact != ''){

						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
						$post_data.="<contact id='$contact'>";
						
					}else{
						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
						$post_data.='<contact>';
					}
					//$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
					//$post_data.='<contact>';
				}
				
				// $post_data.='<Group_Tag name="Sequences and Tags">';
				// if(!empty($remove_ontra_account_type_sequence))
				// {
				// 	$post_data.='<field name="Sequences" action="remove">'.trim($remove_ontra_account_type_sequence).'</field>';
				// }		
				// $post_data.='<field name="Sequences">'.trim($add_ontra_account_type_sequence).'</field>';
				// $post_data.='</Group_Tag>';
				
				$post_data.='<Group_Tag name="Contact Information">			
				<field name="First Name">'.trim($ontra_first_name).'</field>
				<field name="Last Name">'.trim($ontra_last_name).'</field>
				<field name="Email">'.trim($ontra_email).'</field>
				<field name="Office Phone">'.trim($ontra_contact_no).'</field>
				<field name="Address">'.trim($ontra_address).'</field>
				<field name="City">'.trim($ontra_city).'</field>
				<field name="State">'.trim($ontra_state).'</field>
				<field name="Country">'.trim($ontra_country).'</field>
				<field name="Zip Code">'.trim($ontra_billing_zip).'</field>
				<field name="Account Type">'.trim($ontra_account_type).'</field>
				<field name="Account Status">Enabled</field>
				<field name="Account Definition">'.trim($acc_def).'</field>
				</Group_Tag>
				<Group_Tag name="ACCOUNT SETTINGS">			
				<field name="IB Id">OneUpTrader</field>
				<field name="Account Value">'.$d_account_value.'</field>
                <field name="Min Account Balance">'.$MABalance.'</field>
                <field name="FCM ID">OneUpTrader</field>
                <field name="Auto Liquidate Max Min Account Balance">'.$AL_MA_Balance.'</field>
                <field name="Auto Liquidate Threshold">'.$AL_Threshold.'</field>
                <field name="Days">30</field>
                <field name="RMS Buy Limit">'.$RMS_buy.'</field>
                <field name="RMS Sell Limit">'.$RMS_sell.'</field>
                <field name="RMS Loss Limit">'.$RMS_loss.'</field>
                <field name="RMS Max Order Qty">'.$RMS_max.'</field>
                <field name="Risk Algorithm">Limited Trailing Minimum Account Balance</field>
                <field name="Commission Fill Rate">2.5</field>
                <field name="Daily Loss Limit">'.$daily_loss.'</field>
                <field name="Max Drawdown">'.$max_drawdown.'</field>
                <field name="Profit Target">'.$profit_target.'</field>
                <field name="Target Days">15</field>	
				</Group_Tag>';
				
				if(!empty($ontra_demo_password)){
					
					$post_data.='<Group_Tag name="Demo Account Information">';
					if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					}

					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}
					$post_data.='</Group_Tag>';
				}else{
					$post_data.='<Group_Tag name="Demo Account Information">';

					if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					}

					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}

					$ontra_passwd_characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';
					$OntraPasswd = '';
					$max = strlen($ontra_passwd_characters) - 1;
					
					for ($i = 0; $i < 7; $i++) {
						$OntraPasswd.= $ontra_passwd_characters[mt_rand(0, $max)];
					}
					$ontra_demo_password = $OntraPasswd;
					$post_data.='<field name="Password">'.$OntraPasswd.'</field>';
					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					$post_data.='</Group_Tag>';
				}
				
				$post_data.='</contact>';
				
				
				$curl = curl_init('https://api.ontraport.com/cdata.php');
				curl_setopt( $curl, CURLOPT_POST, true );			
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data);			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
				$auth = curl_exec($curl);			
				$info = curl_getinfo($curl);			
				//print_r($info);
				// echo"<br><br>";			
				//print_r($auth);			
				$res=json_decode($auth);			
			//echo"<pre>";			
				//print_r($res);						
				
				$xml = new \SimpleXMLElement($auth);			
				
				
				$array_1 = json_decode(json_encode((array)$xml), TRUE);

					$ontra_contact_id='';
					$ontra_created_date='';
					$ontra_dlm='';
					$ontra_score='';
					$ontra_purl='';
					$ontra_bulk_mail='';
					$ontra_first_name='';
					$ontra_last_name='';
					$ontra_email='';
					$ontra_title='';
					$ontra_company='';
					$oontra_account_type='';
					$ontra_account_status='';
					$ontra_def='';
					$ontra_address='';
					$ontra_address2='';
					$ontra_city='';
					$oontra_state='';
					$ontra_zip='';
					$oontra_country='';
					$ontra_fax='';
					$ontra_sms_number='';
					$ontra_offc_phone='';
					$ontra_birthday='';
					$ontra_website='';
					$ontra_spent='';
					$ontra_date_modified='';
					$ontra_ip_address='';
					$ontra_last_activity='';
					$ontra_last_note='';
					$ontra_is_agree='';
					$ontra_paypal_address='';
					$ontra_no_of_sales='';
					$ontra_last_total_invoice='';
					$ontra_last_invoice_no='';
					$ontra_last_charge='';
					$ontra_last_total_invoice2='';
					$ontra_total_amount_unpaid='';
					$ontra_card_type='';
					$ontra_card_number='';
					$ontra_card_expiry_month='';
					$ontra_last_cc_status='';
					$ontra_card_expiry_year='';
					$ontra_card_expiry_date='';
					$ontra_date_added='';
					$ontra_trading_experience='';
					$ontra_trading_strategy='';
					$ontra_traded_live_before='';
					$ontra_still_trading_live='';
					$ontra_accounts_traded_live='';
					$ontra_avg_trades_per_day='';
					$ontra_time_in_trade='';
					$ontra_5_day_statement='';
					$ontra_user_ip='';
					$ontra_about_trader='';
					$ontra_live_user_id='';
					$ontra_live_account_id='';
					$ontra_password_live='';
					$ontra_live_activation_date='';
					$ontra_live_expiration_date='';
					$ontra_live_account_balance='';
					$ontra_live_termination='';
					$ontra_status='';
					$ontra_live_trading_status='';
					$ontra_termination_reason='';
					$ontra_demo_user_id='';
					$ontra_demo_account_id='';
					$oontra_demo_password='';
					$ontra_activation_date='';
					$ontra_expiration_date='';
					$ontra_termination_date='';
					$ontra_questionnaire='';
					$ontra_contest_start='';
					$ontra_contest_end='';
					$ontra_contest_confirmed='';
					$ontra_trading_status='';
					$ontra_ending_account_balance='';
					$ontra_demo_results='';
					$ontra_demo_fail_reasons='';
					$ontra_products_traded='';
					$ontra_trading_platform='';
					$ontra_professional_background='';
					$ontra_trading_style='';
					$ontra_why_trading='';
					$ontra_daily_preparation='';
					$ontra_short_term_goals='';
					$ontra_long_term_goals='';
					$ontra_strengths='';
					$ontra_weaknesses='';
					$ontra_last_inbound_sms='';
					$ontra_iB_id='';
					$ontra_account_value='';
					$ontra_min_account_balance='';
					$ontra_fcm_id='';
					$ontra_rms_buy_limit='';
					$ontra_rms_sell_limit='';
					$ontra_rms_loss_limit='';
					$ontra_rms_max_order='';
					$ontra_commision_fill_rate='';
					$ontra_days='';
					$ontra_send_to_rithmic='';
					$ontra_update_date_time='';
					$ontra_daily_loss_limit='';
					$ontra_max_down='';
					$ontra_profit_target='';
					$ontra_target_days='';
					
					if(!is_array($array_1['contact']['@attributes']['id'])) {
						$ontra_contact_id = $array_1['contact']['@attributes']['id'];
						

						//add remove sequene code here 
                            if(!empty($remove_ontra_account_type_sequence))
                            {
                               
                                $ch = curl_init();

                                curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/sequence');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                                curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&remove_list=".$remove_ontra_account_type_sequence."&ids=".$ontra_contact_id."");

                                $headers = array();
                                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                                $headers[] = 'Accept: application/json';
                                $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                                $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                                $result = curl_exec($ch);
                                curl_close($ch);

                               
                            }		


                            //new sequence add code place here

                            $ch = curl_init();

                            curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/subscribe');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                            curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&add_list=".$add_ontra_account_type_sequence."&ids=".$ontra_contact_id."&sub_type=Sequence");

                            $headers = array();
                            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                            $headers[] = 'Accept: application/json';
                            $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                            $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                            $result = curl_exec($ch);
                            // if (curl_errno($ch)) {
                            // echo 'Error:' . curl_error($ch);
                            // }
                            curl_close($ch);
					}
					

					Log::info('ConPost - Data received from Ontraport: ' . print_r($array_1, 1));
					$dataToSaveInDatabase = [];
                
					try {
						if (sizeof($array_1) > 0) {
							$dataToSaveInDatabase = $this->parseOntraportDataToDatabase($array_1);
						}
					} catch (\Throwable $th) {
						//throw $th;

						Log::info('Error fill user ontraport data');
						Log::info('');
						Log::info('');
						
						Log::info($th->getMessage());
						Log::info($th->getTraceAsString());
						
						// todo - redirect to homepage - done by miguel alfaiate
						die("Please contact administrator");
					}
					
					Log::info('ConPost - Save data received from Ontraport: ' . print_r($array_1, 1));

					date_default_timezone_set("America/Chicago");
					$curr_time = date("h:i:s");
					DB::table('users')
					->where('user_id', $u_userid)
					->update($dataToSaveInDatabase);
					
					 
				

					$tranID = $CheckoutPaymentInformation['TransactionID'];
					$tranDate = $CheckoutPaymentInformation['created_at'];
					$tranAmount = $CheckoutPaymentInformation['total_amount'];
					$emailTB = DB::table('tb_email')->where('id','=', 3)->first();
					$to=$UserRegistration['email'];
	                $subject='Payment Successful';
	                $message='<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
								<body><div style="font-size:12px;width:100%;height:100%">
								<table width="800" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#efefef;width:800px;max-width:800px;outline:rgb(239,239,239) solid 1px;margin:0px auto"><tbody><tr><td valign="top">
								            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ecebeb;margin:0px auto"><tbody><tr><td align="center" valign="top">
								                  
								                  <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="padding-left:20px;padding-right:20px;background-color:#ecebeb;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                        
								                        <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td valign="top" height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                          </tr><tr><td valign="middle">
								                              <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto"><tbody><tr><td align="center" valign="top" width="150" style="width:150px">
								                                    <a href="#0.2_" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0"><img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170212021144_oneup.png" width="150" style="max-width:150px;display:block!important;width:150px;height:auto;" alt="Logo" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                </tr><tr><td valign="top">
								                                  </td>
								                                </tr></tbody></table><table border="0" align="right" cellpadding="0" cellspacing="0" width="auto"><tbody><tr><td valign="middle" align="center">
								                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="height:100%;margin:0px auto" width="auto"><tbody><tr><td style="font-size:13px;color:#a3a2a2;font-weight:300;text-align:center;font-family:Roboto,Arial,Helvetica,sans-serif;word-break:break-word;line-height:21px" align="center"><br style="font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"></td>
								                                      </tr></tbody></table></td>
								                                </tr></tbody></table></td>
								                          </tr><tr><td valign="top" height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top" height="20" width="20" style="width:20px;height:20px;line-height:20px;font-size:20px">
								                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display:block!important;max-height:20px;max-width:20px"></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td valign="top">
								                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0px auto"><tbody><tr><td valign="top" width="auto" align="center">
								                              
								                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin:0px auto"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border:1px solid rgb(236,236,237);font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;text-align:center;color:#a3a2a2;font-weight:300;padding-left:18px;padding-right:18px;word-break:break-word;line-height:26px"><span style="color:#a3a2a2;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"> <a href="#0.2_" style="color:#a3a2a2;text-decoration:none!important;border-style:none;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color:#333333;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"> PAYMENT</font></span> <span style="color:#dd6b82;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">CONFIRMATION</font></span></font></a></font></span></td>
								                                </tr></tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="27" style="height:27px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                    </tr><tr><td valign="top">
								                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0px auto"><tbody><tr><td valign="top" width="100%">
								                              
								                              <table width="270" border="0" cellspacing="0" cellpadding="0" align="left" style="width:270px"><tbody><tr><td valign="top">
								                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left"><tbody><tr><td valign="top" align="left" width="200" style="width:200px">
								                                          <a href="#0.2_" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0">
								                                            <img src="https://app.oneuptrader.net/images/paid.png" width="200" alt="image8" style="max-width:200px;display:block!important;width:200px;height:auto;margin-top: 30px;margin-left: 20px;" border="0" hspace="0" vspace="0" height="auto"></a>
								                                        </td>
								                                      </tr></tbody></table></td>
								                                </tr><tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                </tr></tbody></table><table width="1" border="0" cellpadding="0" cellspacing="0" align="left" style="font-size:0px;line-height:0;border-collapse:collapse;width:1px"><tbody><tr><td width="0" height="2" style="border-collapse:collapse;width:0px;height:2px;line-height:2px;font-size:2px">
								                                    <p style="padding-left:20px"> </p>
								                                  </td>
								                                </tr></tbody></table><table width="270" border="0" cellspacing="0" cellpadding="0" align="right" style="width:270px"><tbody><tr><td valign="top">
								                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left"><tbody><tr><td style="font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#555555;font-weight:300;text-align:left;word-break:break-word;line-height:26px" align="left"><span style="line-height:26px;font-size:18px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">'.$emailTB->head.'</font></span></td>
								                                      </tr>
								                                      <tr><td style="font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#555555;font-weight:300;text-align:left;word-break:break-word;line-height:26px" align="left"><span style="line-height:26px;font-size:18px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">You are All Set.</font></span></td>
								                                      </tr>
								                                      <tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr>
								                                      <tr>
								                                      <td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left">
								                                      <span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">'.$emailTB->content.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Below are your transaction details 
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; -&nbsp;&nbsp;'.$tranID.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;'.$tranDate.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction Amount&nbsp;&nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;'.$tranAmount.'<br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">
								                                      </span>
								                                      </td>
								                                      </tr><tr><td height="29" style="height:29px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr>
								                                      <tr><td valign="top" width="auto">
								                                          
								                                          <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto">
								                                          <tbody>
								                                          <tr>
								                                            <td valign="top">
								                                                <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto">
								                                                  <tbody>
								                                                    
								                                                  <tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                                                  </tr></tbody></table></td>
								                                            </tr></tbody></table></td>
								                                      </tr><tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr></tbody></table></td>
								                                </tr>
								                                </tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr><tr><td>
								                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="margin:0px auto"><tbody><tr><td height="5" style="height:5px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                          </tr><tr><td style="border-bottom:1px solid #c7c7c7"></td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ecebea;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="4" style="height:4px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr><tr><td valign="top" align="center">
								                        
								                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0px auto"><tbody><tr><td valign="top" align="center">
								                              <table border="0" align="center" cellpadding="0" cellspacing="0" style="table-layout:fixed;margin:0px auto" width="auto"><tbody><tr>
								                                  
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://www.youtube.com/c/Oneuptrader" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-youtube-color.png" width="30" alt="icon-youtube" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://www.facebook.com/OneUpTrader/" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-facebook-color.png" width="30" alt="icon-facebook" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://twitter.com/OneUpTrader" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-twitter-color.png" width="30" alt="icon-twitter" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  
								                                  
								                                  
								                                </tr></tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#dd6b82" bgcolor="#dd6b82">
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="min-width:600px;background-color:#dd6b82;padding-left:20px;padding-right:20px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr><tr><td valign="top" style="font-size:13px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#ffffff;font-weight:300;text-align:left;word-break:break-word;line-height:21px" align="left"><div style="text-align:left;font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height:21px;font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">'.date('Y').' © OneUp Trader. All Rights Reserved. </font></span></font></div></td>
								                      
								                    </tr><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody></table></div>
								</body></html>';
	                $headers = "MIME-Version: 1.0" . "\r\n";
	                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";        
	                $headers .= 'From: OneUp Trader<'.$emailTB->email.'>' . "\r\n";
	                $from = $emailTB->email;
	             
	                //mail($to,$subject,$message,$headers);
	

	                $url = 'https://api.sendgrid.com/';
	                 $user = env('MAIL_USERNAME');
 					 $pass = env('MAIL_PASSWORD');
	                $params = array(
	                'api_user'  => $user,
	                'api_key'   => $pass,
	                'to'        => $to,
	                'subject'   => $subject,
	                'html'      => $message,
	                'from'      => $from,
					'fromname'  => "OneUp Trader",

	                 );
	                $request =  $url.'api/mail.send.json';
	                $session = curl_init($request);
					curl_setopt ($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $pass));
	                curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
	                curl_setopt($session, CURLOPT_HEADER, false);
	                curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
	                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	                $response = curl_exec($session);
	                curl_close($session);	
					
					if(($last_ontra_account_type == 'OUP TRIAL14DAY') || ($last_ontra_account_type == '')){


						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type_from_ontra' => trim($ontra_account_type),'account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}else{
						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}	


					if($last_account_type == ''){

						$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}	

						//Start post CSV for NEW CONTACT USER/ ACCOUNT
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'],
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'demo_password' => trim($csvUser['ontra_demo_password']),
				                    'demo_termination_date' => '',
				                    'days' => trim($csvUser['ontra_days']),
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'country' => trim($con['ontra_name']),
				                    'account_status' => trim($csvUser['ontra_account_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'login_expiration' => '',
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Account_type' => trim($csvUser['ontra_account_type']),
				                    'Account_Threshold' => $Account_Threshold,
				                );
				             
				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/new_contact.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
				        //End post CSV for NEW CONTACT USER/ ACCOUNT

				        //Start post CSV for NEW CONTACT PARAMETER
				           	
				            date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
							$run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
							
							$csv_act_data = [                    
				                'IB_id' => $csvUser['ontra_iB_id'],
				                'User_ID' => $csvUser['ontra_demo_user_id'],
				                'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                'first_name' => trim($csvUser['first_name']),
				                'last_name' => trim($csvUser['last_name']),
				                'email' => trim($csvUser['email']),
				                'demo_password' => trim($csvUser['ontra_demo_password']),
				                'days' => trim($csvUser['ontra_days']),
				                'trading_status' => trim($csvUser['ontra_trading_status']),
				                'address' => trim($csvUser['address']),
				                'city' => trim($csvUser['city']),
				                'state' => trim($stName),
				                'zip' => trim($csvUser['zip']),
				                'country' => trim($con['ontra_name']),
				                'account_status' => trim($csvUser['ontra_account_status']),
				                'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                'account_value' => $account_value,
				                'RMS_buy_limit' => $RMS_buy_limit,
				                'RMS_sell_limit' => $RMS_sell_limit,
				                'RMS_loss_limit' => $RMS_loss_limit,
				                'RMS_max_order_qty' => $RMS_max_order_qty,
				                'min_account_balance' => $min_account_balance,
				                'Commission_fill_rate' => $Commission_fill_rate, 
				                'curr_time' => $curr_time,
				                'run_time' => $run_time          
							];
							
							Log::info('ConPost - Inserting data in csv_act: ' . print_r($csv_act_data, 1));

				            DB::table('csv_act')->insert($csv_act_data);

				          /*  $data1 = array(
				                    'IB_id' => $csvUser['ontra_iB_id'],
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'demo_password' => trim($csvUser['ontra_demo_password']),
				                    'days' => trim($csvUser['ontra_days']),
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'country' => trim($con['ontra_name']),
				                    'account_status' => trim($csvUser['ontra_account_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                );
				             
				            $ch1 = curl_init();
				            $curlConfig1 = array(
				                CURLOPT_URL            => "http://oneuptrader.net/ontraport/MES_new/new_con.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data1
				            );
				            curl_setopt_array($ch1, $curlConfig1);
				            $result1 = curl_exec($ch1);
				            curl_close($ch1);*/
				        //End post CSV for NEW CONTACT PARAMETER   

				            // $user_id = $csvUser['user_id'];
				            // $u_name = $csvUser['name'];
				            // $email = $csvUser['email'];
				            // $tos = 'support@oneuptrader.com';
				            // //$tos = 'sohan.constacloud@gmail.com';
			                // $subjects = "OneUp Trader New User Signup";
			                // $messages='<html>
			                //             <head>
			                //               <title>OneUp Trader New User Signup</title>
			                //             </head>
			                //             <body>
			                //               <table>
			                //              <tr><td>User ID    :</td><td>'.$user_id.'</td></tr>
			                //              <tr><td>User Name  :</td><td>'.$u_name.'</td></tr>
			                //              <tr><td>Email      :</td><td>'.$email.'</td></tr>
			                //              <tr><td>Created at :</td><td>'.date('Y-m-d H:i:s').'</td></tr>
			                //               </table>
			                //             </body>
			                //             </html>';
			                // $urls = 'https://api.sendgrid.com/';
			                // $users = env('MAIL_USERNAME');
 							// $passs = env('MAIL_PASSWORD');
			                // $paramss = array(
			                // 'api_user'  => $users,
			                // 'api_key'   => $passs,
			                // 'to'        => $tos,
			                // 'subject'   => $subjects,
			                // 'html'      => $messages,
			                // 'from'      => 'support@oneuptrader.com',
			                // 'fromname'  => "OneUp Trader",

			                //  );
			                // $requests =  $urls.'api/mail.send.json';
			                // $sessions = curl_init($requests);
							// curl_setopt ($sessions, CURLOPT_POST, true);
							//curl_setopt($sessions, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $passs));
			                // curl_setopt ($sessions, CURLOPT_POSTFIELDS, $paramss);
			                // curl_setopt($sessions, CURLOPT_HEADER, false);
			                // curl_setopt($sessions, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
			                // curl_setopt($sessions, CURLOPT_RETURNTRANSFER, true);
			                // $responses = curl_exec($sessions);
			                // curl_close($sessions);
					}
					else{

						$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}

						//Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 1
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'], 
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'days' => trim($csvUser['ontra_days']),
				                    'country' => trim($con['ontra_name']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Last_Account_ID' => trim($last_demo_account_id),
				                    'Account_Threshold' => $Account_Threshold,
				                );
							 
							Log::info('ConPost - Send data to ud_1.php: ' . print_r($data, 1));

				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/ud_1.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
						//End post CSV for UPGRADE / DOWNGRADE BALANCE Part 1
						
						Log::info('ConPost - Result after send to ud_1.php: ' . print_r($result, 1));

				        //Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
				            date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
							$run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
							
							$csv_run_data = [                    
								'IB_id' => $csvUser['ontra_iB_id'],
			                    'User_ID' => $csvUser['ontra_demo_user_id'],
			                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
			                    'trading_status' => trim($csvUser['ontra_trading_status']),
			                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
			                    'account_value' => $account_value,
			                   	'RMS_buy_limit' => $RMS_buy_limit,
			                    'RMS_sell_limit' => $RMS_sell_limit,
			                    'RMS_loss_limit' => $RMS_loss_limit,
			                    'RMS_max_order_qty' => $RMS_max_order_qty,
			                    'min_account_balance' => $min_account_balance,
			                    'Commission_fill_rate' => $Commission_fill_rate,
			                    'days' => trim($csvUser['ontra_days']),
			                    'state' => trim($stName),
			                    'Last_Account_ID' => trim($last_demo_account_id),
			                    'curr_time' => $curr_time,
			                    'run_time' => $run_time           
							];

							Log::info('ConPost - Inserting data into csv_run: ' . print_r($csv_run_data, 1));

				           	DB::table('csv_run')->insert();
				        
				        //End post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
					} 
					$ontra_acc = UserRegistration::where('user_id','=',$u_userid)->first();
					$onValue = $ontra_acc['temp_account_type'];
					if($onValue == '$25,000'){
						$account_value = 25000;
	                    $profit_target = 1500;	
	                    $target_days = 15;
					}elseif($onValue == '$50,000'){
						$account_value = 50000;
	                    $profit_target = 3000;	
	                    $target_days = 15;
					}elseif($onValue == '$100,000'){
						$account_value = 100000;
	                    $profit_target = 6000;	
	                    $target_days = 15;
					}elseif($onValue == '$150,000'){
						$account_value = 150000;
	                    $profit_target = 9000;	
	                    $target_days = 15;
					}elseif($onValue == '$250,000'){
						$account_value = 250000;
	                    $profit_target = 15000;	
	                    $target_days = 15;
					}
					date_default_timezone_set("America/Chicago");
					$curr_date1 = date('Y-m-d');
	                $daystosum1 = '30';
	                $ex_date1 = date('Y-m-d', strtotime($curr_date1.' + '.$daystosum1.' days'));

					$ontra_account_data = [					
	                    'user_id' => $u_userid,		 			
						'account_id' => $NewDemoAccountId,
						'account_type' => $ontra_account_type,
						'acc_def' => $acc_def,
						'ontra_account_value' => $account_value,
	                    'ontra_profit_target' => $profit_target,
	                    'ontra_target_days' => $target_days,
	                    'ontra_rms_buy_limit' => $ontra_acc['ontra_rms_buy_limit'],
	                    'ontra_daily_loss_limit' => $ontra_acc['ontra_daily_loss_limit'],
	                    'ontra_max_down' => $ontra_acc['ontra_max_down'],
	                    'ontra_activation_date' => $curr_date1,
	                    'ontra_expiration_date' => $ex_date1,						
						'updated_at' => date('Y-m-d H:i:s')				
					];

					Log::info('ConPost - Inserting data into ontra_account: ' . print_r($ontra_account_data, 1));

						DB::table('ontra_account')->insert($ontra_account_data); 

				DB::table('unassign')->where('user_id', $u_userid)->update(['status'=>true]);

				Log::info('User Account completed successfully ');
				
      		return redirect('admin/confirm')->with('message','User Account completed successfully')->with('status','success');	
	}
	
	public function myTest(){

			//$uid = '3987834201905261';
			$ontra_contact_id = '8204AA';
			$u_userid = '5485761793543060AA';
			$post_data = '';
				
				
				$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
				$post_data.="<contact id='$ontra_contact_id'>";
				
				
				
				$post_data.='<Group_Tag name="Contact Information">			
				<field name="Last Name">Thomas1</field>
				</Group_Tag>';
				

				$post_data.='</contact>';
				
				
				$curl = curl_init('https://api.ontraport.com/cdata.php');
				curl_setopt( $curl, CURLOPT_POST, true );			
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data);			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
				$auth = curl_exec($curl);			
				$info = curl_getinfo($curl);			
				//print_r($info);
				// echo"<br><br>";			
				//print_r($auth);			
				$res=json_decode($auth);			
			//echo"<pre>";			
				//print_r($res);						
				
				$xml = new \SimpleXMLElement($auth);			
				
				
				$array_1 = json_decode(json_encode((array)$xml), TRUE);
				$ontra_contact_id='';
					$ontra_created_date='';
					$ontra_dlm='';
					$ontra_score='';
					$ontra_purl='';
					$ontra_bulk_mail='';
					$ontra_first_name='';
					$ontra_last_name='';
					$ontra_email='';
					$ontra_title='';
					$ontra_company='';
					$oontra_account_type='';
					$ontra_account_status='';
					$ontra_def='';
					$ontra_address='';
					$ontra_address2='';
					$ontra_city='';
					$oontra_state='';
					$ontra_zip='';
					$oontra_country='';
					$ontra_fax='';
					$ontra_sms_number='';
					$ontra_offc_phone='';
					$ontra_birthday='';
					$ontra_website='';
					$ontra_spent='';
					$ontra_date_modified='';
					$ontra_ip_address='';
					$ontra_last_activity='';
					$ontra_last_note='';
					$ontra_is_agree='';
					$ontra_paypal_address='';
					$ontra_no_of_sales='';
					$ontra_last_total_invoice='';
					$ontra_last_invoice_no='';
					$ontra_last_charge='';
					$ontra_last_total_invoice2='';
					$ontra_total_amount_unpaid='';
					$ontra_card_type='';
					$ontra_card_number='';
					$ontra_card_expiry_month='';
					$ontra_last_cc_status='';
					$ontra_card_expiry_year='';
					$ontra_card_expiry_date='';
					$ontra_date_added='';
					$ontra_trading_experience='';
					$ontra_trading_strategy='';
					$ontra_traded_live_before='';
					$ontra_still_trading_live='';
					$ontra_accounts_traded_live='';
					$ontra_avg_trades_per_day='';
					$ontra_time_in_trade='';
					$ontra_5_day_statement='';
					$ontra_user_ip='';
					$ontra_about_trader='';
					$ontra_live_user_id='';
					$ontra_live_account_id='';
					$ontra_password_live='';
					$ontra_live_activation_date='';
					$ontra_live_expiration_date='';
					$ontra_live_account_balance='';
					$ontra_live_termination='';
					$ontra_status='';
					$ontra_live_trading_status='';
					$ontra_termination_reason='';
					$ontra_demo_user_id='';
					$ontra_demo_account_id='';
					$oontra_demo_password='';
					$ontra_activation_date='';
					$ontra_expiration_date='';
					$ontra_termination_date='';
					$ontra_questionnaire='';
					$ontra_contest_start='';
					$ontra_contest_end='';
					$ontra_contest_confirmed='';
					$ontra_trading_status='';
					$ontra_ending_account_balance='';
					$ontra_demo_results='';
					$ontra_demo_fail_reasons='';
					$ontra_products_traded='';
					$ontra_trading_platform='';
					$ontra_professional_background='';
					$ontra_trading_style='';
					$ontra_why_trading='';
					$ontra_daily_preparation='';
					$ontra_short_term_goals='';
					$ontra_long_term_goals='';
					$ontra_strengths='';
					$ontra_weaknesses='';
					$ontra_last_inbound_sms='';
					$ontra_iB_id='';
					$ontra_account_value='';
					$ontra_min_account_balance='';
					$ontra_fcm_id='';
					$ontra_rms_buy_limit='';
					$ontra_rms_sell_limit='';
					$ontra_rms_loss_limit='';
					$ontra_rms_max_order='';
					$ontra_commision_fill_rate='';
					$ontra_days='';
					$ontra_send_to_rithmic='';
					$ontra_update_date_time='';
					$ontra_daily_loss_limit='';
					$ontra_max_down='';
					$ontra_profit_target='';
					$ontra_target_days='';
					
					if(!is_array($array_1['contact']['@attributes']['id'])) {
						$ontra_contact_id = $array_1['contact']['@attributes']['id'];
						
					}
					
					if(!is_array($array_1['contact']['@attributes']['date'])) {
						$ontra_created_date = date("Y-m-d H:i:s", $array_1['contact']['@attributes']['date']);
					}
					
					if(!is_array($array_1['contact']['@attributes']['dlm'])) {
						$ontra_dlm = date("Y-m-d H:i:s", $array_1['contact']['@attributes']['dlm']);
					}
					
					if(!is_array($array_1['contact']['@attributes']['score'])) {
						$ontra_score = $array_1['contact']['@attributes']['score'];
					}
					
					if(!is_array($array_1['contact']['@attributes']['purl'])) {
						$ontra_purl = $array_1['contact']['@attributes']['purl'];
					}
					
					if(!is_array($array_1['contact']['@attributes']['bulk_mail'])) {
						$ontra_bulk_mail = $array_1['contact']['@attributes']['bulk_mail'];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][1])) {
						$ontra_first_name = $array_1['contact']['Group_Tag'][0]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][2])) {
						$ontra_last_name = $array_1['contact']['Group_Tag'][0]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][3])) {
						$ontra_email=$array_1['contact']['Group_Tag'][0]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][4])) {
						$ontra_title = $array_1['contact']['Group_Tag'][0]['field'][4];
					}
					
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][5])) {
						$oontra_account_type = $array_1['contact']['Group_Tag'][0]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][6])) {
						$ontra_account_status = $array_1['contact']['Group_Tag'][0]['field'][6];

					}
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][7])) {
						$ontra_def = $array_1['contact']['Group_Tag'][0]['field'][7];

					}
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][8])) {
						$ontra_address = $array_1['contact']['Group_Tag'][0]['field'][8];

					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][9])) {
						$ontra_address2 = $array_1['contact']['Group_Tag'][0]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][10])) {
						$ontra_city = $array_1['contact']['Group_Tag'][0]['field'][10];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][11])) {
						$oontra_state = $array_1['contact']['Group_Tag'][0]['field'][11];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][12])) {
						$ontra_zip = $array_1['contact']['Group_Tag'][0]['field'][12];
					}
					
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][14])) {
						$ontra_fax = $array_1['contact']['Group_Tag'][0]['field'][14];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][15])) {
						$ontra_sms_number = $array_1['contact']['Group_Tag'][0]['field'][15];
					}
						
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][16])) {
						$ontra_birthday = $array_1['contact']['Group_Tag'][0]['field'][16];
					}	


					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][18])) {
						$ontra_company = $array_1['contact']['Group_Tag'][0]['field'][18];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][19])) {
						$ontra_offc_phone = $array_1['contact']['Group_Tag'][0]['field'][19];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][20])) {
						$ontra_website = $array_1['contact']['Group_Tag'][0]['field'][20];
					}
					
					
					date_default_timezone_set("America/Chicago");	
					
					DB::table('users')
					->where('user_id', $u_userid)
					->update([
					'ontra_contact_id' => $ontra_contact_id,
					'ontra_created_date' => $ontra_created_date,
					'ontra_dlm' => $ontra_dlm,
					'ontra_score' => $ontra_score,
					'ontra_purl' => $ontra_purl,
					'ontra_bulk_mail' => $ontra_bulk_mail,
					'ontra_first_name' => $ontra_first_name,
					'ontra_last_name' => $ontra_last_name,
					'ontra_email' => $ontra_email,
					'ontra_account_type' => $oontra_account_type,
					'ontra_company' => $ontra_company,
					'ontra_title' => $ontra_title,
					'ontra_account_status' => $ontra_account_status,
					'ontra_acc_def'   => $ontra_def,
					'ontra_address' => $ontra_address,
					'ontra_address2' => $ontra_address2,
					'ontra_city' => $ontra_city,
					'ontra_state' => $oontra_state,
					'ontra_zip' => $ontra_zip,
					'ontra_fax' => $ontra_fax,
					'ontra_sms_number' => $ontra_sms_number,
					'ontra_offc_phone' => $ontra_offc_phone,
					'ontra_birthday' => $ontra_birthday,
					'ontra_website' => $ontra_website
					]);

		
	}
	public function contact(){	
			$u_userid = '6834071207460305AAA';
			$CheckoutPaymentInformation = CheckoutPaymentInformation::where('user_id', '=', $u_userid)->orderBy('id', 'desc')->first();	

				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['payment_status'=>true]);
				
				$UserRegistration = UserRegistration::leftjoin('states','users.state','=','states.id')
				->leftjoin('countries','users.country','=','countries.id')
				->select('users.*', 'states.name as sname', 'countries.name as cname')			
				->where('users.user_id','=',$u_userid)
				->first();
				
				
				$plan_price = $UserRegistration->temp_account_type;
				
				if($plan_price == '$25,000'){
					$ontra_account_type='OUP EVAL25K';
					$acc_def = '$25,000 Evaluation';
					$d_account_value	= '25000';
					$MABalance = '23500';
					$AL_MA_Balance = '25000';
					$AL_Threshold = '1500';
					$RMS_buy = '3';
					$RMS_sell = '3';
					$RMS_loss = '2500';
					$RMS_max = '9';
					$daily_loss = '500';
					$max_drawdown = '1500';
					$profit_target = '1500';
				}elseif($plan_price == '$50,000'){
					$ontra_account_type='OUP EVAL50K';
					$acc_def = '$50,000 Evaluation';
					$d_account_value	= '50000';
					$MABalance = '47500';
					$AL_MA_Balance = '50000';
					$AL_Threshold = '2500';
					$RMS_buy = '6';
					$RMS_sell = '6';
					$RMS_loss = '1250';
					$RMS_max = '18';
					$daily_loss = '2500';
					$max_drawdown = '2500';
					$profit_target = '3000';
				}elseif($plan_price == '$100,000'){
					$ontra_account_type='OUP EVAL100K';
					$acc_def = '$100,000 Evaluation';
					$d_account_value	= '100000';
					$MABalance = '96500';
					$AL_MA_Balance = '100000';
					$AL_Threshold = '3500';
					$RMS_buy = '12';
					$RMS_sell = '12';
					$RMS_loss = '2500';
					$RMS_max = '36';
					$daily_loss = '2500';
					$max_drawdown = '3500';
					$profit_target = '6000';
				}elseif($plan_price == '$150,000'){
					$ontra_account_type='OUP EVAL150K';
					$acc_def = '$150,000 Evaluation';
					$d_account_value	= '150000';
					$MABalance = '145000';
					$AL_MA_Balance = '150000';
					$AL_Threshold = '5000';
					$RMS_buy = '15';
					$RMS_sell = '15';
					$RMS_loss = '4000';
					$RMS_max = '45';
					$daily_loss = '3500';
					$max_drawdown = '5000';
					$profit_target = '9000';
				}elseif($plan_price == '$250,000'){
					$ontra_account_type='OUP EVAL250K';
					$acc_def = '$250,000 Evaluation';
					$d_account_value	= '250000';
					$MABalance = '244500';
					$AL_MA_Balance = '250000';
					$AL_Threshold = '5500';
					$RMS_buy = '25';
					$RMS_sell = '25';
					$RMS_loss = '5000';
					$RMS_max = '75';
					$daily_loss = '4500';
					$max_drawdown = '5500';
					$profit_target = '15000';
				}
				$last_account_type = $UserRegistration->account_type;
				$last_ontra_account_type = $UserRegistration->account_type_from_ontra;
				$last_demo_account_id = $UserRegistration->ontra_demo_account_id;
				$add_ontra_account_type_sequence = '';
				$remove_ontra_account_type_sequence = '';
				
				if($last_account_type == 'trial'){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						
						$add_ontra_account_type_sequence = 18;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						$remove_ontra_account_type_sequence = 11;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						$remove_ontra_account_type_sequence = 11;
					}	
					
					
				}elseif($last_account_type == ''){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 24;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 25;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 26;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 27;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 28;
						
					}	
					
					
				}elseif($last_account_type == 'demo'){
					
					
					if($ontra_account_type == 'OUP EVAL25K'){
						$add_ontra_account_type_sequence = 18;
						
					}elseif($ontra_account_type == 'OUP EVAL50K'){
						$add_ontra_account_type_sequence = 14;
						
					}elseif($ontra_account_type == 'OUP EVAL100K'){
						$add_ontra_account_type_sequence = 19;
						
					}elseif($ontra_account_type == 'OUP EVAL150K'){
						$add_ontra_account_type_sequence = 21;
						
					}elseif($ontra_account_type == 'OUP EVAL250K'){
						$add_ontra_account_type_sequence = 22;
						
					}

					if($ontra_account_type == $last_ontra_account_type){
						$remove_ontra_account_type_sequence = '';
					}else{
						if($last_ontra_account_type == 'OUP EVAL25K'){
							$remove_ontra_account_type_sequence = 18;
							
						}elseif($last_ontra_account_type == 'OUP EVAL50K'){
							$remove_ontra_account_type_sequence = 14;
							
						}elseif($last_ontra_account_type == 'OUP EVAL100K'){
							$remove_ontra_account_type_sequence = 19;
							
						}elseif($last_ontra_account_type == 'OUP EVAL150K'){
							$remove_ontra_account_type_sequence = 21;
							
						}elseif($last_ontra_account_type == 'OUP EVAL250K'){
							$remove_ontra_account_type_sequence = 22;
							
						}
					}
					
				}
				DB::table('CheckoutPaymentInformation')->where('id', $CheckoutPaymentInformation['id'])->update(['ttmp'=>$add_ontra_account_type_sequence]);
				
				
				$ontra_first_name=$ontra_last_name=$ontra_email=$ontra_contact_no=$ontra_address=$ontra_city=$ontra_state=$ontra_country=$ontra_billing_zip=$ontra_demo_password = '';

				$ontraCNT = DB::table('countries')->where('name', '=', trim($UserRegistration['cname']))->first();
				
				$ontra_contact_id = trim($UserRegistration['ontra_contact_id']);
				$ontra_first_name = trim($UserRegistration['first_name']);
				$ontra_last_name = trim($UserRegistration['last_name']);
				$ontra_email = trim($UserRegistration['email']);
				$ontra_contact_no = trim($UserRegistration['contact_no']);
				$ontra_address = trim($UserRegistration['address']);
				$ontra_city = trim($UserRegistration['city']);
				$ontra_state = trim($UserRegistration['sname']);
				$ontra_country = trim($ontraCNT->ontra_name);
				$ontra_billing_zip = trim($UserRegistration['zip']);
				$ontra_demo_password = trim($UserRegistration['ontra_demo_password']);


				$contact = '';									
				$data1 = '<search>
					<equation>
						<field>E-mail</field>
						<op>e</op>
						<value>'.$UserRegistration->email.'</value>
					</equation>
				</search>';

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://api.ontraport.com/cdata.php");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,"appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&reqType=Search&return_id=1&data=".$data1);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				/*$auth = curl_exec($ch);
				$server_output = simplexml_load_string($auth);
				$contact = $server_output->contact->attributes()->id;*/
				$auth1 = curl_exec($ch);
				$xml1 = new \SimpleXMLElement($auth1);
				$re = json_decode(json_encode((array)$xml1), TRUE);
				
				//$contact = $re['contact']['@attributes']['id'];
                if (empty($re)) {
                    $contact = '';
                 }else{
                    $contact = $re['contact']['@attributes']['id'];
                 }
				
				date_default_timezone_set("America/Chicago");
				$dateValue = date('Y-m-d');
				$time=strtotime($dateValue);
				$day = date("d",$time);
				$month=date("m",$time);
				$year=date("y",$time);
				$random =  (mt_rand(1000,9999));
				date_default_timezone_set("America/Chicago");
                $curr_date = date('Y-m-d');
                $daystosum = '30';
                $ex_date = date('Y-m-d', strtotime($curr_date.' + '.$daystosum.' days'));

				$NewDemoAccountId = trim($UserRegistration['first_name']).trim($UserRegistration['last_name']).'OUP'.$month.$day.$random;
				
				$post_data = '';
				
				if($ontra_contact_id != '')
				{
					$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
					$post_data.="<contact id='$ontra_contact_id'>";
					
				}else{

					if($contact != ''){

						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=update&data=XML";			
						$post_data.="<contact id='$contact'>";
						
					}else{
						$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
						$post_data.='<contact>';
					}
					//$post_data.="appid=".decrypt(config('app.Hkey')->ont_apky)."&key=".decrypt(config('app.Hkey')->ont_ky)."&return_id=1&reqType=add&data=XML";			
					//$post_data.='<contact>';
				}
				
				// $post_data.='<Group_Tag name="Sequences and Tags">';
				// if(!empty($remove_ontra_account_type_sequence))
				// {
				// 	$post_data.='<field name="Sequences" action="remove">'.trim($remove_ontra_account_type_sequence).'</field>';
				// }		
				// $post_data.='<field name="Sequences">'.trim($add_ontra_account_type_sequence).'</field>';
				// $post_data.='</Group_Tag>';
				
				$post_data.='<Group_Tag name="Contact Information">			
				<field name="First Name">'.trim($ontra_first_name).'</field>
				<field name="Last Name">'.trim($ontra_last_name).'</field>
				<field name="Email">'.trim($ontra_email).'</field>
				<field name="Office Phone">'.trim($ontra_contact_no).'</field>
				<field name="Address">'.trim($ontra_address).'</field>
				<field name="City">'.trim($ontra_city).'</field>
				<field name="State">'.trim($ontra_state).'</field>
				<field name="Country">'.trim($ontra_country).'</field>
				<field name="Zip Code">'.trim($ontra_billing_zip).'</field>
				<field name="Account Type">'.trim($ontra_account_type).'</field>
				<field name="Account Status">Enabled</field>
				<field name="Account Definition">'.trim($acc_def).'</field>
				</Group_Tag>
				<Group_Tag name="ACCOUNT SETTINGS">			
				<field name="IB Id">OneUpTrader</field>
				<field name="Account Value">'.$d_account_value.'</field>
                <field name="Min Account Balance">'.$MABalance.'</field>
                <field name="FCM ID">OneUpTrader</field>
                <field name="Auto Liquidate Max Min Account Balance">'.$AL_MA_Balance.'</field>
                <field name="Auto Liquidate Threshold">'.$AL_Threshold.'</field>
                <field name="Days">30</field>
                <field name="RMS Buy Limit">'.$RMS_buy.'</field>
                <field name="RMS Sell Limit">'.$RMS_sell.'</field>
                <field name="RMS Loss Limit">'.$RMS_loss.'</field>
                <field name="RMS Max Order Qty">'.$RMS_max.'</field>
                <field name="Risk Algorithm">Limited Trailing Minimum Account Balance</field>
                <field name="Commission Fill Rate">2.5</field>
                <field name="Daily Loss Limit">'.$daily_loss.'</field>
                <field name="Max Drawdown">'.$max_drawdown.'</field>
                <field name="Profit Target">'.$profit_target.'</field>
                <field name="Target Days">15</field>	
				</Group_Tag>';
				
				if(!empty($ontra_demo_password)){
					
					$post_data.='<Group_Tag name="Demo Account Information">';
					//if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					//}

					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}else{
						$post_data.='<field name="Last Account Id">'.$NewDemoAccountId.'</field>';
					}
					$post_data.='</Group_Tag>';
				}else{
					$post_data.='<Group_Tag name="Demo Account Information">';

					//if($last_account_type != ''){
						$post_data.='<field name="Demo Account Id">'.$NewDemoAccountId.'</field>';
					//}

					if($last_demo_account_id != ''){
						$post_data.='<field name="Last Account Id">'.$last_demo_account_id.'</field>';
					}else{
						$post_data.='<field name="Last Account Id">'.$NewDemoAccountId.'</field>';
					}

					$ontra_passwd_characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';
					$OntraPasswd = '';
					$max = strlen($ontra_passwd_characters) - 1;
					
					for ($i = 0; $i < 7; $i++) {
						$OntraPasswd.= $ontra_passwd_characters[mt_rand(0, $max)];
					}
					$ontra_demo_password = $OntraPasswd;
					$post_data.='<field name="Password">'.$OntraPasswd.'</field>';
					$post_data.='<field name="Password">'.$ontra_demo_password.'</field>';
					$post_data.='<field name="Activation Date">'.$curr_date.'</field>';
					$post_data.='<field name="Expiration Date">'.$ex_date.'</field>';
					$post_data.='<field name="Trading Status">Enabled</field>';
					$post_data.='</Group_Tag>';
				}
				
				$post_data.='</contact>';
				
				
				$curl = curl_init('https://api.ontraport.com/cdata.php');
				curl_setopt( $curl, CURLOPT_POST, true );			
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_data);			
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);			
				$auth = curl_exec($curl);			
				$info = curl_getinfo($curl);			
				//print_r($info);
				// echo"<br><br>";			
				//print_r($auth);			
				$res=json_decode($auth);			
			//echo"<pre>";			
				//print_r($res);						
				
				$xml = new \SimpleXMLElement($auth);			
				
				
				$array_1 = json_decode(json_encode((array)$xml), TRUE);

					$ontra_contact_id='';
					$ontra_created_date='';
					$ontra_dlm='';
					$ontra_score='';
					$ontra_purl='';
					$ontra_bulk_mail='';
					$ontra_first_name='';
					$ontra_last_name='';
					$ontra_email='';
					$ontra_title='';
					$ontra_company='';
					$oontra_account_type='';
					$ontra_account_status='';
					$ontra_def='';
					$ontra_address='';
					$ontra_address2='';
					$ontra_city='';
					$oontra_state='';
					$ontra_zip='';
					$oontra_country='';
					$ontra_fax='';
					$ontra_sms_number='';
					$ontra_offc_phone='';
					$ontra_birthday='';
					$ontra_website='';
					$ontra_spent='';
					$ontra_date_modified='';
					$ontra_ip_address='';
					$ontra_last_activity='';
					$ontra_last_note='';
					$ontra_is_agree='';
					$ontra_paypal_address='';
					$ontra_no_of_sales='';
					$ontra_last_total_invoice='';
					$ontra_last_invoice_no='';
					$ontra_last_charge='';
					$ontra_last_total_invoice2='';
					$ontra_total_amount_unpaid='';
					$ontra_card_type='';
					$ontra_card_number='';
					$ontra_card_expiry_month='';
					$ontra_last_cc_status='';
					$ontra_card_expiry_year='';
					$ontra_card_expiry_date='';
					$ontra_date_added='';
					$ontra_trading_experience='';
					$ontra_trading_strategy='';
					$ontra_traded_live_before='';
					$ontra_still_trading_live='';
					$ontra_accounts_traded_live='';
					$ontra_avg_trades_per_day='';
					$ontra_time_in_trade='';
					$ontra_5_day_statement='';
					$ontra_user_ip='';
					$ontra_about_trader='';
					$ontra_live_user_id='';
					$ontra_live_account_id='';
					$ontra_password_live='';
					$ontra_live_activation_date='';
					$ontra_live_expiration_date='';
					$ontra_live_account_balance='';
					$ontra_live_termination='';
					$ontra_status='';
					$ontra_live_trading_status='';
					$ontra_termination_reason='';
					$ontra_demo_user_id='';
					$ontra_demo_account_id='';
					$oontra_demo_password='';
					$ontra_activation_date='';
					$ontra_expiration_date='';
					$ontra_termination_date='';
					$ontra_questionnaire='';
					$ontra_contest_start='';
					$ontra_contest_end='';
					$ontra_contest_confirmed='';
					$ontra_trading_status='';
					$ontra_ending_account_balance='';
					$ontra_demo_results='';
					$ontra_demo_fail_reasons='';
					$ontra_products_traded='';
					$ontra_trading_platform='';
					$ontra_professional_background='';
					$ontra_trading_style='';
					$ontra_why_trading='';
					$ontra_daily_preparation='';
					$ontra_short_term_goals='';
					$ontra_long_term_goals='';
					$ontra_strengths='';
					$ontra_weaknesses='';
					$ontra_last_inbound_sms='';
					$ontra_iB_id='';
					$ontra_account_value='';
					$ontra_min_account_balance='';
					$ontra_fcm_id='';
					$ontra_rms_buy_limit='';
					$ontra_rms_sell_limit='';
					$ontra_rms_loss_limit='';
					$ontra_rms_max_order='';
					$ontra_commision_fill_rate='';
					$ontra_days='';
					$ontra_send_to_rithmic='';
					$ontra_update_date_time='';
					$ontra_daily_loss_limit='';
					$ontra_max_down='';
					$ontra_profit_target='';
					$ontra_target_days='';
					
					if(!is_array($array_1['contact']['@attributes']['id'])) {
						$ontra_contact_id = $array_1['contact']['@attributes']['id'];
						

						//add remove sequene code here 
                            if(!empty($remove_ontra_account_type_sequence))
                            {
                               
                                $ch = curl_init();

                                curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/sequence');
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

                                curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&remove_list=".$remove_ontra_account_type_sequence."&ids=".$ontra_contact_id."");

                                $headers = array();
                                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                                $headers[] = 'Accept: application/json';
                                $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                                $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                                $result = curl_exec($ch);
                                curl_close($ch);

                               
                            }		


                            //new sequence add code place here

                            $ch = curl_init();

                            curl_setopt($ch, CURLOPT_URL, 'https://api.ontraport.com/1/objects/subscribe');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

                            curl_setopt($ch, CURLOPT_POSTFIELDS, "objectID=0&add_list=".$add_ontra_account_type_sequence."&ids=".$ontra_contact_id."&sub_type=Sequence");

                            $headers = array();
                            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                            $headers[] = 'Accept: application/json';
                            $headers[] = 'Api-Appid: 2_103625_EYUcpSP3e';
                            $headers[] = 'Api-Key: zHxjY8WRbBwXYXq';
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                            $result = curl_exec($ch);
                            // if (curl_errno($ch)) {
                            // echo 'Error:' . curl_error($ch);
                            // }
                            curl_close($ch);
					}
					
					if(!is_array($array_1['contact']['@attributes']['date'])) {
						$ontra_created_date = date("Y-m-d H:i:s", $array_1['contact']['@attributes']['date']);
					}
					
					if(!is_array($array_1['contact']['@attributes']['dlm'])) {
						$ontra_dlm = date("Y-m-d H:i:s", $array_1['contact']['@attributes']['dlm']);
					}
					
					if(!is_array($array_1['contact']['@attributes']['score'])) {
						$ontra_score = $array_1['contact']['@attributes']['score'];
					}
					
					if(!is_array($array_1['contact']['@attributes']['purl'])) {
						$ontra_purl = $array_1['contact']['@attributes']['purl'];
					}
					
					if(!is_array($array_1['contact']['@attributes']['bulk_mail'])) {
						$ontra_bulk_mail = $array_1['contact']['@attributes']['bulk_mail'];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][1])) {
						$ontra_first_name = $array_1['contact']['Group_Tag'][0]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][2])) {
						$ontra_last_name = $array_1['contact']['Group_Tag'][0]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][3])) {
						$ontra_email=$array_1['contact']['Group_Tag'][0]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][4])) {
						$ontra_title = $array_1['contact']['Group_Tag'][0]['field'][4];
					}
					
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][5])) {
						$oontra_account_type = $array_1['contact']['Group_Tag'][0]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][6])) {
						$ontra_account_status = $array_1['contact']['Group_Tag'][0]['field'][6];

					}
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][7])) {
						$ontra_def = $array_1['contact']['Group_Tag'][0]['field'][7];

					}
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][8])) {
						$ontra_address = $array_1['contact']['Group_Tag'][0]['field'][8];

					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][9])) {
						$ontra_address2 = $array_1['contact']['Group_Tag'][0]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][10])) {
						$ontra_city = $array_1['contact']['Group_Tag'][0]['field'][10];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][11])) {
						$oontra_state = $array_1['contact']['Group_Tag'][0]['field'][11];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][12])) {
						$ontra_zip = $array_1['contact']['Group_Tag'][0]['field'][12];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][13])) {
						$oontra_country = $array_1['contact']['Group_Tag'][0]['field'][13];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][14])) {
						$ontra_fax = $array_1['contact']['Group_Tag'][0]['field'][14];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][15])) {
						$ontra_sms_number = $array_1['contact']['Group_Tag'][0]['field'][15];
					}
						
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][16])) {
						$ontra_birthday = $array_1['contact']['Group_Tag'][0]['field'][16];
					}	


					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][18])) {
						$ontra_company = $array_1['contact']['Group_Tag'][0]['field'][18];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][19])) {
						$ontra_offc_phone = $array_1['contact']['Group_Tag'][0]['field'][19];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][0]['field'][20])) {
						$ontra_website = $array_1['contact']['Group_Tag'][0]['field'][20];
					}
					


					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][0])) {
						$ontra_spent = $array_1['contact']['Group_Tag'][3]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][2])) {
						$ontra_date_modified = $array_1['contact']['Group_Tag'][3]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][3])) {
						$ontra_ip_address = $array_1['contact']['Group_Tag'][3]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][4])) {
						$ontra_last_activity = $array_1['contact']['Group_Tag'][3]['field'][4];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][5])) {
						$ontra_last_note = $array_1['contact']['Group_Tag'][3]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][3]['field'][6])) {
						$ontra_is_agree = $array_1['contact']['Group_Tag'][3]['field'][6];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][4]['field'][0])) {
						$ontra_paypal_address = $array_1['contact']['Group_Tag'][4]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][4]['field'][1])) {
						$ontra_no_of_sales = $array_1['contact']['Group_Tag'][4]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][4]['field'][2])) {
						$ontra_last_total_invoice = $array_1['contact']['Group_Tag'][4]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][5]['field'][0])) {
						$ontra_last_invoice_no = $array_1['contact']['Group_Tag'][5]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][5]['field'][1])) {
						$ontra_last_charge = $array_1['contact']['Group_Tag'][5]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][5]['field'][2])) {
						$ontra_last_total_invoice2 = $array_1['contact']['Group_Tag'][5]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][5]['field'][3])) {
						$ontra_total_amount_unpaid = $array_1['contact']['Group_Tag'][5]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][0])) {
						$ontra_card_type = $array_1['contact']['Group_Tag'][6]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][1])) {
						$ontra_card_number = $array_1['contact']['Group_Tag'][6]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][2])) {
						$ontra_card_expiry_month = $array_1['contact']['Group_Tag'][6]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][3])) {
						$ontra_last_cc_status = $array_1['contact']['Group_Tag'][6]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][4])) {
						$ontra_card_expiry_year = $array_1['contact']['Group_Tag'][6]['field'][4];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][6]['field'][5])) {
						$ontra_card_expiry_date = $array_1['contact']['Group_Tag'][6]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][0])) {
						$ontra_date_added = $array_1['contact']['Group_Tag'][7]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][1])) {
						$ontra_trading_experience = $array_1['contact']['Group_Tag'][7]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][2])) {
						$ontra_trading_strategy = $array_1['contact']['Group_Tag'][7]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][3])) {
						$ontra_traded_live_before = $array_1['contact']['Group_Tag'][7]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][4])) {
						$ontra_still_trading_live = $array_1['contact']['Group_Tag'][7]['field'][4];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][5])) {
						$ontra_accounts_traded_live = $array_1['contact']['Group_Tag'][7]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][6])) {
						$ontra_avg_trades_per_day = $array_1['contact']['Group_Tag'][7]['field'][6];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][7])) {
						$ontra_time_in_trade = $array_1['contact']['Group_Tag'][7]['field'][7];
					}
					
				/*	if(!is_array($array_1['contact']['Group_Tag'][7]['field'][8])) {
						$ontra_5_day_statement = $array_1['contact']['Group_Tag'][7]['field'][8];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][9])) {
						$ontra_user_ip = $array_1['contact']['Group_Tag'][7]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][7]['field'][10])) {
						$ontra_about_trader = $array_1['contact']['Group_Tag'][7]['field'][10];
					}
					*/
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][0])) {
						$ontra_live_user_id = $array_1['contact']['Group_Tag'][8]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][1])) {
						$ontra_live_account_id = $array_1['contact']['Group_Tag'][8]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][2])) {
						$ontra_password_live = $array_1['contact']['Group_Tag'][8]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][3])) {
						$ontra_live_activation_date = $array_1['contact']['Group_Tag'][8]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][4])) {
						$ontra_live_expiration_date = $array_1['contact']['Group_Tag'][8]['field'][4];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][5])) {
						$ontra_live_account_balance = $array_1['contact']['Group_Tag'][8]['field'][5];
					}
					
					/*
					$ontra_live_account_balance=$array_1['contact']['Group_Tag'][8]['field'][5];*/
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][6])) {
						$ontra_live_termination = $array_1['contact']['Group_Tag'][8]['field'][6];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][7])) {
						$ontra_status = $array_1['contact']['Group_Tag'][8]['field'][7];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][8])) {
						$ontra_live_trading_status = $array_1['contact']['Group_Tag'][8]['field'][8];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][8]['field'][9])) {
						$ontra_termination_reason = $array_1['contact']['Group_Tag'][8]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][9]['field'][0])) {
						$ontra_demo_user_id = $array_1['contact']['Group_Tag'][9]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][9]['field'][1])) {
						$ontra_demo_account_id = $array_1['contact']['Group_Tag'][9]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][9]['field'][2])) {
						$oontra_demo_password = $array_1['contact']['Group_Tag'][9]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][9]['field'][4])) {
                        $ac = $array_1['contact']['Group_Tag'][9]['field'][4];
                        $ontra_activation_date = DateTime::createFromFormat('m-d-Y', $ac)->format('Y-m-d');
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][5])) {
                        $ex = $array_1['contact']['Group_Tag'][9]['field'][5];

                        $ontra_expiration_date = DateTime::createFromFormat('m-d-Y', $ex)->format('Y-m-d');
                        //echo 'ontra_expiration_date'.$ontra_expiration_date;
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][6])) {
                        $ontra_termination_date = $array_1['contact']['Group_Tag'][9]['field'][6];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][7])) {
                        $ontra_questionnaire = $array_1['contact']['Group_Tag'][9]['field'][7];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][8])) {
                        $ontra_contest_start = $array_1['contact']['Group_Tag'][9]['field'][8];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][9])) {
                        $ontra_contest_end = $array_1['contact']['Group_Tag'][9]['field'][9];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][10])) {
                        $ontra_contest_confirmed = $array_1['contact']['Group_Tag'][9]['field'][10];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][11])) {
                        $ontra_trading_status = $array_1['contact']['Group_Tag'][9]['field'][11];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][12])) {
                        $ontra_ending_account_balance = $array_1['contact']['Group_Tag'][9]['field'][12];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][13])) {
                        $ontra_demo_results = $array_1['contact']['Group_Tag'][9]['field'][13];
                    }
                    
                    if(!is_array($array_1['contact']['Group_Tag'][9]['field'][14])) {
                        $ontra_demo_fail_reasons = $array_1['contact']['Group_Tag'][9]['field'][14];
                    }
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][0])) {
						$ontra_products_traded = $array_1['contact']['Group_Tag'][10]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][1])) {
						$ontra_trading_platform = $array_1['contact']['Group_Tag'][10]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][2])) {
						$ontra_professional_background = $array_1['contact']['Group_Tag'][10]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][3])) {
						$ontra_trading_style = $array_1['contact']['Group_Tag'][10]['field'][3];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][4])) {
						$ontra_why_trading = $array_1['contact']['Group_Tag'][10]['field'][4];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][5])) {
						$ontra_daily_preparation = $array_1['contact']['Group_Tag'][10]['field'][5];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][6])) {
						$ontra_short_term_goals = $array_1['contact']['Group_Tag'][10]['field'][6];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][7])) {
						$ontra_long_term_goals = $array_1['contact']['Group_Tag'][10]['field'][7];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][8])) {
						$ontra_strengths = $array_1['contact']['Group_Tag'][10]['field'][8];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][10]['field'][9])) {
						$ontra_weaknesses = $array_1['contact']['Group_Tag'][10]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][11]['field'])) {
						$ontra_last_inbound_sms = $array_1['contact']['Group_Tag'][11]['field'];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][0])) {
						$ontra_iB_id = $array_1['contact']['Group_Tag'][12]['field'][0];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][1])) {
						$ontra_account_value = $array_1['contact']['Group_Tag'][12]['field'][1];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][2])) {
						$ontra_min_account_balance = $array_1['contact']['Group_Tag'][12]['field'][2];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][3])) {
						$ontra_fcm_id = $array_1['contact']['Group_Tag'][12]['field'][3];
					}

					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][6])) {
						$ontra_days = $array_1['contact']['Group_Tag'][12]['field'][6];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][7])) {
						$ontra_rms_buy_limit = $array_1['contact']['Group_Tag'][12]['field'][7];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][8])) {
						$ontra_rms_sell_limit = $array_1['contact']['Group_Tag'][12]['field'][8];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][9])) {
						$ontra_rms_loss_limit = $array_1['contact']['Group_Tag'][12]['field'][9];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][10])) {
						$ontra_rms_max_order = $array_1['contact']['Group_Tag'][12]['field'][10];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][11])) {
						$ontra_send_to_rithmic = $array_1['contact']['Group_Tag'][12]['field'][11];
					}

					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][12])) {
						$ontra_commision_fill_rate = $array_1['contact']['Group_Tag'][12]['field'][12];
					}
					
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][13])) {
						$ontra_daily_loss_limit = $array_1['contact']['Group_Tag'][12]['field'][13];
					}
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][14])) {
						$ontra_max_down = $array_1['contact']['Group_Tag'][12]['field'][14];
					}
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][15])) {
						$ontra_profit_target = $array_1['contact']['Group_Tag'][12]['field'][15];
					}
					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][16])) {
						$ontra_target_days = $array_1['contact']['Group_Tag'][12]['field'][16];
					}

					if(!is_array($array_1['contact']['Group_Tag'][12]['field'][18])) {
						$ontra_update_date_time = $array_1['contact']['Group_Tag'][12]['field'][18];
					}
					
					date_default_timezone_set("America/Chicago");	
					$Uupdated_at = date('Y-m-d H:i:s');
					$curr_time = date("h:i:s");
					DB::table('users')
					->where('user_id', $u_userid)
					->update(['updated_at' => $Uupdated_at,
					'ontra_contact_id' => $ontra_contact_id,
					'ontra_created_date' => $ontra_created_date,
					'ontra_dlm' => $ontra_dlm,
					'ontra_score' => $ontra_score,
					'ontra_purl' => $ontra_purl,
					'ontra_bulk_mail' => $ontra_bulk_mail,
					'ontra_first_name' => $ontra_first_name,
					'ontra_last_name' => $ontra_last_name,
					'ontra_email' => $ontra_email,
					'ontra_account_type' => $oontra_account_type,
					'ontra_company' => $ontra_company,
					'ontra_title' => $ontra_title,
					'ontra_account_status' => $ontra_account_status,
					'ontra_address' => $ontra_address,
					'ontra_address2' => $ontra_address2,
					'ontra_city' => $ontra_city,
					'ontra_state' => $oontra_state,
					'ontra_zip' => $ontra_zip,
					'ontra_country' => $oontra_country,
					'ontra_fax' => $ontra_fax,
					'ontra_sms_number' => $ontra_sms_number,
					'ontra_offc_phone' => $ontra_offc_phone,
					'ontra_birthday' => $ontra_birthday,
					'ontra_website' => $ontra_website,
					'ontra_spent' => $ontra_spent,
					'ontra_date_modified' => $ontra_date_modified,
					'ontra_ip_address' => $ontra_ip_address,
					'ontra_last_activity' => $ontra_last_activity,
					'ontra_last_note' => $ontra_last_note,
					'ontra_is_agree' => $ontra_is_agree,
					'ontra_paypal_address' => $ontra_paypal_address,
					'ontra_no_of_sales' => $ontra_no_of_sales,
					'ontra_last_total_invoice' => $ontra_last_total_invoice,
					'ontra_last_invoice_no' => $ontra_last_invoice_no,
					'ontra_last_total_invoice2' => $ontra_last_total_invoice2,
					'ontra_total_amount_unpaid' => $ontra_total_amount_unpaid,
					'ontra_card_type' => $ontra_card_type,
					'ontra_card_number' => $ontra_card_number,
					'ontra_card_expiry_month' => $ontra_card_expiry_month,
					'ontra_last_cc_status' => $ontra_last_cc_status,
					'ontra_card_expiry_year' => $ontra_card_expiry_year,
					'ontra_card_expiry_date' => $ontra_card_expiry_date,
					'ontra_date_added' => $ontra_date_added,
					'ontra_trading_experience' => $ontra_trading_experience,
					'ontra_trading_strategy' => $ontra_trading_strategy,
					'ontra_traded_live_before' => $ontra_traded_live_before,
					'ontra_still_trading_live' => $ontra_still_trading_live,
					'ontra_accounts_traded_live' => $ontra_accounts_traded_live,
					'ontra_avg_trades_per_day' => $ontra_avg_trades_per_day,
					'ontra_time_in_trade' => $ontra_time_in_trade,
					//'ontra_5_day_statement' => $ontra_5_day_statement,
					//'ontra_user_ip' => $ontra_user_ip,
					//'ontra_about_trader' => $ontra_about_trader,
					'ontra_live_user_id' => $ontra_live_user_id,
					'ontra_live_account_id' => $ontra_live_account_id,
					'ontra_password_live' => $ontra_password_live,
					'ontra_live_activation_date' => $ontra_live_activation_date,
					'ontra_live_expiration_date' => $ontra_live_expiration_date,
					'ontra_live_account_balance' => $ontra_live_account_balance,
					'ontra_live_termination' => $ontra_live_termination,
					'ontra_status' => $ontra_status,
					'ontra_live_trading_status' => $ontra_live_trading_status,
					'ontra_termination_reason' => $ontra_termination_reason,
					'ontra_demo_user_id' => $ontra_demo_user_id,
					'ontra_demo_account_id' => $ontra_demo_account_id,
					'ontra_acc_def'	=> $ontra_def,
					'ontra_activation_date' => $ontra_activation_date,
					'ontra_expiration_date' => $ontra_expiration_date,
					'ontra_time' => $curr_time,
					'ontra_termination_date' => $ontra_termination_date,
					'ontra_questionnaire' => $ontra_questionnaire,
					'ontra_contest_start' => $ontra_contest_start,
					'ontra_contest_end' => $ontra_contest_end,
					'ontra_contest_confirmed' => $ontra_contest_confirmed,
					'ontra_trading_status' => $ontra_trading_status,
					'ontra_ending_account_balance' => $ontra_ending_account_balance,
					'ontra_demo_results' => $ontra_demo_results,
					'ontra_demo_fail_reasons' => $ontra_demo_fail_reasons,
					'ontra_products_traded' => $ontra_products_traded,
					'ontra_trading_platform' => $ontra_trading_platform,
					'ontra_professional_background' => $ontra_professional_background,
					'ontra_trading_style' => $ontra_trading_style,
					'ontra_why_trading' => $ontra_why_trading,
					'ontra_daily_preparation' => $ontra_daily_preparation,
					'ontra_short_term_goals' => $ontra_short_term_goals,
					'ontra_long_term_goals' => $ontra_long_term_goals,
					'ontra_strengths' => $ontra_strengths,
					'ontra_weaknesses' => $ontra_weaknesses,
					'ontra_last_inbound_sms' => $ontra_last_inbound_sms,
					'ontra_iB_id' => $ontra_iB_id,
					'ontra_account_value' => $ontra_account_value,
					'ontra_min_account_balance' => $ontra_min_account_balance,
					'ontra_fcm_id' => $ontra_fcm_id,
					'ontra_rms_buy_limit' => $ontra_rms_buy_limit,
					'ontra_rms_sell_limit' => $ontra_rms_sell_limit,
					'ontra_rms_loss_limit' => $ontra_rms_loss_limit,
					'ontra_rms_max_order' => $ontra_rms_max_order,
					'ontra_commision_fill_rate' => $ontra_commision_fill_rate,
					'ontra_days' => $ontra_days,
					'ontra_send_to_rithmic' => $ontra_send_to_rithmic,
					'ontra_update_date_time' => $ontra_update_date_time,
					'ontra_daily_loss_limit' => $ontra_daily_loss_limit,
					'ontra_max_down' => $ontra_max_down,
					'ontra_profit_target' => $ontra_profit_target,
					'ontra_target_days' => $ontra_target_days,
					'ontra_demo_password' => $ontra_demo_password]);
					
					 
				

					$tranID = $CheckoutPaymentInformation['TransactionID'];
					$tranDate = $CheckoutPaymentInformation['created_at'];
					$tranAmount = $CheckoutPaymentInformation['total_amount'];
					$emailTB = DB::table('tb_email')->where('id','=', 3)->first();
					$to=$UserRegistration['email'];
	                $subject='Payment Successful';
	                $message='<html><head><META http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
								<body><div style="font-size:12px;width:100%;height:100%">
								<table width="800" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#efefef;width:800px;max-width:800px;outline:rgb(239,239,239) solid 1px;margin:0px auto"><tbody><tr><td valign="top">
								            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ecebeb;margin:0px auto"><tbody><tr><td align="center" valign="top">
								                  
								                  <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="padding-left:20px;padding-right:20px;background-color:#ecebeb;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                        
								                        <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td valign="top" height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                          </tr><tr><td valign="middle">
								                              <table align="left" border="0" cellspacing="0" cellpadding="0" width="auto"><tbody><tr><td align="center" valign="top" width="150" style="width:150px">
								                                    <a href="#0.2_" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0"><img src="http://mailbuild.rookiewebstudio.com/customers/q7y6M2Sw/user_upload/20170212021144_oneup.png" width="150" style="max-width:150px;display:block!important;width:150px;height:auto;" alt="Logo" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                </tr><tr><td valign="top">
								                                  </td>
								                                </tr></tbody></table><table border="0" align="right" cellpadding="0" cellspacing="0" width="auto"><tbody><tr><td valign="middle" align="center">
								                                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="height:100%;margin:0px auto" width="auto"><tbody><tr><td style="font-size:13px;color:#a3a2a2;font-weight:300;text-align:center;font-family:Roboto,Arial,Helvetica,sans-serif;word-break:break-word;line-height:21px" align="center"><br style="font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"></td>
								                                      </tr></tbody></table></td>
								                                </tr></tbody></table></td>
								                          </tr><tr><td valign="top" height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td valign="top" align="center" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            <table width="600" height="20" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top" height="20" width="20" style="width:20px;height:20px;line-height:20px;font-size:20px">
								                <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/space.png" width="20" alt="space" style="display:block!important;max-height:20px;max-width:20px"></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td valign="top">
								                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0px auto"><tbody><tr><td valign="top" width="auto" align="center">
								                              
								                              <table border="0" align="center" cellpadding="0" cellspacing="0" width="auto" style="margin:0px auto"><tbody><tr><td width="auto" align="center" valign="middle" height="28" style="border:1px solid rgb(236,236,237);font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;text-align:center;color:#a3a2a2;font-weight:300;padding-left:18px;padding-right:18px;word-break:break-word;line-height:26px"><span style="color:#a3a2a2;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"> <a href="#0.2_" style="color:#a3a2a2;text-decoration:none!important;border-style:none;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif" border="0"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="color:#333333;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"> PAYMENT</font></span> <span style="color:#dd6b82;line-height:30px;font-size:22px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">CONFIRMATION</font></span></font></a></font></span></td>
								                                </tr></tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="27" style="height:27px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                    </tr><tr><td valign="top">
								                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin:0px auto"><tbody><tr><td valign="top" width="100%">
								                              
								                              <table width="270" border="0" cellspacing="0" cellpadding="0" align="left" style="width:270px"><tbody><tr><td valign="top">
								                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left"><tbody><tr><td valign="top" align="left" width="200" style="width:200px">
								                                          <a href="#0.2_" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0">
								                                            <img src="https://app.oneuptrader.net/images/paid.png" width="200" alt="image8" style="max-width:200px;display:block!important;width:200px;height:auto;margin-top: 30px;margin-left: 20px;" border="0" hspace="0" vspace="0" height="auto"></a>
								                                        </td>
								                                      </tr></tbody></table></td>
								                                </tr><tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                </tr></tbody></table><table width="1" border="0" cellpadding="0" cellspacing="0" align="left" style="font-size:0px;line-height:0;border-collapse:collapse;width:1px"><tbody><tr><td width="0" height="2" style="border-collapse:collapse;width:0px;height:2px;line-height:2px;font-size:2px">
								                                    <p style="padding-left:20px"> </p>
								                                  </td>
								                                </tr></tbody></table><table width="270" border="0" cellspacing="0" cellpadding="0" align="right" style="width:270px"><tbody><tr><td valign="top">
								                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="left"><tbody><tr><td style="font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#555555;font-weight:300;text-align:left;word-break:break-word;line-height:26px" align="left"><span style="line-height:26px;font-size:18px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">'.$emailTB->head.'</font></span></td>
								                                      </tr>
								                                      <tr><td style="font-size:18px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#555555;font-weight:300;text-align:left;word-break:break-word;line-height:26px" align="left"><span style="line-height:26px;font-size:18px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">You are All Set.</font></span></td>
								                                      </tr>
								                                      <tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr>
								                                      <tr>
								                                      <td style="font-size: 13px; font-family: Roboto, Arial, Helvetica, sans-serif; color: #a3a2a2; font-weight: 300; text-align: left; word-break: break-word; line-height: 21px;" align="left">
								                                      <span style="line-height: 21px; font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">'.$emailTB->content.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Below are your transaction details 
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; -&nbsp;&nbsp;'.$tranID.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction Date&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;'.$tranDate.'
								                                      <br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">Transaction Amount&nbsp;&nbsp;&nbsp;&nbsp; -&nbsp;&nbsp;'.$tranAmount.'<br style="font-size: 13px; font-weight: 300; font-family: Roboto, Arial, Helvetica, sans-serif;">
								                                      </span>
								                                      </td>
								                                      </tr><tr><td height="29" style="height:29px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr>
								                                      <tr><td valign="top" width="auto">
								                                          
								                                          <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto">
								                                          <tbody>
								                                          <tr>
								                                            <td valign="top">
								                                                <table border="0" align="left" cellpadding="0" cellspacing="0" width="auto">
								                                                  <tbody>
								                                                    
								                                                  <tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                                                  </tr></tbody></table></td>
								                                            </tr></tbody></table></td>
								                                      </tr><tr><td height="20" style="height:20px;font-size:0px;line-height:0;border-collapse:collapse"> </td>
								                                      </tr></tbody></table></td>
								                                </tr>
								                                </tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr><tr><td>
								                        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="margin:0px auto"><tbody><tr><td height="5" style="height:5px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                          </tr><tr><td style="border-bottom:1px solid #c7c7c7"></td>
								                          </tr></tbody></table></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#ecebeb" bgcolor="#ecebeb">
								            
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#ecebea;padding-left:20px;padding-right:20px;min-width:600px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="4" style="height:4px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr><tr><td valign="top" align="center">
								                        
								                        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0px auto"><tbody><tr><td valign="top" align="center">
								                              <table border="0" align="center" cellpadding="0" cellspacing="0" style="table-layout:fixed;margin:0px auto" width="auto"><tbody><tr>
								                                  
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://www.youtube.com/c/Oneuptrader" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-youtube-color.png" width="30" alt="icon-youtube" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://www.facebook.com/OneUpTrader/" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-facebook-color.png" width="30" alt="icon-facebook" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  <td style="padding-left:5px;width:30px;height:30px;line-height:30px;font-size:30px" height="30" align="center" valign="middle" width="30">
								                                    <a href="https://twitter.com/OneUpTrader" style="font-size:inherit;border-style:none;text-decoration:none!important" border="0" target="_blank">
								                                      <img src="http://mailbuild.rookiewebstudio.com/item/bxToVZR3/images/icon-twitter-color.png" width="30" alt="icon-twitter" style="max-width:30px;height:auto;display:block!important" border="0" hspace="0" vspace="0" height="auto"></a>
								                                  </td>
								                                  
								                                  
								                                  
								                                </tr></tbody></table></td>
								                          </tr></tbody></table></td>
								                    </tr><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody><tbody><tr><td align="center" valign="top" style="background-color:#dd6b82" bgcolor="#dd6b82">
								            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" style="min-width:600px;background-color:#dd6b82;padding-left:20px;padding-right:20px;width:600px;margin:0px auto"><tbody><tr><td valign="top">
								                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="width:560px;margin:0px auto"><tbody><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr><tr><td valign="top" style="font-size:13px;font-family:Roboto,Arial,Helvetica,sans-serif;color:#ffffff;font-weight:300;text-align:left;word-break:break-word;line-height:21px" align="left"><div style="text-align:left;font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif"><span style="line-height:21px;font-size:13px;font-weight:300;font-family:Roboto,Arial,Helvetica,sans-serif"><font face="Roboto, Arial, Helvetica, sans-serif">'.date('Y').' © OneUp Trader. All Rights Reserved. </font></span></font></div></td>
								                      
								                    </tr><tr><td height="10" style="height:10px;font-size:0px;line-height:0;border-collapse:collapse"></td>
								                    </tr></tbody></table></td>
								              </tr></tbody></table></td>
								        </tr></tbody></table></div>
								</body></html>';
	                $headers = "MIME-Version: 1.0" . "\r\n";
	                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";        
	                $headers .= 'From: OneUp Trader<'.$emailTB->email.'>' . "\r\n";
	                $from = $emailTB->email;
	             
	                //mail($to,$subject,$message,$headers);
	

	                $url = 'https://api.sendgrid.com/';
	                $user = env('MAIL_USERNAME');
 					$pass = env('MAIL_PASSWORD');
	                $params = array(
	                'api_user'  => $user,
	                'api_key'   => $pass,
	                'to'        => $to,
	                'subject'   => $subject,
	                'html'      => $message,
	                'from'      => $from,
					'fromname'  => "OneUp Trader",

	                 );
	                $request =  $url.'api/mail.send.json';
	                $session = curl_init($request);
					curl_setopt ($session, CURLOPT_POST, true);
					curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $pass));
	                curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
	                curl_setopt($session, CURLOPT_HEADER, false);
	                curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
	                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	                $response = curl_exec($session);
	                curl_close($session);	
					
					if(($last_ontra_account_type == 'OUP TRIAL14DAY') || ($last_ontra_account_type == '')){


						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type_from_ontra' => trim($ontra_account_type),'account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}else{
						DB::table('users')			
						->where('user_id', $u_userid)			
						->update(['account_type'=>'demo','ontra_days'=>'30','updated_at'=>date('Y-m-d H:i:s'),'ontra_acc_def' => $acc_def]);
					}	


					if($last_account_type == ''){

						$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}	

						//Start post CSV for NEW CONTACT USER/ ACCOUNT
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'],
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'demo_password' => trim($csvUser['ontra_demo_password']),
				                    'demo_termination_date' => '',
				                    'days' => trim($csvUser['ontra_days']),
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'country' => trim($con['ontra_name']),
				                    'account_status' => trim($csvUser['ontra_account_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'login_expiration' => '',
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Account_type' => trim($csvUser['ontra_account_type']),
				                    'Account_Threshold' => $Account_Threshold,
				                );
				             
				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/new_contact.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
				        //End post CSV for NEW CONTACT USER/ ACCOUNT

				        //Start post CSV for NEW CONTACT PARAMETER
				           date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
				            $run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
				            DB::table('csv_act')->insert([                    
				                'IB_id' => $csvUser['ontra_iB_id'],
				                'User_ID' => $csvUser['ontra_demo_user_id'],
				                'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                'first_name' => trim($csvUser['first_name']),
				                'last_name' => trim($csvUser['last_name']),
				                'email' => trim($csvUser['email']),
				                'demo_password' => trim($csvUser['ontra_demo_password']),
				                'days' => trim($csvUser['ontra_days']),
				                'trading_status' => trim($csvUser['ontra_trading_status']),
				                'address' => trim($csvUser['address']),
				                'city' => trim($csvUser['city']),
				                'state' => trim($stName),
				                'zip' => trim($csvUser['zip']),
				                'country' => trim($con['ontra_name']),
				                'account_status' => trim($csvUser['ontra_account_status']),
				                'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                'account_value' => $account_value,
				                'RMS_buy_limit' => $RMS_buy_limit,
				                'RMS_sell_limit' => $RMS_sell_limit,
				                'RMS_loss_limit' => $RMS_loss_limit,
				                'RMS_max_order_qty' => $RMS_max_order_qty,
				                'min_account_balance' => $min_account_balance,
				                'Commission_fill_rate' => $Commission_fill_rate, 
				                'curr_time' => $curr_time,
				                'run_time' => $run_time          
				            ]);

				            /*$data1 = array(
				                    'IB_id' => $csvUser['ontra_iB_id'],
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'demo_password' => trim($csvUser['ontra_demo_password']),
				                    'days' => trim($csvUser['ontra_days']),
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'country' => trim($con['ontra_name']),
				                    'account_status' => trim($csvUser['ontra_account_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                );
				             
				            $ch1 = curl_init();
				            $curlConfig1 = array(
				                CURLOPT_URL            => "http://oneuptrader.net/ontraport/MES_new/new_con.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data1
				            );
				            curl_setopt_array($ch1, $curlConfig1);
				            $result1 = curl_exec($ch1);
				            curl_close($ch1);*/
				        //End post CSV for NEW CONTACT PARAMETER   

				            // $user_id = $csvUser['user_id'];
				            // $u_name = $csvUser['name'];
				            // $email = $csvUser['email'];
				            // $tos = 'support@oneuptrader.com';
				            // //$tos = 'sohan.constacloud@gmail.com';
			                // $subjects = "OneUp Trader New User Signup";
			                // $messages='<html>
			                //             <head>
			                //               <title>OneUp Trader New User Signup</title>
			                //             </head>
			                //             <body>
			                //               <table>
			                //              <tr><td>User ID    :</td><td>'.$user_id.'</td></tr>
			                //              <tr><td>User Name  :</td><td>'.$u_name.'</td></tr>
			                //              <tr><td>Email      :</td><td>'.$email.'</td></tr>
			                //              <tr><td>Created at :</td><td>'.date('Y-m-d H:i:s').'</td></tr>
			                //               </table>
			                //             </body>
			                //             </html>';
			                // $urls = 'https://api.sendgrid.com/';
			                //$users = env('MAIL_USERNAME');
 							//$passs = env('MAIL_PASSWORD');
			                // $paramss = array(
			                // 'api_user'  => $users,
			                // 'api_key'   => $passs,
			                // 'to'        => $tos,
			                // 'subject'   => $subjects,
			                // 'html'      => $messages,
			                // 'from'      => 'support@oneuptrader.com',
			                // 'fromname'  => "OneUp Trader",

			                //  );
			                // $requests =  $urls.'api/mail.send.json';
			                // $sessions = curl_init($requests);
							// curl_setopt ($sessions, CURLOPT_POST, true);
							 //curl_setopt($sessions, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $passs));
			                // curl_setopt ($sessions, CURLOPT_POSTFIELDS, $paramss);
			                // curl_setopt($sessions, CURLOPT_HEADER, false);
			                // curl_setopt($sessions, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
			                // curl_setopt($sessions, CURLOPT_RETURNTRANSFER, true);
			                // $responses = curl_exec($sessions);
			                // curl_close($sessions);
					}
					else{

						$csvUser = UserRegistration::where('user_id','=',$u_userid)->first();
						$csValue = $csvUser['temp_account_type'];

						if($csValue == '$25,000'){
							$account_value = 25000;
		                    $RMS_buy_limit = 3;
		                    $RMS_sell_limit = 3;
		                    $RMS_loss_limit = 500;
		                    $RMS_max_order_qty = 9;
		                    $min_account_balance = 23500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 25000;	
		                    $Account_Threshold = 1500;
						}elseif($csValue == '$50,000'){
							$account_value = 50000;
		                    $RMS_buy_limit = 6;
		                    $RMS_sell_limit = 6;
		                    $RMS_loss_limit = 1250;
		                    $RMS_max_order_qty = 18;
		                    $min_account_balance = 47500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 50000;	
		                    $Account_Threshold = 2500;
						}elseif($csValue == '$100,000'){
							$account_value = 100000;
		                    $RMS_buy_limit = 12;
		                    $RMS_sell_limit = 12;
		                    $RMS_loss_limit = 2500;
		                    $RMS_max_order_qty = 36;
		                    $min_account_balance = 96500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 100000;	
		                    $Account_Threshold = 3500;
						}elseif($csValue == '$150,000'){
							$account_value = 150000;
		                    $RMS_buy_limit = 15;
		                    $RMS_sell_limit = 15;
		                    $RMS_loss_limit = 4000;
		                    $RMS_max_order_qty = 45;
		                    $min_account_balance = 145000;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 150000;	
		                    $Account_Threshold = 5000;
						}elseif($csValue == '$250,000'){
							$account_value = 250000;
		                    $RMS_buy_limit = 25	;
		                    $RMS_sell_limit = 25;
		                    $RMS_loss_limit = 5000;
		                    $RMS_max_order_qty = 75;
		                    $min_account_balance = 244500;
		                    $Commission_fill_rate = 2.5;
		                    $auto_liquidate = 250000;	
		                    $Account_Threshold = 5500;
						}

						//Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 1
				             $con = countries::where('id','=',$csvUser['country'])->first();
				              
				              if($csvUser['state'] != ''){
				                $sta = states::where('id','=',$csvUser['state'])->first();
				                $stName = $sta['name'];
				              }else{
				                $stName = '';
				              }
				            $data = array(
				                    'IB_id' => $csvUser['ontra_iB_id'], 
				                    'User_ID' => $csvUser['ontra_demo_user_id'],
				                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
				                    'trading_status' => trim($csvUser['ontra_trading_status']),
				                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
				                    'account_value' => $account_value,
				                    'RMS_buy_limit' => $RMS_buy_limit,
				                    'RMS_sell_limit' => $RMS_sell_limit,
				                    'RMS_loss_limit' => $RMS_loss_limit,
				                    'RMS_max_order_qty' => $RMS_max_order_qty,
				                    'min_account_balance' => $min_account_balance,
				                    'Commission_fill_rate' => $Commission_fill_rate,
				                    'first_name' => trim($csvUser['first_name']),
				                    'last_name' => trim($csvUser['last_name']),
				                    'email' => trim($csvUser['email']),
				                    'days' => trim($csvUser['ontra_days']),
				                    'country' => trim($con['ontra_name']),
				                    'state' => trim($stName),
				                    'zip' => trim($csvUser['zip']),
				                    'address' => trim($csvUser['address']),
				                    'city' => trim($csvUser['city']),
				                    'risk_algorithm' => trim($csvUser['ontra_send_to_rithmic']),
				                    'auto_liquidate' => $auto_liquidate,
				                    'Last_Account_ID' => trim($last_demo_account_id),
				                    'Account_Threshold' => $Account_Threshold,
				                );
				             
				            $ch = curl_init();
				            $curlConfig = array(
				                CURLOPT_URL            => env('URL_CURL_ONEUPTRADER')."/ontraport/MES_new/ud_1.php",
				                CURLOPT_POST           => true,
				                CURLOPT_RETURNTRANSFER => true,
				                CURLOPT_POSTFIELDS     => $data
				            );
				            curl_setopt_array($ch, $curlConfig);
				            $result = curl_exec($ch);
				            curl_close($ch);
				        //End post CSV for UPGRADE / DOWNGRADE BALANCE Part 1

				        //Start post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
				            date_default_timezone_set("America/Chicago");
				            $curr_time = date('Y-m-d H:i:s');
				            $run_time = date("Y/m/d H:i:s", strtotime("+15 minutes"));
				           	DB::table('csv_run')->insert([                    
								'IB_id' => $csvUser['ontra_iB_id'],
			                    'User_ID' => $csvUser['ontra_demo_user_id'],
			                    'demo_account_id' => $csvUser['ontra_demo_account_id'],
			                    'trading_status' => trim($csvUser['ontra_trading_status']),
			                    'FCM_ID' => trim($csvUser['ontra_fcm_id']),
			                    'account_value' => $account_value,
			                   	'RMS_buy_limit' => $RMS_buy_limit,
			                    'RMS_sell_limit' => $RMS_sell_limit,
			                    'RMS_loss_limit' => $RMS_loss_limit,
			                    'RMS_max_order_qty' => $RMS_max_order_qty,
			                    'min_account_balance' => $min_account_balance,
			                    'Commission_fill_rate' => $Commission_fill_rate,
			                    'days' => trim($csvUser['ontra_days']),
			                    'state' => trim($stName),
			                    'Last_Account_ID' => trim($last_demo_account_id),
			                    'curr_time' => $curr_time,
			                    'run_time' => $run_time           
							]);
				        
				        //End post CSV for UPGRADE / DOWNGRADE BALANCE Part 2
					} 
					$ontra_acc = UserRegistration::where('user_id','=',$u_userid)->first();
					$onValue = $ontra_acc['temp_account_type'];
					if($onValue == '$25,000'){
						$account_value = 25000;
	                    $profit_target = 1500;	
	                    $target_days = 15;
					}elseif($onValue == '$50,000'){
						$account_value = 50000;
	                    $profit_target = 3000;	
	                    $target_days = 15;
					}elseif($onValue == '$100,000'){
						$account_value = 100000;
	                    $profit_target = 6000;	
	                    $target_days = 15;
					}elseif($onValue == '$150,000'){
						$account_value = 150000;
	                    $profit_target = 9000;	
	                    $target_days = 15;
					}elseif($onValue == '$250,000'){
						$account_value = 250000;
	                    $profit_target = 15000;	
	                    $target_days = 15;
					}
					date_default_timezone_set("America/Chicago");
					$curr_date1 = date('Y-m-d');
	                $daystosum1 = '30';
	                $ex_date1 = date('Y-m-d', strtotime($curr_date1.' + '.$daystosum1.' days'));

						DB::table('ontra_account')->insert([					
	                    'user_id' => $u_userid,		 			
						'account_id' => $NewDemoAccountId,
						'account_type' => $ontra_account_type,
						'acc_def' => $acc_def,
						'ontra_account_value' => $account_value,
	                    'ontra_profit_target' => $profit_target,
	                    'ontra_target_days' => $target_days,
	                    'ontra_rms_buy_limit' => $ontra_acc['ontra_rms_buy_limit'],
	                    'ontra_daily_loss_limit' => $ontra_acc['ontra_daily_loss_limit'],
	                    'ontra_max_down' => $ontra_acc['ontra_max_down'],
	                    'ontra_activation_date' => $curr_date1,
	                    'ontra_expiration_date' => $ex_date1,						
						'updated_at' => date('Y-m-d H:i:s')				
						]); 

			
		}	

	public function SiteDown(){
		
		return view('multiauth::admin.errors.maintenance');
	}
	
	public function AddManualDiscount(Request $request)
	{
		
		$discount_amount = trim($request->discount_amount);
		$discount_code = trim($request->discount_code);
		$user_row_id = trim($request->user_row_id);
		$discount_evaluation = trim($request->discount_evaluation);
		$discount_evaluation_text = trim($request->discount_evaluation_text);
		
		$exist_code = DB::table('coupon_list')
		                   ->where('c_code','=',$discount_code)
						   ->first();
		
		if(is_null($exist_code)){
		   return response()->json('Please enter valid coupon code');	
		}else{
			$coupon_id = $exist_code->id;
			$c_type = $exist_code->type;
			$apply_by = 'Admin';
			$manual_amount = '$'.$discount_amount;
			
			$tbl_evaluation = DB::table('tblevaluation')->where('plan_name','=',$discount_evaluation_text)->first();
			
			$paid_amount =  ($tbl_evaluation->plan_amount) - $discount_amount;
			$paid_amount =  '$'.$paid_amount;
			//echo $tbl_evaluation->plan_amount.' - '.$discount_amount.' - '.$paid_amount;die;
			$insert_record = DB::table('coupon_use')
			                    ->insert(['user_id' => $user_row_id,'coupon_id' => $coupon_id,'c_code' => $discount_code,'c_type' => $c_type,
								          'paid_amount' => $paid_amount,'item_name' => $discount_evaluation,'item_dis' => '','apply_date' => date('Y-m-d'),
										  'apply_by' => $apply_by,'manual_amount' => $manual_amount]);
		        
				if($insert_record){
					return response()->json('success');
				}else{
					return response()->json('Oops...something went wrong');
				}
										  
		   
		}
		
		
	} 



	function manual_cancel(Request $request){

		
		$user_idwith = $request->query('user_id');
		$user_id = trim($user_idwith, "'");

		$name = UserRegistration::where('user_id',$user_id)->first();
		
				$account_type = $name->temp_account_type;
				$contact_id = $name->ontra_contact_id;


			if($name->subscription_uuid != ''){
				

					//redeemption coupon remove from account 
					if($name->recurly_acc_id != ''){	
						$redeemtion_remove_error = '';
						try{
							$redemption = Recurly_CouponRedemption::get($name->recurly_acc_id);
							$redemption->delete();
							
							}catch (Recurly_NotFoundError $e) {
								$error_response = $e->getMessage();
							}catch (Recurly_ValidationError $e) {
								$error_response = $e->getMessage();
									
							}
					
					}

		    	//recurly terminate code
					$subscription_success  = '';


					
			try {

				$getsubscription = Recurly_Subscription::get($name->subscription_uuid);
				
				if($getsubscription->state != 'expired'){

		
					try {
							$subscription = Recurly_Subscription::get($name->subscription_uuid);
							$subscription->terminateWithoutRefund();
							
						 $uuid = $subscription->uuid;
						 $state = $subscription->state; 
	
						 UserRegistration::where('user_id','=',$user_id)->update(['subscription_uuid'=>'','subscription_status'=>'']);
	
						 $subscription_success  = 'success';
	
						} catch (Recurly_NotFoundError $e) {
						// $error_response = $e->getMessage();
						$subscription_success  = 'error';
	
						} catch (Recurly_Error $e) {
							// $error_response = $e->getMessage();
							$subscription_success  = 'error';
	
						}

				}else{

					 UserRegistration::where('user_id','=',$user_id)->update(['subscription_uuid'=>'','subscription_status'=>'']);

					 $subscription_success  = 'success';
				}
						
		} catch (Recurly_NotFoundError $e) {
  			$subscription_success  = 'error';
		}					
	
						if($subscription_success == 'success'){
	
							if($account_type == "$25,000"){
								$pay_status = "25";
								$remove_ontra_account_type_sequence = 18;
							}else if($account_type == "$50,000"){
								$pay_status = "50";
								$remove_ontra_account_type_sequence = 14;
							}else if($account_type == "$100,000"){
								$pay_status = "100";
								$remove_ontra_account_type_sequence = 19;
							}else if($account_type == "$150,000"){
								$pay_status = "150";
								$remove_ontra_account_type_sequence = 21;
							}else{
								$pay_status = "250";
								$remove_ontra_account_type_sequence = 22;
							}
		
							$data='{
								"objectID": 0,
								"id": '.$contact_id.',
								"f1548": "'.$pay_status.'"
							}';
							$curl=curl_init('http://api.ontraport.com/1/objects');
		
							curl_setopt($curl, CURLOPT_HTTPHEADER, array(
								'Content-Type: application/json',
								"Api-Key: ".decrypt(config('app.Hkey')->ont_ky),
								"Api-Appid:".decrypt(config('app.Hkey')->ont_apky)

							)
							);
		
							curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
							curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
							curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
							$auth = curl_exec($curl);
							$info = curl_getinfo($curl);
							
	
							   $acc_cnt = DB::table('acc_change')->where('user_id',$user_id)->first();
							
								
								if($acc_cnt){
									DB::table('acc_change')->where('user_id', $user_id)->update(['cancel' => 1]);
								}else{
									 DB::table('acc_change')->insert([                   
													'user_id' => $user_id, 
													'cancel' => 1,           
													'update_at' => date('Y-m-d H:i:s')             
													]); 
								}


							
								$last = DB::table('CheckoutPaymentInformation')->where('user_id',$user_id)->orderBy('id', 'DESC')->first();
								
								if($last){
										$last_id =  $last->id;
										
										DB::table('CheckoutPaymentInformation')
											->where('id', $last_id)->where('user_id',$user_id)
											->update(['activate' => 0]);
									
								}

									
							return redirect('admin/user-list')
											->with('message','Account Canceled Successfully')
											->with('status','success');	
							
						}else{
							return redirect('admin/user-list')
										->with('message','Account cancellation could not proceed, subscription not found')
										->with('status','error');	
						}

						

		}else{

			return redirect('admin/user-list')
						->with('message','Account cancellation could not proceed, subscription not found')
						->with('status','error');	
		}
					
		
	}



	public function update_pwd(Request $request){

		
		$UserRegistration = UserRegistration::where('user_id', '=', $request->user_id)->first();
	
	if($request->newpwd != ''){
		if($UserRegistration){
		         $newpwd = bcrypt($request->newpwd);
				 $created = date('Y-m-d H:i:s');
				 DB::table('pwds')->insert(['user_id'=>$request->user_id,'pwd'=>$UserRegistration->password,'created_at'=>$created,'act'=>1]);
                 DB::table('users')->where('user_id', $request->user_id)->update(['password' => $newpwd]);
				 return redirect()->back()->with('message' ,'Account password updated successfully')->with('status' ,'success');
			}else{
				return redirect()->back()->with('message' ,'Account password could not updated')->with('status' ,'error');
			}
	}else{
		return redirect()->back()->with('message' ,'Account password could not blank')->with('status' ,'error');
	}
    	
	}
	

	public function restore_pwd(Request $request){

		$pwds = DB::table('pwds')->where('user_id', '=', $request->user_id)->where('act',1)->first();
		
		if($pwds){
				DB::table('pwds')->where('user_id', $request->user_id)->update(['act' => 0]);
				DB::table('users')->where('user_id', $request->user_id)->update(['password' => $pwds->pwd]);
				 return redirect()->back()->with('message' ,'Account password restored successfully')->with('status' ,'success');
			}else{
				return redirect()->back()->with('message' ,'Account password could not restored')->with('status' ,'error');
			}
	
    	
		}
		


		public function payouts(){
	
       $Evaluation = DB::table('tblevaluation')->get();
      	   //echo "<pre>";
       //print_r(json_decode(json_encode($Evaluation),true));die;
       return view('multiauth::admin.payout_statement',['evaluation_list' => json_decode(json_encode($Evaluation),true)]);


    }

		 public function getPayoutStatement(Request $request)
    {

			$affiliate = $request->affiliate;
		
        $offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'c.id';
                break;
            case 'Payout Amount':
                $sort = 'c.amount';
                break;
            default:
                $sort = 'c.id';
        }

        $data=array();
        $rows=array();

        $columns=['c.amount'];
				 
        $users = DB::table('conversions as c')
						->select('c.tapfiliate','c.amount','c.withdraw','c.amount','c.created_at','u.user_id','u.email','u.name','u.first_name','u.last_name')
						->leftjoin('users as u','c.tapfiliate','=','u.tapfiliate')
						->where('c.withdraw', '=', 1)
						->orderBy('c.created_at','desc')
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
						})
						  ->where(function ($query) use($affiliate){
						if($affiliate != '')
						{
										$query->where('c.tapfiliate', '=', $affiliate);							
						}
						})
						->orderBy($sort, $order)->skip($offset)->take($limit)
						->get();

					
			 
        $users_total = DB::table('conversions')
           ->where('withdraw', '=', 1)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
						})
						->where(function ($query) use($affiliate){
						if($affiliate != '')
						{
										$query->where('tapfiliate', '=', $affiliate);							
						}
						})
						->get();
					
						$data = array();
						$data2 = array();
						$sno = $offset+1;
						
						foreach($users as $key => $b){
   

						$data['sno'] = $sno++;
						$data['username'] = $b->name;
						$data['email'] = $b->email;
						$data['name'] = $b->first_name.' '.$b->last_name;
						$data['redeem_date'] =  date('d/m/Y',strtotime($b->created_at));  
						$data['amount'] = '$'.$b->amount;
						$data2[] = $data;
					}	    

					$record = array();
        $record['total']= count($users_total);
        $record['rows']=$data2;
				
        return response()->json($record);

		}
		

		public function get_users(Request $request){
		
			if(!empty($_GET['q'])) {

				$search = $_GET['q'];

				$columns=['email','name'];

		$result	=	DB::table('users')
				->where('status', '=', 1)->where('del_state', '=', 0)
				->where('tapfiliate', '!=', '0')
				->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
                }
						})->get();

				if(!empty($result)) {


				foreach($result as $u) {

								$val='('.$u->name.') '.$u->first_name." ".$u->last_name;
								$json[] = ['id'=>$u->tapfiliate, 'text'=>$val];
								}
								echo json_encode($json);
								}
							}
			
		}



		public function search_users()
    {
        $data="";
                 if(!empty($_GET['q'])) {
$query ="SELECT ontra_demo_user_id,user_id FROM users WHERE ontra_demo_user_id like '" . $_GET['q'] . "%' OR NAME like '" . $_GET['q'] . "%' OR email like '" . $_GET['q'] . "%' AND ontra_demo_user_id IS NOT NULL  ORDER BY id LIMIT 0,20";
$result = DB::select($query);
if(!empty($result)) {

foreach($result as $usr) {
$json[] = ['id'=>$usr->user_id, 'text'=>$usr->ontra_demo_user_id];
}
echo json_encode($json);
        }
      }
    }


	
	public function testcode(){
			
			try {
				$account = Recurly_Account::get('7285618329161875');
				echo '<pre>';
				print_r($account);
				// $balance = $account->balance_in_cents->USD->amount_in_cents;
				// echo abs($balance/100);
				// print "Account: $account\n";
				} catch (Recurly_NotFoundError $e) {
				echo $e->getMessage();
				}
        
		}


   public function all_accounts(Request $request,$user_id){

	 $user = DB::table('users')->where('user_id',$user_id)->first();

		return view('multiauth::admin.all-accounts',['user'=>$user]);					 

	}

		public function getlistallaccounts(Request $request,$user_id)
    {
			//echo "yes";die;
		$offset=isset($request->offset)?$request->offset:'0';
        $limit=isset($request->limit)?$request->limit:'9999999999';

        if($limit=='All' )
        {
            $limit=9999999999;
        }
        $order=$request->order;
        if(!isset($request->sort))
        {
            $order='desc';
        }

        $sortString=isset($request->sort)?$request->sort:'S.No.';

        $search=isset($request->search)?$request->search:'';

        switch($sortString)
        {
            case 'S.No.':
                $sort = 'ontra_account.id';
                break;
                default:
                $sort = 'ontra_account.id';
        }

        $data=array();
        $rows=array();

        $columns=['ontra_account.account_id'];
		
		
        $posts = DB::table('ontra_account')
			->select('account_id','updated_at')
			->where('user_id', '=', $user_id)
            ->where(function ($query) use($search, $columns){
                if($search!='')
                {
                    $query->where($columns[0], 'like', '%'.$search.'%');
                    for($i=1;$i< count($columns);$i++)
                    {
                        $query->orWhere($columns[$i], 'like', '%'.$search.'%');
                    }
								}
								
						})
						->orderBy($sort, $order)->skip($offset)->take($limit)->get();
			
			

			$sno = $offset+1;
			$records = array();
				foreach($posts as $r => $row)
       			 {
					 $check = DB::table('AS_R_User')->where('Username',$row->account_id);

					 if($check->count() != 0){

						$ck = $check->first();

						if($ck->Status == 1){
							$status = 'Active';
						}else{
							$status = 'Inactive';
						}

						$checkbox = '<input type="checkbox" class="case" name="case" value="'.$row->account_id.'"/>';
					 }else{
						$status = 'Not found'; 
						$checkbox = '';
					 }
					$records['sno'] = $sno++;
					$records['checkbox'] = $checkbox;
					$records['post_time'] = date('d/m/Y',strtotime($row->updated_at));
					$records['account_id'] = $row->account_id;
					$records['status'] = $status;

					$data2[] = $records;

				}  


			$data['total']= count($posts);
			$data['rows']=$data2;
			
        return response()->json($data);

		}


		
		public function bulkdisable(Request $request){
		
			return view('multiauth::admin.bulk_disable');					 

		}
	
		
		function csvToArray($filename = '', $delimiter = ',')
		{
			if (!file_exists($filename) || !is_readable($filename))
				return false;

			$header = null;
			$data = array();
			if (($handle = fopen($filename, 'r')) !== false)
			{
				while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
				{
					if (!$header)
						$header = $row;
					else
						$data[] = array_combine($header, $row);
				}
				fclose($handle);
			}

			return $data;
		}

		public function postbulkdisable(Request $request){
			
			$bulk_type = $request->bulk_type;
			$csvfile = $request->file('csv_file');

			$extension = $csvfile->getClientOriginalExtension();
			// echo '<pre>';
			// print_r($customerArr);	 

			if($extension == 'csv'){

				
			$customerArr = $this->csvToArray($csvfile);		
			
				if(count($customerArr) != 0){

					if($bulk_type == 'all'){

						foreach($customerArr as $i => $cus){
	
							$email = $cus['Email'];

							$ck	= DB::table('users')->select('user_id')->where('email',$email);

							if($ck->count() > 0){
								
								$users = $ck->first();
									 
								$user_id = $users->user_id;
								
										/**Condition for disable all***/

											$old_ontra_accounts = DB::table('ontra_account')->select('account_id')->where('user_id',$user_id)->get();
											if(count($old_ontra_accounts) > 0){
												foreach($old_ontra_accounts as $oa => $old){
												    $username = $old->account_id;
													$status = '0';

														$check_first = DB::table('AS_R_User')->where('Username', $username)->count();
															if($check_first != 0){
																	$ut = new Utils();
																	$ut->SignalUser($username,$status);
															}

												}
											
											}

							}
						}

						return redirect()->back()->with('message','Accounts updated successfully')->with('status','success');	

				}elseif($bulk_type == 'partial'){

					foreach($customerArr as $i => $cus){
	
							$email = $cus['Email'];

							$ck	= DB::table('users')->select('user_id')->where('email',$email);

							if($ck->count() > 0){
								
								$users = $ck->first();

								$user_id = $users->user_id;
								
										/**Condtion for keep last 3**/
											$old_ontra_accounts = DB::table('ontra_account')->select('account_id')->where('user_id',$user_id)->count();

											if($old_ontra_accounts > 3){

												$get_olds = DB::table('ontra_account')->select('account_id')->where('user_id',$user_id)
												->orderBy('id','desc')->take($old_ontra_accounts)->skip(3)->get();

												foreach($get_olds as $oa => $old){
													$username = $old->account_id;
													$status = '0';

														$check_first = DB::table('AS_R_User')->where('Username', $username)->count();
															if($check_first != 0){
																	$ut = new Utils();
																	$ut->SignalUser($username,$status);
															}

												}
												
												
											}

							}
						}
						
						return redirect()->back()->with('message','Accounts updated successfully')->with('status','success');

				}

			
			}else{
				return redirect()->back()->with('message','Error the file is empty')->with('status','danger');
			}
		}else{
				return redirect()->back()->with('message','Invalid file type please upload .csv file')->with('status','danger');
		}

		}


	public function call_state($cat_id){ 
       //$cat_id = "Canada";
       // $ct = countries::where('name', $cat_id)->first();
       // print_r($ct->id);
        $results = states::where('country_id', $cat_id)->orderBy('name', 'ASC')->get();
        //print_r($results);
        //die;
        return \Response::json($results);
        //return view('attendance',['user' => $results]);
        
    }   

}
