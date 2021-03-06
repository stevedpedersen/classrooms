<?php

/**
 */
class Classrooms_ClassData_CronJob extends Bss_Cron_Job
{
    const PROCESS_ACTIVE_JOBS_EVERY = 60; // once a day
    
    public function run ($startTime, $lastRun, $timeDelta)
    {
        if ($timeDelta >= self::PROCESS_ACTIVE_JOBS_EVERY)
        {
            set_time_limit(0);

            $createFacultyAccounts = true;
            $importer = new Classrooms_ClassData_Importer($this->getApplication());
            
            list($count, $added, $removed) = $importer->syncDepartments();

            $semesterCodes = $this->getApplication()->siteSettings->semesters;
            
            if (!is_array($semesterCodes))
            {
                $semesterCodes = explode(',', $semesterCodes);
            }
            foreach ($semesterCodes as $semesterCode)
            {
                $importer->import($semesterCode, $createFacultyAccounts);
            }

            // import schedule info
            foreach ($semesterCodes as $semesterCode)
            {
                $rs = pg_query("
                    SELECT user_id FROM classroom_classdata_enrollments
                    WHERE year_semester = '{$semesterCode}' AND role = 'instructor' 
                ");                

                $instructors = [];
                while (($row = pg_fetch_row($rs)))
                {
                    $instructors[$row[0]] = $row[0];
                }
                
                $importer->importSchedules($semesterCode, $instructors);
            }

            return true;
        }
    }

    private function schema ($recordClass)
    {
        return $this->getApplication()->schemaManager->getSchema($recordClass);
    }
}
