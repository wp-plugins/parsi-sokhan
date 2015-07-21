<?php
add_action('wp_ajax_sb_add_new','parsisokhan_add_new');
add_action('wp_ajax_sb_change_status','parsisokhan_change_status');
add_action('wp_ajax_sb_delete_item','parsisokhan_delete_item');
add_action('wp_ajax_sb_edit_item','parsisokhan_edit_item');
function parsisokhan_add_new()
{
    global $wpdb;
    $teller=  esc_attr($_POST['teller']).' :';
    $content=  esc_attr($_POST['content']);
    $date=  current_time('mysql');
    $sql="INSERT INTO {$wpdb->prefix}parsi_sokhan (teller,content,status,date)VALUES('%s','%s','1','%d')";
    $res=$wpdb->query($wpdb->prepare( $sql,$teller ,$content,'1',$date ) );
    if($res)
    {
       die('اطلاعات با موفقیت ثبت گردید');
    }else{
        die('ثبت اطلاعات با مشکل مواجه گردید');
    }
    
}
function parsisokhan_change_status()
{
    global $wpdb;
    $set_status=null;
    $id=intval($_POST['id']);
    $status=intval($_POST['status']);
    if($status==1)
    {
        $set_status=0;
    }elseif($status==0){
        $set_status=1;
    }
    if($id!=0){
        $sql="UPDATE {$wpdb->prefix}parsi_sokhan SET status='%s' WHERE id=%d";
        $res=$wpdb->query( $wpdb->prepare( $sql,$set_status ,$id ) );
		echo " ";
        if($res)
        {
            die(1);
        }
        else
        {
            die(0);
        }
    }
	
            
}
function parsisokhan_delete_item(){
     global $wpdb;
	 echo " ";
    $id=intval($_POST['id']);
    if($id!=0)
    {
        $sql="DELETE FROM {$wpdb->prefix}parsi_sokhan WHERE id=%d";
        $res=$wpdb->query( $wpdb->prepare( $sql,$id ) );
        if($res)
        {
            die(1);
        }
        else
        {
            die(0);
        }
    }
    
}
function parsisokhan_edit_item(){
    global $wpdb;
    $id=$_POST['id'];
    $teller=  esc_attr($_POST['teller']);
    $content=  esc_attr($_POST['content']);
    if($teller!="" && $content!="")
    {
        $sql="UPDATE {$wpdb->prefix}parsi_sokhan SET teller='%s',content='%s' WHERE id=%d";
        $res=$wpdb->query( $wpdb->prepare( $sql,$teller ,$content,$id ) );
        if($res)
        {
            die('اطلاعات با موفقبت به روز رسانی شد');
        }
        else
        {
            die('اطلاعات تکراری یا خطایی رخ داده است');
        }
    }
    
}