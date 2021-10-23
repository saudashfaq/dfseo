<?php

//Get eligible keywords for taskPost (user_account active and keywords ranking check in not today)
//Try task posting
//If taskPost failed update logs and update task post table
//if taskPost success update keyword_tasks table
//for each data receiving create serp_type wise separate files
//when data receives update keyword_tasks table and update keywords and rankings table
//If keyword_task is still waiting for data and due time is past, run taskGet
//if taskGet successful update keywords, keywords rankings and keyword_tasks table.
//if taskGet failed update task_get_logs table and keywrods_tasks table.