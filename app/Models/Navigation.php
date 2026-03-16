<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;
use DB;

class Navigation extends BaseModel
{
    use HasFactory;

    private $system_menu;
    private $system_menu_parent;

    protected $table ="nav_menu";

 private function getMenuItems($parentId = null)
{
    $user_type = auth()->user()->user_type;

    $user_type_access = "";
    if(!in_array($user_type, [1,2])) {

        $qu = DB::select("WITH RECURSIVE menu_tree AS (
                SELECT id, parent_menu_id, label
                FROM `pdb_nav_menu`
                WHERE id IN (SELECT menu_id FROM `pdb_user_type_menu_access` WHERE user_type_id = ?)
            
                UNION ALL
            
                SELECT m.id, m.parent_menu_id, m.label
                FROM `pdb_nav_menu` m
                JOIN menu_tree mt ON m.parent_menu_id = mt.id
            )
            SELECT GROUP_CONCAT(id) as menu_ids
            FROM menu_tree"
        , [$user_type]);

        if(empty($qu) || empty($qu[0]->menu_ids)) {
            $user_type_access = " and id in (0)";
        } else {
            $user_type_access = "and id in (
                ".$qu[0]->menu_ids. "
            )";
        }

    }

    // 1. Fetch menu items from `nav_menu` not overridden by `nav_menu_system`
    $defaultMenus = \DB::select("
        SELECT 
            'main' as menu_level,id, parent_menu_id, slug, menu_type_id, report_level_id, has_duration, sort,
            class, modal_label, label, dashnav, table_head, type, period, is_active,
            page_type, is_locked, report_short_hand, default_date, hide_second_level,
            activate_route_show_menu, activate_route_options, notification_content,
            date_options, date_data, filter_api, sidebar_icon, export_single, export_all,
            filter_dropdown, disable_date, third_level_header,server_side_sorting, row_submit 
        FROM ".DB::getTablePrefix()."nav_menu
        WHERE is_enabled = 1 $user_type_access and parent_menu_id = ? 
        
        ORDER BY parent_menu_id,sort asc
    ", [$parentId]);

    /*// 2. Fetch overridden menu items from `nav_menu_system`
    $systemMenus = \DB::select("
        SELECT 
            'sub' as menu_level,menu_id as id, parent_menu_id, slug, menu_type_id, report_level_id, has_duration, sort,
            class, modal_label, label, dashnav, table_head, type, period, is_active,
            page_type, is_locked, report_short_hand, default_date, hide_second_level,
            activate_route_show_menu, activate_route_options, notification_content,
            date_options, date_data, filter_api, sidebar_icon, export_single, export_all,
            filter_dropdown, disable_date, third_level_header
        FROM nav_menu_system
        WHERE system_id = ? AND parent_menu_id = ?
        ORDER BY sort asc
    ", [$systemId, $parentId]);*/

    // Merge results
    $menu = array_merge($defaultMenus, []);

    usort($menu,function($a,$b){ return (float)$a->sort <=> (float)$b->sort; }); 


    // Process each menu item recursively
    foreach ($menu as &$item) {

        if(empty($item->report_short_hand)) { unset($item->report_short_hand); }
        if(empty($item->default_date)) { unset($item->default_date); }
        if(empty($item->hide_second_level)) { unset($item->hide_second_level); }
        if(empty($item->activate_route_show_menu)) { unset($item->activate_route_show_menu); }
        if(empty($item->activate_route_options)) { unset($item->activate_route_options); }
        if(empty($item->notification_content)) { unset($item->notification_content); }
        if(empty($item->date_options)) { unset($item->date_options); }
        if(empty($item->date_data)) { unset($item->date_data); }
        if(empty($item->filter_api)) { unset($item->filter_api); }
        if(empty($item->sidebar_icon)) { unset($item->sidebar_icon); }
        if(empty($item->export_single)) { unset($item->export_single); }
        if(empty($item->export_all)) { unset($item->export_all); }
        if(empty($item->filter_dropdown)) { unset($item->filter_dropdown); }
        if(empty($item->disable_date)) { unset($item->disable_date); }
        if(empty($item->third_level_header)) { unset($item->third_level_header); }
        if(empty($item->tab_icon)) { unset($item->tab_icon); }


        if (!empty($item->activate_route_options    )) {
            $item->activate_route_options    = json_decode($item->activate_route_options, true); // or false for object
        }
        if (!empty($item->date_data     )) {
            $item->date_data = json_decode($item->date_data, true); // or false for object
        }
        if (!empty($item->filter_dropdown)) {
            $item->filter_dropdown = json_decode($item->filter_dropdown, true); // or false for object
        }
        if (!empty($item->date_options)) {
            $item->date_options = json_decode($item->date_options, true); // or false for object
        }

        // Get submenus
        if($item->menu_level != "sub") {
            $item->submenus = $this->getMenuItems($item->id);
        } else {
            continue;
        }        

        // Prepare or clean fields
        $item->dashnav = [];
        $item->table_head = [];
        $item->period = [];

        unset($item->parent_menu_id); // if you want to hide `id` from output
    }

    return $menu;
}


    public function scopeTable($query)
    {

        

        $menuTree = $this->getMenuItems(0);

        $obj = new stdClass();
        $obj->management = 'support@interlynx.com';
        $obj->rcm = 'support@rittal-interlynx.com';
        $obj->dist = 'distributor.support@rittal-interlynx.com';
        $obj->sp = 'distributor.support@rittal-interlynx.com';
        $obj->am = 'support@rittal-interlynx.com';
        $obj->cs = 'support@rittal-interlynx.com';
        $obj->dp = 'distributor.support@rittal-interlynx.com';
        $obj->rep = 'rep.support@rittal-interlynx.com';
        $obj->md = 'support@rittal-interlynx.com';
        $obj->accounting = 'support@rittal-interlynx.com';
        $obj->cfo = 'support@rittal-interlynx.com';
        $obj->gm = 'support@rittal-interlynx.com';
        $obj->ispm = 'support@rittal-interlynx.com';
        $obj->isp = 'support@rittal-interlynx.com';
        $obj->ospc = 'support@rittal-interlynx.com';
        $obj->ispc = 'support@rittal-interlynx.com';
        $obj->drc = 'support@rittal-interlynx.com';



        $obj2 = new stdClass();
        $obj2->mailtype = $obj;

        $obj3 = new stdClass();
        $obj3->walkthrough = [];


        $menuTree[] = $obj2;
        $menuTree[] = $obj3;

        
       

        return json_encode($menuTree);
    }


    public function scopeAuth($query)
    {
        if(request()->header('SystemLang') && request()->header('SystemLang') != 'en' && request()->header('SystemLang') !="") {
            $query->select("navigation_".request()->header('SystemLang')." as navigation");
        } else {
            $query->select("navigation");            
        }
        return $query->where('system_id', auth()->user()->system_id);
    }
}
