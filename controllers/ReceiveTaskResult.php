<?php


/*

    To run ngrok
    ./ngrok http http://localhost:8888
    in htdocs index.php file will be triggered

*/
require_once __DIR__ . '/../repositories/KeywordTasks.php';
require_once __DIR__ . '/../controllers/TaskResultParser.php';

$received_response_zipped = file_get_contents('php://input');

if (!empty($received_response_zipped)) {


    $response = json_decode(gzdecode($received_response_zipped), true);

    if (isset($response['status_code']) and $response['status_code'] === 20000) {

        _in_logit_POST("result", $response);
        //do something with results

        //TODO: remove this sleep
        sleep(5);

        try {
            $task_id = $response['tasks']['0']['id'];

            //DataForSeo Task ID
            $keyword_task_data_from_db = KeywordTasks::getTaskDataByDFSTaskId($task_id);
            if (empty($keyword_task_data_from_db)) {

                throw new Exception('Task was not found from keyword_tasks table having id: ' . $task_id);

            } else {

                try {

                    TaskResultParser::parseTaskGetResponse($response, $keyword_task_data_from_db);

                } catch (Exception $e) {

                    print_r($e->getMessage() . '... Could not parse task result received from DFS for task id: ' . $task_id);

                }

            }


        } catch (Exception $e) {

            print_r($e->getMessage());
        }


        $err[] = "ok";
    } else {
        //_in_logit_POST('error decode', $received_response_zipped);
        $err[] = "error";
    }
} else {
    $err[] = "empty POST";

}


function _in_logit_POST($id_message, $data)
{
    @file_put_contents(__DIR__ . "/postback_url_example.log", PHP_EOL . date("Y-m-d H:i:s") . ": " . $id_message . PHP_EOL . "---------" . PHP_EOL . print_r($data, true) . PHP_EOL . "---------", FILE_APPEND);
}
