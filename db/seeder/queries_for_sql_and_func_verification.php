<?php


//Pick keywords for task_posting. Task_posting job triggers
"SELECT * FROM keywords WHERE latest_rank_checked_on < '2021-10-16' and
 campaign_id IN (select campaign_id from campaigns where status = 1 and
 rank_check_due_time < CURRENT_TIME) and
 user_account_id IN (SELECT id from user_accounts where status = 1)";




//Get all tasks (failed task posting) from keyword_tasks table
// that are ready to reattempt task posting.

"select 
		kt.id,
        kt.serp_type, 
        CONCAT ('kid_',kt.keyword_id,'_tid_',kt.id) as tag,
        c.location_code, c.language_code, 
        keywords.keyword
        
        from keyword_tasks as kt, keywords, campaigns as c, user_accounts as ua
        
        where 

		kt.keyword_id = keywords.keyword_id and
        keywords.campaign_id = c.campaign_id AND
        keywords.user_account_id = ua.id AND

        kt.status = 1 and
        c.status = 1 and
        ua.status = 1 and
         
        (kt.post_task_attempt_due_at < current_timestamp OR kt.post_task_attempt_due_at IS NULL) and 
        (kt.post_task_attempts < 4 or kt.post_task_attempts is null) 
        order by kt.created_at asc limit 1000";
