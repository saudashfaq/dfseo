<?php

function filter(&$value) {

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

}


function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

function getCurrentDate() {
    return date('Y-m-d');
}


function create_tag_for_task_posting($kid, $tid = null) {

    return 'kid_'. $kid . !empty($tid) ? '_tid_'. $tid : '';

}

function get_tid_from_tag ( $tag ) {

    //get task_post_table unique id
    $pos = stripos($tag, 'tid');
    return $pos === false ? false : substr($tag, $pos+4);
}

function get_kid_from_tag ( $tag ) {

    $pos = stripos($tag, '_tid');
    $tag_without_tid = $pos === false ? $tag : substr($tag, 0, $pos);

    $pos_kid = stripos($tag_without_tid, 'kid');
    return $pos_kid === false ? $tag_without_tid : substr($tag_without_tid, 4); //as kid_
}
/*$tag = 'kid_2399989838_tid_12999999223';
echo get_tid_from_tag($tag);
echo get_kid_from_tag($tag);*/



//This validator validates if our API call was successful or not to "DataForSeo API"
//SO, if response has some params it means call was successful and we get response as well
function validateResponse($response):bool {

    if( !empty($response['status_code']) && !empty($response['status_message'])) {

        return true;

    }

    return false;
}


