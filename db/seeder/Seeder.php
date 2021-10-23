<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../DB.php';
require_once __DIR__ .'/locations_and_languages.php';

class Seeder
{

    public object $faker;
    public array $languages;
    public array $locations;



    public function __CONSTRUCT($languages, $locations, $urls)
    {

        if( empty($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] != 'localhost' ) {

            die('Cannot run this script');

        }


        $this->faker = Faker\Factory::create();

        $this->languages = $languages;

        $this->locations = $locations;

        $this->urls = $urls;

    }

    public function registerUsers($how_many = 1, $truncate_table = false)
    {

        $values = '';

        for ($x = 1; $x <= $how_many; $x++) {

            $business_name = htmlspecialchars($this->faker->company(), ENT_QUOTES);
            $email = $this->faker->email();
            $logo = $this->faker->word();
            $status = $this->faker->numberBetween(0, 1);

            $values .= "('" . $business_name . "', " .
                "'" . $email . "', " .
                "'" . $logo . "', " .
                $status. ")";

            if ($x < $how_many) {
                $values .= " , ";
            }

        }

        if ($truncate_table === true) {
            DB::run_mysql_query("TRUNCATE TABLE user_accounts", ' Line Number: '. __LINE__ . ' ');
            sleep(2);
        }

        $sql = "INSERT INTO user_accounts (business_name, email, logo, status) VALUES $values";
        DB::run_mysql_query($sql, ' Line Number: '. __LINE__ . ' ');

    }


    public function addCampaigns($how_many = 1, $truncate_table = false)
    {

        $values = '';

        $user_account_ids = $this->getUserAccountIds();

        for ($x = 1; $x <= $how_many; $x++) {

            $mtrand_for_lang = array_rand($this->languages);
            $mtrand_for_loc = array_rand($this->locations);
            $mtrand_for_url = array_rand($this->urls);

            $user_account_id = $user_id = $user_account_ids[array_rand($user_account_ids)];
            $campaign_name = htmlspecialchars($this->faker->company(), ENT_QUOTES);
            $time_zone = $this->faker->timezone();
            $campaign_logo = $this->faker->word();
            $language_name = $this->languages[$mtrand_for_lang]['language_name'];
            $language_code = $this->languages[$mtrand_for_lang]['language_code'];
            $location_code = $this->locations[$mtrand_for_loc]['location_code'];
            $location_name = $this->locations[$mtrand_for_loc]['location_name'];
            $country_iso_code = $this->locations[$mtrand_for_loc]['country_iso_code'];
            $url = $this->urls[$mtrand_for_url];
            $rank_check_due_time = $this->faker->time("H:i:s", '23:59:59');
            $created_at = $this->faker->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d H:i:s');
            $updated_at = $this->faker->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d H:i:s');
            $status = $this->faker->numberBetween(0, 1);

            $values .= "( " .
                "'". $campaign_name . "', ".
                "'". $campaign_logo . "', ".
                "'". $time_zone . "', ".
                "'". $language_name . "', ".
                "'". $language_code . "', ".
                 $location_code . ", ".
                "'". $location_name . "', ".
                "'". $country_iso_code . "', ".
                "'". $url . "', ".
                "'". $rank_check_due_time . "', ".
                "'". "$created_at" . "', ".
                "'". $updated_at . "', ".
                 $user_account_id . ", ".
                 $user_id . ", ".
                 $status .
                " )";

            if ($x < $how_many) {
                $values .= " , ";
            }

        }

        if ($truncate_table === true) {

            DB::run_mysql_query("TRUNCATE TABLE campaigns", ' Line Number: '. __LINE__ . ' ');
            sleep(2);

        }

        $sql = "INSERT INTO `campaigns`(
            `campaign_name`, `campaign_logo`, `time_zone`,
            `language_name`, `language_code`, `location_code`,
            `location_name`, `country_iso_code`, `url`, `rank_check_due_time`,
            `created_at`, `updated_at`, `user_account_id`, `user_id`,
             `status`) VALUES $values";

