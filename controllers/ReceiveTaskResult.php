<?php


/*

    To run ngrok
    cd to downloads folder
    ./ngrok http http://localhost:8888
    in htdocs index.php file will be triggered

*/
require_once __DIR__ . '/../repositories/KeywordTasks.php';
require_once __DIR__ . '/../controllers/TaskResultParser.php';

$task_id = $_GET['id'];
$tag = $_GET['tag'];

if( !empty($task_id) && isset($tag) ) {

    $task_id = stripslashes(strip_tags(htmlspecialchars($task_id, ENT_QUOTES)));
    try {
        //TODO: remove this sleep. Put this sleep because DFSEO was sending the result at once in demo
        sleep(5);

        $keyword_task_data_from_db = KeywordTasks::getTaskDataByDFSTaskId($task_id);
        if( empty($keyword_task_data_from_db)) {

            throw new Exception('Task id: ' . $task_id . ' was not found in keyword_tasks table. This activity happened at File/Line: '.__File__.' / '. __LINE__);


        }
    } catch (Exception $e) {

        print_r($e->getMessage());

    }


    if( !empty($keyword_task_data_from_db) ) {

        $received_response_zipped = file_get_contents('php://input');

        if (!empty($received_response_zipped)) {

            $response = json_decode(gzdecode($received_response_zipped), true);

            if (isset($response['status_code']) and $response['status_code'] === 20000) {

                //TODO: remove the following log function as well
                _in_logit_POST("result", $response);
                //do something with results
                //$task_id = $response['tasks']['0']['id'];

                try {

                    TaskResultParser::parseTaskGetResponse($response, $keyword_task_data_from_db);

                } catch (Exception $e) {

                    print_r($e->getMessage() . '... Could not parse task result received from DFS for task id: ' . $task_id);

                }

                $err[] = "ok";

            } else {

                $err[] = "error";
            }
        } else {

            $err[] = "empty POST";

        }



    }


}




function _in_logit_POST($id_message, $data)
{
    @file_put_contents(__DIR__ . "/postback_url_example.log", PHP_EOL . date("Y-m-d H:i:s") . ": " . $id_message . PHP_EOL . "---------" . PHP_EOL . print_r($data, true) . PHP_EOL . "---------", FILE_APPEND);
}
