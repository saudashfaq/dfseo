<?php

//namespace serp\dataforseo;


error_reporting(E_ALL);
ini_set('display_errors', '1');

//use http\Url;
require_once __DIR__ . '/RestClient.php';

class SerpClient
{

    private string $api_login = 'saud.ashfaq@gmail.com';
    private string $api_pass = '88b7f48cede7c6fa';
    private object $client;

    private string $end_point = '/v3/serp/google';
    private string $end_point_google = '/v3/serp/google';
    private string $end_point_bing = '/v3/serp/bing';


    //const WEBHOOK_REC_URL = 'https://webhook.site/2174f745-e851-4724-9ed8-079de19f259f';
    const WEBHOOK_REC_URL = 'https://6b40-101-50-69-90.ngrok.io/index.php'; //always put / after the domain name otherwise it will be considered as invalid url
    const BASE_URL_SANDBOX = 'https://sandbox.dataforseo.com/';
    const BASE_URL_LIVE = 'https://api.dataforseo.com/';



    const LOCATIONS_URL = '/locations';
    const LANGUAGES_URL = '/languages';

    /**
     * For the following urls
     * put /organic or /maps in the beginning as per need base
     */
    const POST_TASKS_ENDPOINT = '/task_post';
    const TASKS_READY_ENDPOINT = '/tasks_ready';
    const GET_TASK_ENDPOINT = '/task_get'; // id of the task at the end
    const REGULAR = '/regular/';
    const ADVANCED = '/advanced/';


    public function __construct( $test = true, $se = 'google' )
    {
        try {

            /**
             * Set base url as per instructions of sandbox or live
             */
            if ($test == true)
            {
                $base_url = self::BASE_URL_SANDBOX;

            }
            else {
                $base_url = self::BASE_URL_LIVE;
            }

            // Instead of 'login' and 'password' use your credentials from https://app.dataforseo.com/api-dashboard
            $this->client = new RestClient($base_url, null, $this->api_login, $this->api_pass);

        } catch (RestClientException $e) {
            echo "\n";
            print "HTTP code: {$e->getHttpCode()}\n";
            print "Error code: {$e->getCode()}\n";
            print "Message: {$e->getMessage()}\n";
            print  $e->getTraceAsString();
            echo "\n";
            exit();
        }
    }


    public function getResults($end_point): array
    {
        try {

            $res = $this->client->get($end_point);
            $this->resetEndPoint();
            return $res;

            // do something with result
        } catch (RestClientException $e) {
            echo "\n";
            print "HTTP code: {$e->getHttpCode()}\n";
            print "Error code: {$e->getCode()}\n";
            print "Message: {$e->getMessage()}\n";
            print  $e->getTraceAsString();
            echo "\n";
        }


    }

    private function resetEndPoint() {

        $this->end_point = get_class_vars(get_class($this))['end_point'];

    }




    public function postTasks( $tasks_array = [], $mobile = false, $prepared_end_point = NULL )
    {
        // this example has a 3 elements, but in the case of large number of tasks - send up to 100 elements per POST request
        if (count($tasks_array) > 0) {

            try {

                //set ping back, post back and priority etc
                $this->addParametersInPostTaskArray( $tasks_array, $mobile );

                if( $prepared_end_point !== NULL ) {
                    $this->end_point = $this->end_point.$prepared_end_point.self::POST_TASKS_ENDPOINT;

                } else {
                    $this->end_point = $this->end_point.self::POST_TASKS_ENDPOINT;
                }

                //echo 'Stopped<br>';
                //echo $this->end_point;
                //echo '<br>';
                //$this->resetEndPoint();
                //return;
                $result = $this->client->post($this->end_point, $tasks_array);
                //echo '<pre>';
                //print_r($result);
                $this->resetEndPoint();
                return $result;

            } catch (RestClientException $e) {
                echo "\n";
                print "HTTP code: {$e->getHttpCode()}\n";
                print "Error code: {$e->getCode()}\n";
                print "Message: {$e->getMessage()}\n";
                print  $e->getTraceAsString();
                echo "\n";
            }
        }
        else
        {
            return new Exception('Post parameters cannot be empty.');
        }
    }


