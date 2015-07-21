<?php
function sokhane_jadid_func() {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}parsi_sokhan WHERE status=1 ORDER BY rand() LIMIT 1";
        $result = $wpdb->get_row($sql);
        $teller = $result->teller;
        $content = $result->content;
        ?>
          <p id="sb_teller" ><?php echo (!empty($teller)) ? $teller.' ' : ""; ?></p>
          <p id="sb_content" ><?php echo!empty($content) ? $content : ""; ?></p>
        <?php
}
function m_sokhane_jadid_func(){
         global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}parsi_sokhan WHERE status=1 ORDER BY rand() LIMIT 1";
        $result = $wpdb->get_row($sql);
        $teller = $result->teller;
        $content = $result->content;
        ?>
          <div class="kadr">
           <marquee style="padding:5px;" scrollamount="1" direction="right" >
          <span class="goyande"><?php echo (!empty($teller)) ? $teller.' ' : " "; ?></span>
          <span class="matn"><?php echo !empty($content) ? $content : ""; ?></span>
          </marquee>
          </div>
         
        <?php     
}
add_shortcode('parsi_sokhan','sokhane_jadid_func');
add_shortcode('parsi_sokhan_move','m_sokhane_jadid_func');