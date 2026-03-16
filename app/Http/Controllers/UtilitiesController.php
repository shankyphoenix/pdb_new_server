<?php

namespace App\Http\Controllers;

use App\Models\Navigation;
use App\Models\User;
use Carbon\Carbon;

class UtilitiesController extends Controller
{


    public function navigation_old() {
        $menu = '[
   
    {
        "slug": "lead-management",
        "menu_type_id": 3,
        "report_level_id": "[1,3,125,126,127,124,4,5,114,115,116,117,118,120,121]",
        "has_duration": 1,
        "sort": "1",
        "class": "c-sidebar__icon feather icon-briefcase",
        "label": "Companies",
        "submenus": [
           
            {
                        "slug": "company_list2",
                        "menu_type_id": 1,
                        "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                        "has_duration": 0,
                        "sort": "0.1",
                        "class": null,
                        "label": "Companies",
                        "submenus": [
         
                            {
                                "slug": "company_list",
                                "menu_type_id": 1,
                                "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                                "has_duration": 0,
                                "sort": "0.1",
                                "class": null,
                                "label": "List View",
                                "submenus": [
                                ],
                                "dashnav": [],
                                "table_head": [],
                                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                                "period": [
                                    "PRIOR MONTH",
                                    "YEAR TO DATE",
                                    "TWELVE MONTH",
                                    "CUSTOMIZE"
                                ],
                                "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                                "page_type": "detail"
                            },{
                                "slug": "company_map",
                                "menu_type_id": 8,
                                "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                                "has_duration": 0,
                                "sort": "0.1",
                                "class": null,
                                "label": "Map View",
                                "submenus": [
                                ],
                                "dashnav": [],
                                "table_head": [],
                                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                                "period": [
                                    "PRIOR MONTH",
                                    "YEAR TO DATE",
                                    "TWELVE MONTH",
                                    "CUSTOMIZE"
                                ],
                                "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                                "page_type": "pos_map"
                            }
                        ],
                        "dashnav": [],
                        "table_head": [],
                        "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                        "period": [
                            "PRIOR MONTH",
                            "YEAR TO DATE",
                            "TWELVE MONTH",
                            "CUSTOMIZE"
                        ],
                        "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                        "page_type": "detail"
                    }
                ],
                "dashnav": [],
                "table_head": [],
                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,sup,dp,rep,md,accounting,cfo,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                "period": [
                    "PRIOR MONTH",
                    "YEAR TO DATE",
                    "TWELVE MONTH",
                    "CUSTOMIZE"
                ],
                "is_active": "management,rcm,ospc,ispc,drc,isp,dist,sup,ts,sp,rep,md,accounting,cfo,gm,am,dp",
                "page_type": "table"
            },
            {
                "slug": "contact-management",
                "menu_type_id": 3,
                "report_level_id": "[1,3,125,126,127,124,4,5,114,115,116,117,118,120,121]",
                "has_duration": 1,
                "sort": "1",
                "class": "c-sidebar__icon feather icon-briefcase",
                "label": "Contacts",
                "submenus": [
                   
                    {
                        "slug": "contact_list",
                        "menu_type_id": 1,
                        "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                        "has_duration": 0,
                        "sort": "0.1",
                        "class": null,
                        "label": "List View",
                        "submenus": [
                        ],
                        "dashnav": [],
                        "table_head": [],
                        "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                        "period": [
                            "PRIOR MONTH",
                            "YEAR TO DATE",
                            "TWELVE MONTH",
                            "CUSTOMIZE"
                        ],
                        "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                        "page_type": "detail"
                    }
                ],
                "dashnav": [],
                "table_head": [],
                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,sup,dp,rep,md,accounting,cfo,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                "period": [
                    "PRIOR MONTH",
                    "YEAR TO DATE",
                    "TWELVE MONTH",
                    "CUSTOMIZE"
                ],
                "is_active": "management,rcm,ospc,ispc,drc,isp,dist,sup,ts,sp,rep,md,accounting,cfo,gm,am,dp",
                "page_type": "table"
            },
            {
                "slug": "supplier-management",
                "menu_type_id": 3,
                "report_level_id": "[1,3,125,126,127,124,4,5,114,115,116,117,118,120,121]",
                "has_duration": 1,
                "sort": "1",
                "class": "c-sidebar__icon feather icon-briefcase",
                "label": "Suppliers",
                "submenus": [
                   
                    {
                        "slug": "supplier_list2",
                        "menu_type_id": 1,
                        "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                        "has_duration": 0,
                        "sort": "0.1",
                        "class": null,
                        "label": "Suppliers",
                        "submenus": [
         
                            {
                                "slug": "supplier_list",
                                "menu_type_id": 1,
                                "report_level_id": "[1,3,125,126,127,4,5,114,115,116,117,118,119,120,121,124]",
                                "has_duration": 0,
                                "sort": "0.1",
                                "class": null,
                                "label": "List View",
                                "submenus": [
                                ],
                                "dashnav": [],
                                "table_head": [],
                                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                                "period": [
                                    "PRIOR MONTH",
                                    "YEAR TO DATE",
                                    "TWELVE MONTH",
                                    "CUSTOMIZE"
                                ],
                                "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                                "page_type": "detail"
                            }
                        ],
                        "dashnav": [],
                        "table_head": [],
                        "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,dp,rep,md,accounting,cfo,sup,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                        "period": [
                            "PRIOR MONTH",
                            "YEAR TO DATE",
                            "TWELVE MONTH",
                            "CUSTOMIZE"
                        ],
                        "is_active": "management,rsm,csr,rcm,ospc,ispc,drc,dist,sup,ts,sp,rep,md,accounting,cfo,gm,ispm,am,dp,isp",
                        "page_type": "detail"
                    }
                ],
                "dashnav": [],
                "table_head": [],
                "type": "management,rcm,csr,dist,ts,rsm,sp,am,cs,sup,dp,rep,md,accounting,cfo,gm,ispm,am,dp,isp,ospc,ispc,drc,none",
                "period": [
                    "PRIOR MONTH",
                    "YEAR TO DATE",
                    "TWELVE MONTH",
                    "CUSTOMIZE"
                ],
                "is_active": "management,rcm,ospc,ispc,drc,isp,dist,sup,ts,sp,rep,md,accounting,cfo,gm,am,dp",
                "page_type": "table"
            },
            {
                "mailtype": {
                    "management": "support@rittal-interlynx.com",
                    "rcm": "support@rittal-interlynx.com",
                    "dist": "distributor.support@rittal-interlynx.com",
                    "sp": "distributor.support@rittal-interlynx.com",
                    "am": "support@rittal-interlynx.com",
                    "cs": "support@rittal-interlynx.com",
                    "dp": "distributor.support@rittal-interlynx.com",
                    "rep": "rep.support@rittal-interlynx.com",
                    "md": "support@rittal-interlynx.com",
                    "accounting": "support@rittal-interlynx.com",
                    "cfo": "support@rittal-interlynx.com",
                    "gm": "support@rittal-interlynx.com",
                    "ispm": "support@rittal-interlynx.com",
                    "isp": "support@rittal-interlynx.com",
                    "ospc": "support@rittal-interlynx.com",
                    "ispc": "support@rittal-interlynx.com",
                    "drc": "support@rittal-interlynx.com",
                    "none": null
                }
            },
            {
                "walkthrough": []
            }
          ]';
    return  $menu;
    }

    public function navigation() {    

        $manager = auth()->user();

        //if($manager->user_type == 1) { echo $this->navigation_old(); return; }

        if(!$manager) { return json_encode('{"status_code":"Not authorized."'); };
        
        $data = Navigation::table();

        $menu = json_decode($data);  

        return json_encode($menu);        
    }

    public function navigation_logo() {

        return response()->json([
            
            'logo' => [
                0 => [
                    'imgPath' => config("pdb.app_url").'/images/' . 'interlynx-logo.png',
                    'website' => ""
                ],
            ],
            'supportEmail' => "supportEmail@a.com",
            'systemName' => "PDB",
            'fullSystemName' => "",
            'current_lang' => "en",
            'lang_list' => ["en","es"],
            'language' => null,
            'hide_logo' => true,
            'suppliers' => []
        ])->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

   function system_config(){
        
   }

   function homepage(){
        return [];
   }

}