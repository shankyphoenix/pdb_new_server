<?php

namespace App\Http\Controllers;

use App\Library\CustomMail;
use App\Models\System;
use App\Models\User;
use App\Models\Whitelist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use DB;

class TestController extends Controller
{
    function calculate() {
         $data = System::where("is_synced",1)
            ->where("is_active",1)
            
            ->get();       

        $duns = 0;
        $without_duns = 0;
        $records = [];

        foreach ($data as $key => $value) {
            $rawResponse = @file_get_contents($value->system_url."/get_company_info");
            $response = json_decode($rawResponse,true);
            $duns += (isset($response['duns'])) ? $response['duns'] : 0;
            $without_duns += (isset($response['without_duns'])) ? $response['without_duns'] : 0;
            $records[] = [
                            "system_name" => (isset($response['system_name'])) ? $response['system_name'] : "Unknown",
                            "system_id" => (isset($response['system_id'])) ? $response['system_id'] : 0,
                          "duns" => (isset($response['duns'])) ? $response['duns'] : 0,
                          "without_duns" => (isset($response['without_duns'])) ? $response['without_duns'] : 0 ];            
        }


        echo "<table width='100%' border='1'>";
            echo "<tr><th>ID</th><th>System Name</th><th>Duns</th><th>Without Duns</th></tr>";
        foreach ($records as $key => $value) {
            echo "<tr><td>".$value['system_id']."</td><td>".$value['system_name']."</td><td align='right'>".$value['duns']."</td><td  align='right'>".$value['without_duns']."</td></tr>";
        }
            echo "<tr><th></th><th> Total </th><th  align='right'>".$duns."</th><th  align='right'>".$without_duns."</th></tr>";
        echo "</table>";


        
    }

 
    function authorize_ip() {

        $request = request();
        $show_otp = false;
        $error_message = "";
        $ip = $request->ip();
        $email = "";
        $error_message = "";  
        // dd(request()->all());
        if($request->has('otp') && $request->has('user_id') && $request->otp != "" && $request->user_id != "") {

             $user = User::with('user_type')
             ->where("id",$request->user_id)->first();
            // dd($user->user_type);
            // Get all menu slug paths for this user_type
            $menuSlugs = $this->getUserTypeMenuSlugs($user->user_type);
            // dd($menuSlugs); // Show all slug paths for the user_type

            $record = Whitelist::where("user_id",$user->id)
                        ->where("otp",$request->otp)
                        ->where("ip",$ip)
                        ->first();

                        

            if($record) {

                Whitelist::updateOrCreate(
                    ['user_id' => $user->id, "ip" => $ip],
                    ['is_active' => 1]
                );
                
                //$url = config("pdb.report_url").'/#/lead-management/company_list?cpath=';
                $url = config("pdb.report_url2").$menuSlugs[0].'?cpath=';                

                if($user->user_type == 3) {
                    // $url = config("pdb.report_url2").'/threshold/threshold_mfg?cpath=';                    
                }

              //  echo $user->createToken('report', ['*'], Carbon::now()->addDays(9))->plainTextToken;

                $url .= base64_encode(json_encode([
                    "hash" => $user->createToken('report', ['*'], Carbon::now()->addDays(9))->plainTextToken, //api token
                    "start" => date("Y-m-d"),
                    "end" => date('Y-m-d', strtotime("+7 days")),
                    "filters" => [],            
                    "email" => $user->email,
                    "role" => "MANAGEMENT",
                    "otp_email" => $user->email,
                    "otp" => [
                        //'quote-management' => "true",
                        //'rebates' => "true",
                    ],
                    "services" => "companies,contacts,suppliers,productpricing,territory,quotethreshold,supplier_management,lead_source,dedupe",
                    "can" => [
                        "export"
                    ],
                    "has" => [],
                    "alternate_date" => date('Y-m-d'),
                    "feature_list" => "Bookmark,SaveSearch,Export,PostExport",
                    "message" => "Data retrieved successfully"            
                ]));


                
                return redirect()->to($url);               

                

            } else  {

                $show_otp = true; 
                $error_message = "OTP mismatch.";  
                return view('authorize_screen',[
                            "ip" => $ip,
                            "user_id" => $user->id,
                            "show_otp" =>  $show_otp,
                            "email" =>  $email,
                            "error_message" =>  $error_message
                            ]);
            }

             


        } elseif($request->has('email') && $request->email != "") {
            $email = $request->email;

            $user = User::where("email",$email)->first();


            
            if($user) {
                $show_otp = true;   

                $otp  = rand(111111,999999);

                Mail::raw('Your One-Time Password (OTP) to access the  Admin Panel is '.$otp, function ($message) use($email, $otp) {
                    $message->from('pdb@interlynxsystems.com', 'PDB');
                    $message->to($email);
                    $message->subject('OTP - PDB Admin');
                });

                

                Whitelist::updateOrCreate(
                    ['user_id' => $user->id, "ip" => $ip],
                    ['user_id'=> $user->id,'otp' => $otp]
                );
                return view('authorize_screen',[
                                        "ip" => $ip,
                                        "user_id" => $user->id,
                                        "show_otp" =>  $show_otp,
                                        "email" =>  $email,
                                        "error_message" =>  $error_message
                                        ]);

            } else {

                return redirect()->back()->with('error', 'Email not found on our database.');
            }
            
        } else {
                return view('authorize_screen',[
                                        "ip" => $ip,
                                        "show_otp" =>  $show_otp,
                                        "email" =>  $email,
                                        "error_message" =>  $error_message
                                        ]);

        }

        
        
    
       
    }

    /**
     * Get all menu slug paths for a given user_type_id
     */
    public function getUserTypeMenuSlugs($userTypeId)
    {
        // Step 1: Get all menu_ids for this user_type
        $menuIds = DB::table('user_type_menu_access')
            ->where('user_type_id', $userTypeId)
            ->pluck('menu_id');

        if(in_array($userTypeId, [1,2])) {
            // For Admin and Super Admin, get all menu IDs
            $menuIds = [2];
        }
        
        // Step 2: For each menu_id, build the full slug path
        $slugPaths = [];
        foreach ($menuIds as $menuId) {
            $slugPaths[] = $this->buildMenuSlugPath($menuId);
        }

        return $slugPaths;
    }

    /**
     * Recursively build the slug path for a menu_id
     */
    private function buildMenuSlugPath($menuId)
    {
        $menu = DB::table('nav_menu')->where('id', $menuId)->first();
        if (!$menu) {
            return '';
        }
        // If parent_id exists, recursively get parent slug
        if ($menu->parent_menu_id) {
            $parentSlug = $this->buildMenuSlugPath($menu->parent_menu_id);
            return rtrim($parentSlug, '/') . '/' . $menu->slug;
        } else {
            return '/' . $menu->slug;
        }
    }

    // Example usage inside authorize_ip() after you get $user:
    // $menuSlugs = $this->getUserTypeMenuSlugs($user->user_type['id']);
    // dd($menuSlugs); // This will show all slug paths for the user_type
}
