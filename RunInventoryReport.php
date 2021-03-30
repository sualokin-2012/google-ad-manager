<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//namespace Google\AdsApi\Examples\AdManager\v202102\ReportService;

//require __DIR__ . '/../../../../vendor/autoload.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/env.php';

// use Google\AdsApi\AdManager\v202102\DateTime;
// use Google\AdsApi\AdManager\v202102\DateTimeZone;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v202008\AdManagerDateTimes;
use Google\AdsApi\AdManager\Util\v202008\ReportDownloader;
use Google\AdsApi\AdManager\v202008\Column;
use Google\AdsApi\AdManager\v202008\DateRangeType;
use Google\AdsApi\AdManager\v202008\Dimension;
use Google\AdsApi\AdManager\v202008\ExportFormat;
use Google\AdsApi\AdManager\v202008\ReportJob;
use Google\AdsApi\AdManager\v202008\ReportQuery;
use Google\AdsApi\AdManager\v202008\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v202008\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;

require_once('DbConfig.php');

/**
 * This example runs a typical daily inventory report and saves it in your
 * system's temp directory. It filters on the network's root ad unit ID. This is
 * only to demonstrate filtering for the purposes of this example, as filtering
 * on the root ad unit is equivalent to not filtering on any ad units.
 */
class RunInventoryReport
{    
    protected static $host = null;
    protected static $user = null;
    protected static $password = null;
    protected static $database = null;

    public static function setDBConfig($host, $user, $password, $database)
    {
        self::$host = $host;
        self::$user = $user;
        self::$password = $password;
        self::$database = $database;

        printf("DB Info : %s %s %s %s\n", self::$host, self::$user, self::$password, self::$database);
    }

    public static function runExample(
        ServiceFactory $serviceFactory,
        AdManagerSession $session
    ) {
        $reportService = $serviceFactory->createReportService($session);

        // Network Information
        $networkService = $serviceFactory->createNetworkService($session);
        // Make a request
        $network = $networkService->getCurrentNetwork();
        printf(
            "Network with code %d and display name '%s' was found.\n",
            $network->getNetworkCode(),
            $network->getDisplayName()
        );

        // Create report query.
        $reportQuery = new ReportQuery();
        $reportQuery->setDimensions(
            [
                Dimension::AD_UNIT_ID,
                Dimension::AD_UNIT_NAME,
                Dimension::REQUEST_TYPE
            ]
        );
        $reportQuery->setColumns(
            [
                // Column::AD_SERVER_IMPRESSIONS,
                // Column::AD_SERVER_CLICKS,
                // Column::ADSENSE_LINE_ITEM_LEVEL_IMPRESSIONS,
                // Column::ADSENSE_LINE_ITEM_LEVEL_CLICKS,
                // Column::TOTAL_LINE_ITEM_LEVEL_IMPRESSIONS,
                // Column::TOTAL_LINE_ITEM_LEVEL_CPM_AND_CPC_REVENUE
                
                // Total
                Column::TOTAL_CODE_SERVED_COUNT,
                Column::TOTAL_LINE_ITEM_LEVEL_IMPRESSIONS,
                // not supported
                //Column::TOTAL_LINE_ITEM_LEVEL_TARGETED_IMPRESSIONS,
                Column::TOTAL_LINE_ITEM_LEVEL_CLICKS,
                // not supported
                //Column::TOTAL_LINE_ITEM_LEVEL_TARGETED_CLICKS,
                Column::TOTAL_LINE_ITEM_LEVEL_CPM_AND_CPC_REVENUE,
                //Total CPM, CPC, CPD, and vCPM revenue
                Column::TOTAL_ACTIVE_VIEW_MEASURABLE_IMPRESSIONS,
                Column::TOTAL_ACTIVE_VIEW_VIEWABLE_IMPRESSIONS,
                Column::TOTAL_AD_REQUESTS,

                // Ad server
                Column::AD_SERVER_IMPRESSIONS,
                Column::AD_SERVER_CLICKS,
                Column::AD_SERVER_CPM_AND_CPC_REVENUE,                
                // Ad server CPM, CPC, CPD, and vCPM revenue

                // AdSense
                // AdSense impressions ?
                Column::ADSENSE_LINE_ITEM_LEVEL_IMPRESSIONS,                
                // AdSense clicks?
                Column::ADSENSE_LINE_ITEM_LEVEL_CLICKS,
                // AdSense revenue
                Column::ADSENSE_ACTIVE_VIEW_REVENUE,

                // Ad Exchange
                //Column::AD_EXCHANGE_IMPRESSIONS,
                //Column::AD_EXCHANGE_CLICKS,
                // Ad Exchange revenue
                Column::AD_EXCHANGE_ACTIVE_VIEW_REVENUE
            ]
        );

        // Set the ad unit view to hierarchical.
        $reportQuery->setAdUnitView(ReportQueryAdUnitView::HIERARCHICAL);
        // Set the start and end dates or choose a dynamic date range type.
        //$reportQuery->setDateRangeType(DateRangeType::YESTERDAY);
        $reportQuery->setDateRangeType(DateRangeType::LAST_WEEK);
        // $reportQuery->setDateRangeType(DateRangeType::CUSTOM_DATE);
        // $reportQuery->setStartDate(
        //     AdManagerDateTimes::fromDateTime(
        //         new DateTime(
        //             '-7 days',
        //             new DateTimeZone('America/New_York')
        //         )
        //     )
        //         ->getDate()
        // );
        
        // printf("Start Date year : %d month : %d day : %d", 
        // $reportQuery->getStartDate()->getYear(),
        // $reportQuery->getStartDate()->getMonth(),
        // $reportQuery->getStartDate()->getDay());

        // $reportQuery->setEndDate(
        //     AdManagerDateTimes::fromDateTime(
        //         new DateTime(
        //             'now',
        //             new DateTimeZone('America/New_York')
        //         )
        //     )
        //         ->getDate()
        // );

        // printf("End Date year : %d month : %d day : %d", 
        // $reportQuery->getEndDate()->getYear(),
        // $reportQuery->getEndDate()->getMonth(),
        // $reportQuery->getEndDate()->getDay());

        // Create report job and start it.
        $reportJob = new ReportJob();
        $reportJob->setReportQuery($reportQuery);
        $reportJob = $reportService->runReportJob($reportJob);
        printf("report job id : %s", $reportJob->getId());        

        // Create report downloader to poll report's status and download when
        // ready.
        $reportDownloader = new ReportDownloader(
            $reportService,
            $reportJob->getId()
        );
        if ($reportDownloader->waitForReportToFinish()) {
            //Write to system temp directory by default.
            $filePath = sprintf(
                '%s.csv.gz',
                tempnam(sys_get_temp_dir(), 'inventory-report-')
            );

            printf("Downloading report to %s ...%s", $filePath, PHP_EOL);
            // Download the report.
            $reportDownloader->downloadReport(
                ExportFormat::CSV_DUMP,
                $filePath
            );
            print "done.\n";
            // Unzip .gz file to csv
            self::unzip($filePath);
            print "unzip complete.\n";

            // Remove .gz from file name
            $unzipFile = substr($filePath, 0, -3);
            printf("Unzip file name : %s\n", $unzipFile);
            self::importCSVToMySQL(($unzipFile));

            //printf("DB Info : %s %s %s %s\n", self::$host, self::$user, self::$password, self::$database);
            
        } else {
            print "Report failed.\n";
        }
    }

