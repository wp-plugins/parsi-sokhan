jQuery( document ).ready( function ( $ )
{
    var sen_id=null;
    var sen_teller=null;
    var sen_content=null;
    $('#sb_admin_add_new').click(function(){
        $('#sb_add_new').slideDown(300);
        return false;
    });
    $('#sb_add_cancel').click(function(){
        $('#sb_add_new_frm').trigger('reset');
         $('#sb_add_new').slideUp(300);
    });
    $('#sb_add_new_frm').on('submit',function(){
       var teller=$(this).find('input[type=text]');
       var content=$(this).find('textarea');
        var sendbtn=$('#sb_add_submit');
        var canbtn=$('#sb_add_cancel');
         var msg=$('#sb_add_new').find('#updated');
         if(teller.val()=="" || content.val()=="")
             {
                 alert('لطفا اطلاعات لازم را وارد نمایید.');
                 return false;
             }
            sendbtn.val('در حال ارسال ...');
            sendbtn.attr('disabled','disabled');
            canbtn.attr('disabled','disabled');
            teller.attr('disabled','disabled');
            content.attr('disabled','disabled');
             $.ajax({
                 url:sb_ajax.ajaxurl,
                 type:'post',
                 data:{
                     action:'sb_add_new',
                     teller:teller.val(),
                     content:content.val()
                 },
                 success:function(response){
                  alert(response);
                 
                   sendbtn.val('اضافه کردن جمله جدید');
                   canbtn.val('بستن');
                   sendbtn.removeAttr('disabled');
                   canbtn.removeAttr('disabled');
                   teller.removeAttr('disabled');
                   content.removeAttr('disabled');
                   $('#sb_add_new_frm').trigger('reset');
                 }
             });
         return false;
     });
    $('#sb_item_chk').change(function(){
    $('input[type="checkbox"]').attr('checked',this.checked);
     });
    $('#sb_actions_submit').click(function(){
         if($('#sb_actions').val()==-1)
              return false;
     });
    $('#sb_srch_submit').click(function(){
         if($('#sb_srch_q').val()=="")
             {
               alert('برای جستجو  باید عبارت مورد نظر را وارد نمایید');
               return false;
             }
     });
     $('.change_status').on('click',function(){
         var el=$(this);
         var status=$(this).data('status');
         var id=$(this).data('id');
         el.attr('disabled','disabled');
         $.ajax({
             url:sb_ajax.ajaxurl,
             type:'post',
             data:{
                 action:'sb_change_status',
                 id:id,
                 status:status
             },
             success:function(data){
                 if(data)
                     {
                        el.html(change_status(status));
                        el.data('status',(status==1?0:1));
                     }
                     el.removeAttr('disabled');
             },
         });
 return false;
     })
    $('.sb_delete_item').on('click', function() {
        if (!confirm('برای حذف این آیتم مطمئن هستید؟'))
        {
            return false;
        }
        var el = $(this);
        var id = el.data('id');
        el.attr('disabled', 'disabled');
        $.ajax({
            url: sb_ajax.ajaxurl,
            type: 'post',
            data: {
                action: 'sb_delete_item',
                id: id,
            },
            success: function(data) {
                if (data)
                {
                    el.closest('tr').remove();
                }
                el.removeAtt('disabled');
            }
        });
        return false;
    });
    $('.sb_edit_item').on('click',function(){
        var off=jQuery(this).offset();
        var id=jQuery(this).data('id');
        var teller=jQuery(this).parent().prev().prev().prev();
        var content=jQuery(this).parent().prev().prev();
        jQuery('#sb_item_edit_wrap').find('#sb_edit_item_teller').val(teller.text());
        sen_id=id;
        jQuery('#sb_item_edit_wrap').find('textarea').val(content.text());
        jQuery('#sb_item_edit_wrap').css({'top':off.top,'left':off.left}).slideDown(200);
        sen_teller=teller;
        sen_content=content;
        return false;
    });
    $('#sb_edit_reg').on('click',function(){
        var wrap=$('#sb_item_edit_wrap');
        var message=wrap.find('#sb_edit_result');
        var teller=wrap.find('#sb_edit_item_teller');
        var content=wrap.find('#sb_edit_item_content');
        var rbtn=$('#sb_edit_reg');
        var cbtn=$('#sb_edit_can');
        if(teller.val()=="" || content.val()=="")
            {
                return false;
            }
        teller.prop('disabled','disabled');
        content.prop('disabled','disabled');
        rbtn.prop('disabled','disabled');
        cbtn.prop('disabled','disabled');
        message.html('لطفا صبر کنید ...').slideDown(200);
        $.ajax({
            url:sb_ajax.ajaxurl,
            type:'post',
            data:{
                action:'sb_edit_item',
                id:sen_id,
                teller:teller.val(),
                content:content.val(),
            },
            success:function(data){
                
                message.html(data);
                teller.removeAttr('disabled');
                content.removeAttr('disabled');
                rbtn.removeAttr('disabled');
                cbtn.removeAttr('disabled');
                cbtn.text('بستن');
                sen_teller.text(teller.val());
                sen_content.text(content.val());
                
               
            }    
        });
                
    });
     $('#sb_edit_can').on('click',function(){
         var message=$('#sb_item_edit_wrap').find('#sb_edit_result');
         message.html("").hide();
         $('#sb_item_edit_wrap').slideUp(300);
    });
});
function change_status(stat)
{
    if(stat)
        {
            return '<span class="sb_inactive">غیر فعال</span>';
        }
        else
            {
                return '<span class="sb_active">فعال</span>';
            }
}