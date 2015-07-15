<?php
function parsisokhan_get_status($status)
{
    $status=intval($status);
    if($status)
    {
        return '<span class="sb_active" >فعال</span>';
    }
    else
    {
         return '<span class="sb_inactive" >غیر فعال</span>';
    }
}
function parsisokhan_check_query()
{
    
    $query="";
    if(isset($_GET['status']) && !empty($_GET['status']) && !empty($_GET['_wpnonce']))
    {
        $nonce=$_GET['_wpnonce'];
        parsisokhan_verify_nonce($nonce);
        $status=$_GET['status'];
        switch ($status)
        {
            case 'active':$query="WHERE status=1";
                break;
            case 'inactive':$query="WHERE status=0";
                break;
        }
        
    }
    return $query;
}
function parsisokhan_verify_nonce($nonce)
{
    if(!wp_verify_nonce($nonce))
    {
        wp_die('درخواست شما معتبر نمی باشد');
    }
}
function parsisokhan_search($where)
{
    global $wpdb;
   
   $sb_type= esc_attr($_POST['sb_srch']) == 'teller' ? 'teller' : 'content';
   $sb_q=  esc_attr($_POST['sb_srch_q']);
   if($where=="")
   {
    return $wpdb->prepare("WHERE $sb_type LIKE '%%%s%%' ",$sb_q);
   }else
   {
       return $wpdb->prepare(" AND $sb_type LIKE '%%%s%%' ",$sb_q );
   }
   
    
}
function parsisokhan_actions(){
    $sb_action=$_POST['sb_actions'];
    switch ($sb_action)
    {
        case 'delete_all':parsisokhan_delete_all();
            break;
        case 'delete_selected':parsisokhan_delete_selected();
            break;
        case 'active_selected':parsisokhan_active_selected();
            break;
        case 'inactive_selected':parsisokhan_inactive_selected();
            break;
    }
}
function parsisokhan_delete_all(){
    global $wpdb;
    $sql="DELETE FROM {$wpdb->prefix}sb WHERE 1";
    $wpdb->query($sql);
}
function parsisokhan_delete_selected(){
   global $wpdb;
   $ids=$_POST['sb_item_checked'];
   if(count($ids)!=0){
       foreach ($ids as $id) {
       $sql="DELETE FROM {$wpdb->prefix}sb WHERE id='%d'";
       $wpdb->query( $wpdb->prepare( $sql,$id ) );
   }
   }
   
}
function parsisokhan_active_selected()
{
      global $wpdb;
    $ids = $_POST['sb_item_checked'];
    if (count($ids) != 0) {
        foreach ($ids as $id) {
            $sql = "UPDATE {$wpdb->prefix}sb SET status='1' WHERE id='%d'";
            $wpdb->query( $wpdb->prepare( $sql,$id ) );
        }
		echo " ";
    }
}
function  parsisokhan_inactive_selected(){
    global $wpdb;
    $ids = $_POST['sb_item_checked'];
    if (count($ids) != 0) {
        foreach ($ids as $id) {
            $sql = "UPDATE {$wpdb->prefix}sb SET status='0' WHERE id='%d'";
            $wpdb->query( $wpdb->prepare( $sql,$id ) );
        }
		echo " ";
    } 
}
function get_paginate_count_parsisokhan()
{
    $count=  get_option('sb_paginate_count');
    if(isset($count))
    {
        return $count;
    }
 else {
        return 15;
    }
}
