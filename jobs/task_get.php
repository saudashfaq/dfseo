<?php

/**
 * Make a loading class and then load class with the help of that
 */

require_once __DIR__.'/../repositories/KeywordTasks.php';
require_once __DIR__.'/../dataforseo/SerpClient.php';
require_once __DIR__.'/../controllers/TaskResultParser.php';

$file_name = 'task_get';


// TODO: if task_get is unsuccessful or KeywordsRankings table update
// is unsuccessful, keyword_tasks table should be updated


$tasks_get = KeywordTasks::getAllDueTasksAwaitingData(1000);
echo '<pre>';
print_r($tasks_get);
echo '<hr>';
echo '<br>============================================================================================<br>';
echo '<hr>';
//echo 'No keywordTasks found to get data';
//die();

if( !empty( $tasks_get ) ) {

//    print_r($tasks_get);
//    die();

    $googleClient = new SerpClient();
    foreach ($tasks_get as $task) {

        if(!empty($res)) { unset($res);}
        if(!empty($task_id)) { unset($task_id);}
        $task_id = $task['task_id'];

        switch ($task['serp_type']) {

            case 1: //google organic
                $res = $googleClient->organic()->getSingleTaskReadyByID($task_id);
                break;
            case 2: // google maps
                $res = $googleClient->maps()->getSingleTaskReadyByID($task_id);
                break;
            case 3: //google organic mobile
                $res = $googleClient->organic()->getSingleTaskReadyByID($task_id);
                break;
            //TODO: add bing case as well

        }

        print_r($res);
        if( validateResponse($res) ) {

            $res = TaskResultParser::parseTaskGetResponse($res, $task,'task_get');

        } else {

            echo 'Problem';
            logErrorAndEmail($res, $task);

        }

    }

}