    public static function main()
    {   
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()
            ->build();
        $session = (new AdManagerSessionBuilder())->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
        self::runExample(new ServiceFactory(), $session);
    }

    // Unzip .gz to csv file
    public static function unzip($filename)
    {
        $file_name = $filename;

        $buffer_size = 4096;
        $out_file_name = str_replace('.gz', '', $file_name);

        $file = gzopen($file_name, 'rb');
        $out_file = fopen($out_file_name, 'wb');

        while(!gzeof($file)) {
            fwrite($out_file, gzread($file, $buffer_size));
        }
        fclose($out_file);
        gzclose($file);
    }

    // Import csv to MySQL
    public static function importCSVToMySQL($filename)
    {
        if(!file_exists($filename)) {
            print "File not found.\n";
            exit;
        }

        $file = fopen($filename, "r");

        if(!$file) {
            print "Error opening data file.\n";
            exit;
        }

        $size = filesize($filename);
        if(!$size) {
            print "File is empty.\n";
            exit;
        }

        printf("File size : %d\n", $size);

        // Connect to MySQL Database
        $conn = new mysqli(self::$host, self::$user, self::$password, self::$database);

        // Check connection
        if($conn->connect_error) {
            die("Connection failed: ".$conn->connect_error);
        }

        printf("DB Connection success!\n");
                
        $row = 1;
        while(($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            if($row == 1)
            {
                $row++;
                continue;
            }

            $row++;
            
            // echo "<pre>";
            // print_r($getData);  

            $sql = "INSERT into reports (`ad_unit_id`, `ad_unit`, `dimension_request_type`, `column_total_code_served_count`, `column_total_line_item_level_impressions`, `column_total_line_item_level_clicks`, `column_total_line_item_level_cpm_and_cpc_revenue`, `column_total_active_view_measurable_impressions`, `column_total_active_view_viewable_impressions`, `column_total_ad_requests`, `column_ad_server_impressions`, `column_ad_server_clicks`, `column_ad_server_cpm_and_cpc_revenue`, `column_adsense_line_item_level_impressions`, `column_adsense_line_item_level_clicks`, `column_adsense_active_view_revenue`, `column_adsense_exchange_active_view_revenue`)
            VALUES ('".$getData[0]."', '".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."','".$getData[8]."','".$getData[9]."','".$getData[10]."','".$getData[11]."','".$getData[12]."','".$getData[13]."','".$getData[14]."','".$getData[15]."','".$getData[16]."')";

            $result = mysqli_query($conn, $sql);

            if(!isset($result))
            {
                print "Invalid File\n";
            }
            else
            {
                print "CSV File has been successfully Imported.\n";
            }
              
            //$id = mysqli_real_escape_string($conn, $getData[0]);
            //printf($getData[0]);
        }

        fclose($file);
    }
}

RunInventoryReport::setDBConfig($mysql_host, $mysql_user, $mysql_password, $mysql_db);
RunInventoryReport::main();