    /**
     * @param array $post_array
     *
     * HELPING FUNCTIONS START
     */

    private function addParametersInPostTaskArray( array &$post_array, $mobile = false ) {

        $settings = [
            "priority" => 1, //1=normal, 2=high
            "postback_data" => !empty($this->end_point) && strstr($this->end_point, 'maps') == false ? 'regular' : 'advanced', //regular, advanced, html
            "postback_url" => SerpClient::WEBHOOK_REC_URL.'?id=$id&tag=$tag',
            //"tag" => "keyword_id".rand(101,999),
            'device' => !empty($mobile) && $mobile == true ? 'mobile' : 'desktop',
        ];

        if( count($post_array) > 0 ) {

            foreach ($post_array as $key => $val ) {

                $post_array[$key] = array_merge($post_array[$key], $settings);

            }


        }

        //print_r($post_array);

    }


    public function organic():object {

        $this->end_point = $this->end_point . '/organic';
        return $this;

    }

    public function maps():object {

        $this->end_point = $this->end_point . '/maps';
        return $this;

    }

    public function getListOfTasksReady():array
    {
        $end_point = $this->end_point.self::TASKS_READY_ENDPOINT;
        return $this->getResults($end_point);
    }

    public function getSingleTaskReadyByID( $id ) {

        if( !empty($id) ) {

            if( stristr($this->end_point, 'maps') === false ) {

                $end_point = $this->end_point.self::GET_TASK_ENDPOINT.self::REGULAR.$id;

            } else {
                //for maps results are only advanced
                $end_point = $this->end_point.self::GET_TASK_ENDPOINT.self::ADVANCED.$id;
            }

            return $this->getResults( $end_point );
        }

        return 'Task id is required';

    }

    public function getSingleTaskReadyByUrl( $url ) {

        if( !empty($url) ) {

            return $this->getResults( $url );
        }

        return 'Task id is required';

    }


    public function getLocations():array {

        $end_point = $this->end_point.self::LOCATIONS_URL;
        return $this->getResults( $end_point );

    }

    public function getLanguages():array {

        $end_point = $this->end_point.self::LANGUAGES_URL;
        return $this->getResults( $end_point );

    }



}


//$googleClient = new SerpClient();

/**************************** GET RESULTS ****************************/

//$languages = $googleClient->getLanguages();
//$locations = $googleClient->getLocations();
/*
$data[] = $tasks_ready_organic = $googleClient->organic()->getListOfTasksReady();
$data[] = $tasks_ready_maps = $googleClient->maps()->getListOfTasksReady();
$data[] = $task_result_organic = $googleClient->organic()->getSingleTaskReadyByID('07141141-2942-0066-2000-d9db5238d23e');
$data[] = $task_result_by_url = $googleClient->getSingleTaskReadyByUrl('/v3/serp/google/organic/task_get/regular/07111112-0001-0066-0000-6e17a7d8e217');
*/
//echo '<pre>';
//print_r($locations);
//die();

/***************************** POST RESULTS ***************************/
//07141141-2942-0066-2000-d9db5238d23e
/*
$post_array[] = array(
    "language_code" => "en",
    "location_code" => 2840,
    //"tag" => "some_string_123",
    "keyword" => mb_convert_encoding(str_replace(':', ' ', "albert einstein"), "UTF-8"),

);
//07141141-2942-0066-2000-7fd617693c04
$post_array[] = array(
    "language_code" => "en",
    "location_code" => 2850,
    //"tag" => "some_string_123",
    "keyword" => mb_convert_encoding(str_replace(':', ' ', "work from home"), "UTF-8"),

);

$result['google_organic'] = $googleClient->organic()->postTasks( $post_array, false );
//$result['google_maps'] = $googleClient->maps()->postTasks( $post_array, false );
//$result['google_organic_mobile'] = $googleClient->organic()->postTasks( $post_array, true );

echo '<pre>';
print_r($result);
*/