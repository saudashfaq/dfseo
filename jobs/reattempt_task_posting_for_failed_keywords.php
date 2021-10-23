<?php

/**
 * Make a loading class and then load class with the help of that
 */

require_once __DIR__ . '/../repositories/KeywordTasks.php';
require_once __DIR__ . '/../dataforseo/SerpClient.php';
require_once __DIR__ . '/../controllers/TaskPostDataReceiverAndParser.php';
require_once __DIR__ . '/../vars_and_const/Serp.php';

$task_datas = KeywordTasks::getEligibleTasksForReattemptTaskPosting(1000);
echo '<pre>';
print_r($task_datas);
$post_array = [];

if ($task_datas !== NULL) {

    $post_data = splitTasksIntoSerpTypes($task_datas);

    //print_r($post_data);
    //echo '<br>post data end ------------------------- <br>';
    //die('stopped');

    $response = postTasks($post_data); //data was queried in usable form
    echo 'task posting complete';
    echo '<br>';

    TaskPostDataReceiverAndParser::parseKeywordsTaskPostResponse($response, 'reattempt_task_posting_for_failed_keywords');
    echo 'keywords post task response updated in db';
    echo '<br>';

    unset($response);


} else {
    echo 'No records to process';
}


function splitTasksIntoSerpTypes($tasks = [])
{

    $post_data = [];

    if (!empty($tasks)) {

        foreach ($tasks as $task) {

            $type = $task['serp_type'];
            unset($task['serp_type']);
            $post_data[$type][] = $task;
            unset($type);

        }

    }

    return $post_data;

}


function postTasks($post_array):array
{

    $googleClient = new SerpClient();
    //1=google-organic, 2=google-local, 3=google-mobile, 4=bing-organic
    //serp_type
    $response = [];

    foreach ($post_array as $serp_type => $tasks) {

        $serp_name = array_search($serp_type, Serp::ALL_SERP_TYPES);

        $chunk = array_chunk($tasks, 100)[0];
            //echo '<br>serp type: ' , $serp_type , ' serp name: ' , $serp_name, '<br>';
            //print_r($chunk);
            //continue;


            switch ($serp_type) {

                case 1: //google-organic , device default

                    $res = $googleClient->organic()->postTasks($chunk, false);

                    if( validateResponse($res) ) {

                        $response[$serp_name] = $res;

                    } else {

                        logErrorAndEmail($res, $chunk);

                    }

                    unset($res);

                    break;

                case 2: //google-local, maps

                    $res = $googleClient->maps()->postTasks($chunk, false);

                    if( validateResponse($res) ) {

                        $response[$serp_name] = $res;

                    } else {

                        logErrorAndEmail($res, $chunk);

                    }

                    unset($res);

                    break;

                case 3://google-mobile , device mobile

                    $res = $googleClient->organic()->postTasks($chunk, true);

                    if( validateResponse($res) ) {

                        $response[$serp_name] = $googleClient->organic()->postTasks($chunk, true);

                    } else {

                        logErrorAndEmail($res, $chunk);
                    }

                    unset($res);

                    break;
                //todo: add bing
            }


    }

    print_r($response);
    return $response;

}