        DB::run_mysql_query($sql, ' Line Number: '. __LINE__ . ' ');


    }

    public function addKeywords($how_many = 1, $truncate_table = false)
    {

        $values = '';

        //$user_account_ids = $this->getUserAccountIds();
        $campaigns = $this->getCampaigns();


        /*echo '<pre>';
        print_r($campaigns);
        echo '<hr>';
        echo $campaign_index_val;
        echo '<hr>';
        die();*/
        for ($x = 1; $x <= $how_many; $x++) {

            $campaign_index_val = array_rand($campaigns);

            $word_count = mt_rand(1,5);
            $date = $this->faker->dateTimeBetween('-3 days', '+3 days');
            $created_at = $updated_at = $date->format('Y-m-d H:i:s');
            $latest_rank_checked_on = $date->format('Y-m-d');

            $user_account_id = $user_id = $campaigns[$campaign_index_val]['user_account_id'];
            $campaign_id = $campaigns[$campaign_index_val]['campaign_id'];
            $keyword = $this->faker->words($word_count , true);
            //$latest_rank_checked_on = $this->faker->dateTimeBetween('-3 days', '+3 days')->format('Y-m-d');
            //$created_at = $this->faker->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d H:i:s');
            //$updated_at = $this->faker->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d H:i:s');

            $values .= "( " .
                $user_account_id . ", ".
                $campaign_id . ", ".
                "'". $keyword . "', ".
                "'". $latest_rank_checked_on . "', ".
                "'". "$created_at" . "', ".
                "'". $updated_at . "'".
                " )";

            if ($x < $how_many) {
                $values .= " , ";
            }

        }

        if ($truncate_table === true) {

            DB::run_mysql_query("TRUNCATE TABLE keywords", ' Line Number: '. __LINE__ . ' ');
            sleep(2);

        }

        $sql = "INSERT INTO `keywords`(
                       `user_account_id`, `campaign_id`, 
                       `keyword`, `latest_rank_checked_on`, `created_at`, 
                       `updated_at`) VALUES $values";

        DB::run_mysql_query($sql, ' Line Number: '. __LINE__ . ' ');


    }


    private function getUserAccountIds() {

        $res = DB::run_mysql_query("Select id from user_accounts", " Line Number: " . __LINE__);

        if( $res->num_rows > 0 ) {
            $res = mysqli_fetch_all($res, MYSQLI_ASSOC);
        }

        return array_column($res, 'id');
    }


    private function getCampaignIds() {

        $res = DB::run_mysql_query("Select campaign_id from campaigns", " Line Number: " . __LINE__);

        if( $res->num_rows > 0 ) {

            $res = mysqli_fetch_all($res, MYSQLI_ASSOC);

        }

        return array_column($res, 'campaign_id');

    }


    private function getCampaigns() {

        $res = DB::run_mysql_query("Select campaign_id, user_account_id from campaigns", " Line Number: " . __LINE__);

        if( $res->num_rows > 0 ) {

            $res = mysqli_fetch_all($res, MYSQLI_ASSOC);

        }

        return $res;

    }



    public function deleteDatabase() {

        DB::run_mysql_query("TRUNCATE TABLE keyword_rankings", ' Line Number: '. __LINE__ . ' ');
        DB::run_mysql_query("TRUNCATE TABLE keyword_tasks", ' Line Number: '. __LINE__ . ' ');
        DB::run_mysql_query("TRUNCATE TABLE keyword_task_get_failed_logs", ' Line Number: '. __LINE__ . ' ');
        DB::run_mysql_query("TRUNCATE TABLE keyword_task_post_failed_logs", ' Line Number: '. __LINE__ . ' ');
        sleep(2);


    }


}
//Languages and Locations are variables in included file
$s = new Seeder( $languages, $locations, $urls);
$s->deleteDatabase();
$s->registerUsers(10, true );
$s->addCampaigns(20, true);
$s->addKeywords(50, true);

echo '<br>Seeding is complete.<br>';