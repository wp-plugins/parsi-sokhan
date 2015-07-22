<?php
class parsisokhan_Widget extends WP_Widget {

    function parsisokhan_Widget() {
        $widget_option = array('classname' => 'widget', 'description' => 'نمایش سخنان بزرگان');
        parent::WP_Widget('sokhane-bozorgan', 'پارسی سخن', $widget_option);
    }

    function widget_parsisokhan($args, $instance) {
        extract($args, EXTR_SKIP);
        $title = ($instance['title']) ? $instance['title'] : 'پارسی سخن';
        $default=($instance['default'])?$instance['default']:'هیچ سخنی در سیستم پیدا نشد.';
        global $wpdb;
        $sql="SELECT * FROM {$wpdb->prefix}parsi_sokhan WHERE status=1 ORDER BY rand() LIMIT 1";
        $result=$wpdb->get_row($sql);
        $teller=$result->teller;
        $content=$result->content;
        ?>
        <?php echo $before_widget ?>
        <?php echo $before_title . $title . $after_title ?>
        <p class="goyande-wi"><?php echo (!empty($teller))?$teller:""; ?></p>
        <p class="matn-wi"><?php echo !empty($content)?$content:$default; ?></p>
        <?php echo $after_widget ?>
        <?php  
        }
    
    function form($instance)
    {
        if(isset($instance['title']))
        {
            $title=$instance['title'];
        }
        else
        {
            $title="پارسی سخن";
        }
        if(isset($instance['default']))
        {
            $default=$instance['default'];
        }  else {
            $default="هیچ سخنی در سیستم ثبت نشده است";
        }
        
        ?>
        <p>عنوان ابزارک</p>
        <p><input style="width:100%" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title')?>" type="text" value="<?php echo esc_attr($title); ?>"></p>
        <p>متن پیش فرض :</p>
        <p><textarea style="width:100%;height:200px;" id="<?php echo $this->get_field_id('default') ?>" name="<?php echo $this->get_field_name('default')?>" type="text"><?php echo esc_attr($default); ?></textarea></p>
            <?php
    }
    function update_parsisokhan($new_instance, $old_instance)
    {
        $instance=array();
        $instance['title']=  strip_tags($new_instance['title']);
        $instance['default']=  strip_tags($new_instance['default']);
        return $instance;
    }

}
function parsisokhan_widget_init()
{
    register_widget("parsisokhan_Widget");
}
add_action('widgets_init', 'parsisokhan_widget_init');
?>
