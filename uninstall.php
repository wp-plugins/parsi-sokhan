<?php
global $wpdb;
$wpdb->query( "DROP table {$wpdb->prefix}parsi_sokhan" );
delete_option('sb_paginate_count');

