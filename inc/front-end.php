<?php
require_once ('front-end-function.php');
require_once ('pagination.php');
function parsisokhan_page_content() {
    global $wpdb;
    $where="";
    $where.=parsisokhan_check_query();  
    if(isset($_POST['sb_srch_submit']))
    {
        $where.=parsisokhan_search($where);
    } elseif(isset ($_POST['sb_actions_submit'])) {
        parsisokhan_actions();
    }
    $per_page=get_paginate_count_parsisokhan();
    $page = (isset($_GET['paged']) && !empty($_GET['paged']) && intval($_GET['paged'])!=0) ? esc_attr($_GET['paged']) : 1;
     if ($page != 1 && !isset($_GET['_wpnonce'])) {
        $nonce = $_REQUEST['_wpnonce'];
        parsisokhan_verify_nonce($nonce);
    }
    $sql="SELECT * FROM {$wpdb->prefix}parsi_sokhan {$where} ORDER BY date";
    $result=$wpdb->get_results(trim($sql));
    $total_count=count($result);
    $pagination=new Pagination_parsisokhan($page, $per_page, $total_count);
    $sql="SELECT * FROM {$wpdb->prefix}parsi_sokhan {$where} ORDER BY date LIMIT {$per_page} OFFSET {$pagination->offset()}";
    $sb_res=$wpdb->get_results($sql);
    $sb_count=count($sb_res);
    ?>
<div class="wrap">
    <p><div class="icon-page" id="icon32"></div><h2>پارسی سخن</h2></p>
    <ul id="sb_admin_link" class="subsubsub">
        <li><a id="sb_admin_add_new" href="#" >اضافه کردن جمله جدید</a></li> |
        <li><a id="sb_admin_add_new" href="<?php echo admin_url('admin.php?page=sb_page'); ?>" >همه جملات</a></li> |
        <li><a href="<?php echo add_query_arg(array('status'=>'active','_wpnonce'=>  wp_create_nonce())); ?>" >جملات فعال</a></li> |
        <li><a href="<?php echo add_query_arg(array('status'=>'inactive','_wpnonce'=>  wp_create_nonce())); ?>" >جملات غیر فعال</a></li>
    </ul>
<div class="clear"></div>
<div id="sb_add_new">
    <div id="updated"></div>
    <form id="sb_add_new_frm" method="post" action="">
        <p> <label for="sb_new_teller">گوینده :
            <input name="sb_new_teller" type="text" size="40">
        </label></p>
        <p><label for="sb_new_content">متن :
            <textarea name="sb_new_content" ></textarea>
        </label></p>
        <p><input class="button action" type="submit" id="sb_add_submit" name="sb_add_submit" value="اضافه کردن">
        <input class="button action" type="reset" id="sb_add_cancel" value="انصراف"></p>
    </form>
</div>
<form id="sb_list_frm" action="" method="post">
    <div id="sb_actions_list">
        <lable for="sb_actions">عملیات :
            <select id="sb_actions" name="parsisokhan_actions" >
                <option value="-1" selected>انتخاب کنید ...</option>
                <option value="delete_all" >حذف همه جمله ها</option>
                <option value="delete_selected" >حذف جمله های انتخاب شده</option>
                <option value="active_selected" >فعال کردن جمله های انتخاب شده</option>
                <option value="inactive_selected" >غیر فعال کردن جمله های انتخاب شده</option>
            </select>
            <input id="sb_actions_submit" name="sb_actions_submit" type="submit" class="button action" value="اجرا" >
        </lable>
        <label style="float:left;margin-left:20px;" for="sb_srch">جستجو بر اساس :
            <select name="sb_srch">
                <option selected value="teller">گوینده سخن</option>
                <option value="content" >متن سخن</option>
            </select>
            <input id="sb_srch_q" type="text" class="input" size="30" maxlength="60" name="sb_srch_q" placeholder="متن یا کلمه مورد نظر شما">
            <input id="sb_srch_submit" type="submit" name="sb_srch_submit" value="جستجو" class="button action">
        </label>
    </div>
<table  class="wp-list-table widefat fixed" cellspacing="0">
    <caption style="margin: 10px 0;font-weight:bold" >لیست سخنان</caption>
    <tr style="background-color: #C2E8F7;">
        <th style="width:5%;text-align: center" ><input id="sb_item_chk" type="checkbox" style="margin: 0px -6px 0px 0px;"></th>
        <th style="width:15%;text-align: center;font-weight: bold;" >گوینده</th>
        <th style="width:60%;text-align: center;font-weight: bold;" > متن</th>
        <th  style="width:10%;text-align: center;font-weight: bold;" >وضعیت</th>
        <th style="width:10%;text-align: center;font-weight: bold;" >عملیات</th>
    </tr>
    <?php if($sb_count){  ?>
        <?php foreach ($sb_res as $sb): ?>
        <tr>
            <td style="width:5%;text-align: center" ><input type="checkbox" name="sb_item_checked[]" value="<?php echo $sb->id; ?>"></td>
            <td style="width:15%;text-align: center" ><?php echo $sb->teller; ?></td>
            <td style="width:60%;text-align: center"><?php echo $sb->content; ?></td>
            <td style="width:10%;text-align: center" ><a class="change_status" data-status="<?php echo $sb->status; ?>" data-id="<?php echo $sb->id; ?>"  href="#" ><?php echo parsisokhan_get_status($sb->status); ?></a></td>
            <td style="width:10%;text-align: center" ><a class="sb_delete_item" href="#" data-id="<?php echo $sb->id; ?>" >حذف</a> | <a class="sb_edit_item" href="#" data-id="<?php echo $sb->id; ?>">ویرایش</a></td>
        </tr>
        <?php endforeach; ?>
    <?php }else{ ?>
        <tr>
             <td colspan="4" style="font-weight: bold;text-align: center;color:#cc0000" >هیچ جمله ای یافت نشد</td>
        </tr>
    <?php } ?>
</table>
</form>
<div class="pagination">
            <?php
            if ($pagination->total_page() > 1) {
                $count=($pagination->total_page() <= 20)?$pagination->total_page():20;
                $nonce = wp_create_nonce();
                if ($pagination->has_previous_page()) {
                    echo '<a href="' . add_query_arg(array("paged" => $pagination->previous_page(), '_wpnonce' => $nonce)) . '">صفحه قبل</a>';
                }

                for ($i =$count; $i >= 1; $i--) {
                    if ($i == $page) {
                        echo '<span class="current" >' . $i . '</span>';
                    } else {
                        echo '<a href="' . add_query_arg(array("paged" => $i, '_wpnonce' => $nonce)) . '">' . $i . '</a>';
                    }
                }
                if($page>=20)
                {
                    echo '<span >...</span>';
                    if($page==$pagination->total_page())
                    {
                        echo '<span class="current" >'.$pagination->total_page().'</span>';
                    }else
                    {
                        echo '<a href="' . add_query_arg(array("paged" => $pagination->total_page(), '_wpnonce' => $nonce)) . '">' . $pagination->total_page() . '</a>';
                    }
                }
                
                if ($pagination->has_next_page()) {
                    echo '<a href="' . add_query_arg(array("paged" => $pagination->next_page(), '_wpnonce' => $nonce)) . '">صفحه بعد</a>';
                }
            }
            ?>
    </div>
<div id="sb_item_edit_wrap" data-id="0" >
    <div id="sb_edit_result"></div>
    <p><input class="input" type="text"  id="sb_edit_item_teller" size="40" maxlength="40" ></p>
    <textarea id="sb_edit_item_content" ><?php ?></textarea>
    <p><button class="button-primary" data-id="" id="sb_edit_reg" >ذخیره </button>&nbsp;<button class="button-primary" id="sb_edit_can">انصراف</button></p>
</div>
</div>
    <?php

}
