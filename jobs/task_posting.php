<?php

/**
 * Make a loading class and then load class with the help of that
 */

require_once __DIR__ . '/../repositories/Keywords.php';
require_once __DIR__ . '/../dataforseo/SerpClient.php';
require_once __DIR__ . '/../controllers/TaskPostDataReceiverAndParser.php';

$file_name = 'task_posting';

echo date("Y-m-d H:i:s");
echo '<br>';

//

$keywords_data = Keywords::getEligibleKeywordsForTaskPosting(1000);
echo '<pre>';
print_r($keywords_data);

//die();
$post_array = [];

if ( count($keywords_data) > 0 ) {

    //making unique id/tag
    $keywords_data = array_map("unserialize", array_unique( array_map("serialize", $keywords_data)));

    //making chunks of 100 records from total 1000 query limit
    $chunks = array_chunk($keywords_data, 100);

    //print_r($arrays);
    //die();


    unset( $keywords_data );

    foreach ($chunks as $kws) {

        echo 'Loop Iteration started to process 100 keywords';
        echo '<br>';

        $response = postTasks($kws); //data was queried in usable form
        print_r($response);
        echo 'task posting complete';
        echo '<br>';
        //die('stopped');


        try {

            TaskPostDataReceiverAndParser::parseKeywordsTaskPostResponse($response, $file_name);
            echo 'keywords post task response updated in db';
            echo '<br>';


            try {

                $ids_only = array_column($kws, 'keyword_id');
                Keywords::updateKeywordsDueDateThatWerePickedUpForTaskPosting($ids_only);
                unset($ids_only);
                echo 'Keywords table updated';

            } catch (Exception $e) {
                $subject = 'Urgent: Stop and Debug Job ' . __FILE__;
                $message = ' Task posting has been done but system could not update Keywords table for KeywordsDueDate ';
                $message .= ' <br> Error message is as follows: <br> ';
                $message .= ' <br> ' . json_encode([$e->getMessage(), $e->getFile(), $e->getLine()]);
                //todo: comment message and uncomment mail sending
                echo $message;
                //mail('saud.ashfaq@gmail.com', $subject, $message);
            }


        } catch (Exception $e) {

            $subject = 'Urgent: Stop and Debug Job ' . __FILE__;
            $message = ' System could not do parsing for Task posting but the tasks were posted correctly ';
            $message .= ' <br> Response from DataForSeo is as follows: <br> ';
            $message .= json_encode($response);
            $message .= ' <br> ';
            $message .= ' <br> Error message is as follows: <br> ';
            $message .= ' <br> ' . json_encode([$e->getMessage(), $e->getFile(), $e->getLine()]);

            //todo: comment message and uncomment mail sending
            echo $message;
            //mail('saud.ashfaq@gmail.com', $subject, $message);
        }

        unset($response);

    }

} else {

    echo 'No keywords found for posting';
}


function postTasks($post_array): array
{

    //change serp_type names (keys in response array) with defined constants
    $response = [];

    $googleClient = new SerpClient();

    $res = $googleClient->organic()->postTasks($post_array, false);

    if (validateResponse($res)) {

        $response[Serp::GOOGLE_ORGANIC['name']] = $res;

    } else {

        logErrorAndEmail($res, $post_array);

    }
    unset($res);


    $res = $googleClient->maps()->postTasks($post_array, false);

    if (validateResponse($res)) {

        $response[Serp::GOOGLE_LOCAL['name']] = $res;

    } else {

        logErrorAndEmail($res, $post_array);
    }
    unset($res);

    $res = $googleClient->organic()->postTasks($post_array, true);

    if (validateResponse($res)) {

        $response[Serp::GOOGLE_MOBILE['name']] = $res;
    } else {

        logErrorAndEmail($res, $post_array);
    }

    //todo add bing-se as well

    unset( $googleClient );
    //print_r($response);
    return $response;

}


function logErrorAndEmail($response, $kws)
{

    $response = json_encode($response);

    $message = 'We received invalid response from DataForSEO in file ' . __FILE__ .
        ' at line ' . __LINE__ . ' data is as follows. <br>';
    $message .= $response;
    $message .= ' <br> ' . 'For the date ' . date('Y-m-d');
    $message .= ' all keyword ids in this failed attempt are <br>';
    $message .= ' ' . json_encode($kws);

    //todo: comment message and uncomment mail sending
    echo $message;
    //mail('saud.ashfaq@gmail.com', 'Invalid Response in file'.__FILE__, $message);
}