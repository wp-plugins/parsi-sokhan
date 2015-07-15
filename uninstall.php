<?php
global $wpdb;
$wpdb->query( "DROP table {$wpdb->prefix}sb" );
delete_option('sb_paginate_count');

