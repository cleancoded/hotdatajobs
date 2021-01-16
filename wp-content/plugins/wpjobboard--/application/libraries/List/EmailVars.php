<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailVars
 *
 * @author greg
 */
class Wpjb_List_EmailVars {
    
    public function objectJob()
    {
        $default = array();
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 9999,
        );
        $default["post_id"] = array(
            "name" => "post_id",
            "title" => __("Post ID (associated post ID)", "wpjobboard"),
            "value" => 1009,
        );
        $default["employer_id"] = array(
            "name" => "employer_id",
            "title" => __("Employer ID (ID of an Employer who owns this job)", "wpjobboard"),
            "value" => 1111,
        );
        $default["job_title"] = array(
            "name" => "job_title",
            "title" => __("Job Title", "wpjobboard"),
            "value" => "Retail Sales Consultant",
        );
        $default["job_slug"] = array(
            "name" => "job_slug",
            "title" => __("Slug (URL friendly name)", "wpjobboard"),
            "value" => "retail-sales-consultant",
        );
        $default["job_description"] = array(
            "name" => "job_description",
            "title" => __("Job Description", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
        );
        $default["job_created_at"] = array(
            "name" => "job_created_at",
            "title" => __("Job Creation Date (YYYY-MM-DD)", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("TODAY - 5 DAY")),
        );
        $default["job_modified_at"] = array(
            "name" => "job_modified_at",
            "title" => __("Job Last Modification Date (YYYY-MM-DD)", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("TODAY")),
        );
        $default["job_expires_at"] = array(
            "name" => "job_expires_at",
            "title" => __("Job Expiration Date (YYYY-MM-DD)", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("TODAY - 5 DAY")),
        );
        $default["job_country"] = array(
            "name" => "job_country",
            "title" => __("Country (ISO 3166-1 Number)", "wpjobboard"),
            "value" => "820",
        );
        $default["job_state"] = array(
            "name" => "job_state",
            "title" => __("Job State", "wpjobboard"),
            "value" => "NY",
        );
        $default["job_zip_code"] = array(
            "name" => "job_zip_code",
            "title" => __("Job Zip Code", "wpjobboard"),
            "value" => "10118",
        );
        $default["job_city"] = array(
            "name" => "job_city",
            "title" => __("Job City", "wpjobboard"),
            "value" => "New York",
        );
        $default["company_name"] = array(
            "name" => "company_name",
            "title" => __("Company Name", "wpjobboard"),
            "value" => "ACME Corporation.",
        );
        $default["company_url"] = array(
            "name" => "company_url",
            "title" => __("Company URL", "wpjobboard"),
            "value" => "http://example.com/",
        );
        $default["company_email"] = array(
            "name" => "company_email",
            "title" => __("Company Email", "wpjobboard"),
            "value" => "acme@example.com",
        );
        $default["is_approved"] = array(
            "name" => "is_approved",
            "title" => __("Is Approved", "wpjobboard"),
            "value" => "1",
        );
        $default["is_active"] = array(
            "name" => "is_active",
            "title" => __("Is Active", "wpjobboard"),
            "value" => "1",
        );
        $default["is_filled"] = array(
            "name" => "is_filled",
            "title" => __("Is Filled", "wpjobboard"),
            "value" => "0",
        );
        $default["is_featured"] = array(
            "name" => "is_featured",
            "title" => __("Is Featured", "wpjobboard"),
            "value" => "0",
        );
        $default["membership_id"] = array(
            "name" => "membership_id",
            "title" => __("Membership ID", "wpjobboard"),
            "value" => "",
        );
        $default["pricing_id"] = array(
            "name" => "pricing_id",
            "title" => __("Pricing ID", "wpjobboard"),
            "value" => "",
        );
        $default["url"] = array(
            "name" => "url",
            "title" => __("Job URL", "wpjobboard"),
            "value" => "http://example.com/job/retail-sales-consultant/",
        );
        $default["admin_url"] = array(
            "name" => "admin_url",
            "title" => __("Admin URL", "wpjobboard"),
            "value" => "http://example.com/wp-admin//?id=9999",
        );

        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
            "country" => array(
                "header" => __("Country", "wpjobboard"),
                "data" => $this->getCountry(),
            ),
            "meta" => array(
                "header" => __("Custom Fields", "wpjobboard"),
                "data" => $this->getMeta("job")
            ),
            "tag" => array(
                "header" => __("Tags", "wpjobboard"),
                "data" => $this->getTags()
            ),
            "file" => array(
                "header" => __("Files", "wpjobboard"),
                "data" => $this->getFiles("job")
            )
        );
        

        return $data;
    }
    
    public function objectPayment()
    {
        $default = array();
        $dates = array();
        $formatted = array();
        
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 8888,
        );
        $default["user_id"] = array(
            "name" => "user_id",
            "title" => __("User ID", "wpjobboard"),
            "value" => 7777,
        );
        $default["email"] = array(
            "name" => "email",
            "title" => __("Buyer Email", "wpjobboard"),
            "value" => "buyer@example.com",
        );
        $default["fullname"] = array(
            "name" => "fullname",
            "title" => __("Buyer Name", "wpjobboard"),
            "value" => "John Doe",
        );
        $default["user_ip"] = array(
            "name" => "user_id",
            "title" => __("Buyer IP Address", "wpjobboard"),
            "value" => "127.0.0.1",
        );
        $default["object_id"] = array(
            "name" => "object_id",
            "title" => __("Object ID", "wpjobboard"),
            "value" => 9999,
        );
        $default["object_type"] = array(
            "name" => "object_type",
            "title" => __("Object Type", "wpjobboard"),
            "value" => "job",
        );
        $default["engine"] = array(
            "name" => "engine",
            "title" => __("Payment Method", "wpjobboard"),
            "value" => "Cash",
        );
        $default["pricing_id"] = array(
            "name" => "pricing_id",
            "title" => __("Pricing ID", "wpjobboard"),
            "value" => 5555,
        );
        $default["status"] = array(
            "name" => "status",
            "title" => __("Payment Status", "wpjobboard"),
            "value" => 2,
        );
        $default["message"] = array(
            "name" => "message",
            "title" => __("Logged Messages", "wpjobboard"),
            "value" => "",
        );
        $dates["created_at"] = array(
            "name" => "created_at",
            "title" => __("Created", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -5 DAY")),
        );
        $dates["paid_at"] = array(
            "name" => "paid_at",
            "title" => __("Paid", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -1 DAY")),
        );
        $default["payment_sum"] = array(
            "name" => "payment_sum",
            "title" => __("To Pay", "wpjobboard"),
            "value" => "100",
        );
        $default["payment_paid"] = array(
            "name" => "payment_paid",
            "title" => __("Paid", "wpjobboard"),
            "value" => "100",
        );
        $default["payment_discount"] = array(
            "name" => "payment_discount",
            "title" => __("Discount", "wpjobboard"),
            "value" => "10",
        );
        $default["payment_currency"] = array(
            "name" => "payment_currency",
            "title" => __("Currency", "wpjobboard"),
            "value" => "USD",
        );
        $default["url"] = array(
            "name" => "url",
            "title" => __("Payment URL", "wpjobboard"),
            "value" => "http://example.com/wpjobboard/payment/pay/?payment_hash=5555-12345678901234567890",
        );
        $default["admin_url"] = array(
            "name" => "admin_url",
            "title" => __("Admin URL", "wpjobboard"),
            "value" => "http://example.com/wp-admin/admin.php?page=wpjb-payment&action=edit&id=5555",
        );
        $formatted["readable_id"] = array(
            "name" => "readable_id",
            "title" => __("ID", "wpjobboard"),
            "value" => "#005555"
        );
        $formatted["readable_to_pay"] = array(
            "name" => "readable_to_pay",
            "title" => __("To Pay", "wpjobboard"),
            "value" => wpjb_price(100, "USD")
        );
        $formatted["readable_paid"] = array(
            "name" => "readable_paid",
            "title" => __("Paid", "wpjobboard"),
            "value" => wpjb_price(100, "USD")
        );
        $formatted["readable_status"] = array(
            "name" => "readable_status",
            "title" => __("Payment Status", "wpjobboard"),
            "value" => "Completed"
        );
        
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
            "dates" => array(
                "header" => __("Dates", "wpjobboard"),
                "data" => $dates
            ),
            "formatted" => array(
                "header" => __("Formatted", "wpjobboard"),
                "data" => $formatted
            ),
            "meta" => array(
                "header" => __("Custom Fields", "wpjobboard"),
                "data" => $this->getMeta("payment")
            ),
        );

        return $data;
    }
    
    public function objectCompany()
    {
        $default = array();
        $dates = array();
        $formatted = array();
        
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 1111,
        );
        $default["post_id"] = array(
            "name" => "post_id",
            "title" => __("Post ID (associated post ID)", "wpjobboard"),
            "value" => 1009,
        );
        $default["user_id"] = array(
            "name" => "user_id",
            "title" => __("User ID", "wpjobboard"),
            "value" => 7777,
        );
        $default["company_name"] = array(
            "name" => "company_name",
            "title" => __("Company Name", "wpjobboard"),
            "value" => "Acme Corp.",
        );
        $default["company_slug"] = array(
            "name" => "company_slug",
            "title" => __("Slug (URL friendly name)", "wpjobboard"),
            "value" => "acme-corp",
        );
        $default["company_website"] = array(
            "name" => "company_website",
            "title" => __("Company Website", "wpjobboard"),
            "value" => "http://example.com/",
        );
        $default["company_info"] = array(
            "name" => "company_info",
            "title" => __("Company Info", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
        );
        $default["company_country"] = array(
            "name" => "company_country",
            "title" => __("Company Country", "wpjobboard"),
            "value" => 840,
        );
        $default["company_state"] = array(
            "name" => "company_state",
            "title" => __("Company State", "wpjobboard"),
            "value" => "NY",
        );
        $default["company_zip_code"] = array(
            "name" => "company_zip_code",
            "title" => __("Company Zip- Code", "wpjobboard"),
            "value" => "11238",
        );
        $default["company_location"] = array(
            "name" => "company_location",
            "title" => __("Company Location", "wpjobboard"),
            "value" => "200 Eastern Pkwy",
        );
        $default["is_public"] = array(
            "name" => "is_public",
            "title" => __("Is Public", "wpjobboard"),
            "value" => "1",
        );
        $default["is_active"] = array(
            "name" => "is_active",
            "title" => __("Is Active", "wpjobboard"),
            "value" => "1",
        );
        $default["is_verified"] = array(
            "name" => "is_verified",
            "title" => __("Is Verified", "wpjobboard"),
            "value" => "1",
        );
        $default["url"] = array(
            "name" => "url",
            "title" => __("URL", "wpjobboard"),
            "value" => "http://example.com/company/acme-corp/",
        );
        $default["admin_url"] = array(
            "name" => "admin_url",
            "title" => __("Admin URL", "wpjobboard"),
            "value" => "http://example.com/wp-admin/admin.php?page=wpjb-employers&action=edit&id=1111",
        );
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
            "user" => array(
                "header" => __("User", "wpjobboard"),
                "data" => $this->getUser("company")
            ),
            "country" => array(
                "header" => __("Country", "wpjobboard"),
                "data" => $this->getCountry()
            ),
            "meta" => array(
                "header" => __("Custom Fields", "wpjobboard"),
                "data" => $this->getMeta("company")
            ),
            "file" => array(
                "header" => __("Files", "wpjobboard"),
                "data" => $this->getFiles("company")
            ),
        );

        return $data;
    }
    
    public function objectApplication()
    {
        $default = array();
        $dates = array();
        $formatted = array();
        
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 2222,
        );
        $default["job_id"] = array(
            "name" => "job_id",
            "title" => __("Job ID", "wpjobboard"),
            "value" => 9999,
        );
        $default["user_id"] = array(
            "name" => "user_id",
            "title" => __("User ID", "wpjobboard"),
            "value" => 7777,
        );
        $default["applied_at"] = array(
            "name" => "applied_at",
            "title" => __("Created", "wpjobboard"),
            "value" => date("Y-m-d H:i:s"),
        );
        $default["applicant_name"] = array(
            "name" => "applicant_name",
            "title" => __("Applicant Name", "wpjobboard"),
            "value" => "John Doe",
        );
        $default["message"] = array(
            "name" => "message",
            "title" => __("Message", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
        );
        $default["email"] = array(
            "name" => "email",
            "title" => __("Applicant Email", "wpjobboard"),
            "value" => "john.doe@example.com",
        );
        $default["status"] = array(
            "name" => "status",
            "title" => __("Status", "wpjobboard"),
            "value" => "2",
        );
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
            "meta" => array(
                "header" => __("Custom Fields", "wpjobboard"),
                "data" => $this->getMeta("apply")
            ),
            "file" => array(
                "header" => __("Files", "wpjobboard"),
                "data" => $this->getFiles("apply")
            ),
        );

        return $data;
    }
    
    public function objectAlert()
    {
        $default = array();
        $dates = array();
        $formatted = array();
        
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 2222,
        );
        $default["user_id"] = array(
            "name" => "user_id",
            "title" => __("User ID", "wpjobboard"),
            "value" => 7777,
        );
        $default["email"] = array(
            "name" => "email",
            "title" => __("Email", "wpjobboard"),
            "value" => "john.doe@example.com",
        );
        $default["created_at"] = array(
            "name" => "created_at",
            "title" => __("Created", "wpjobboard"),
            "value" => date("Y-m-d H:i:s", strtotime("Today -2 DAY")),
        );
        $default["last_run"] = array(
            "name" => "last_run",
            "title" => __("Last Run", "wpjobboard"),
            "value" => date("Y-m-d H:i:s"),
        );
        $default["frequency"] = array(
            "name" => "frequency",
            "title" => __("Frequency", "wpjobboard"),
            "value" => 1,
        );
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
        );

        return $data;
    }
    
    public function objectResume()
    {
        $default = array();
        $dates = array();
        $formatted = array();
        
        $default["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 2222,
        );
        $default["post_id"] = array(
            "name" => "post_id",
            "title" => __("Post ID (associated post ID)", "wpjobboard"),
            "value" => 1002,
        );
        $default["user_id"] = array(
            "name" => "user_id",
            "title" => __("User ID", "wpjobboard"),
            "value" => 7777,
        );
        $default["candidate_slug"] = array(
            "name" => "candidate_slug",
            "title" => __("Slug (URL friendly name)", "wpjobboard"),
            "value" => "john-doe",
        );
        $default["phone"] = array(
            "name" => "phone",
            "title" => __("Phone Number", "wpjobboard"),
            "value" => "555-555-555",
        );
        $default["headline"] = array(
            "name" => "headline",
            "title" => __("Professional Headline", "wpjobboard"),
            "value" => "",
        );
        $default["description"] = array(
            "name" => "description",
            "title" => __("Description", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
        );
        $default["created_at"] = array(
            "name" => "created_at",
            "title" => __("Created", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -7 DAY")),
        );
        $default["modified_at"] = array(
            "name" => "modified_at",
            "title" => __("Last Modified", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -2 DAY")),
        );
        $default["candidate_country"] = array(
            "name" => "candidate_country",
            "title" => __("Country", "wpjobboard"),
            "value" => 840,
        );
        $default["candidate_state"] = array(
            "name" => "candidate_state",
            "title" => __("State", "wpjobboard"),
            "value" => "NY",
        );
        $default["candidate_zip_code"] = array(
            "name" => "candidate_zip_code",
            "title" => __("Zip-Code", "wpjobboard"),
            "value" => "10451",
        );
        $default["candidate_location"] = array(
            "name" => "candidate_location",
            "title" => __("City", "wpjobboard"),
            "value" => "1 E 161st St, Bronx",
        );
        $default["is_public"] = array(
            "name" => "is_public",
            "title" => __("Is Public", "wpjobboard"),
            "value" => "1",
        );
        $default["is_active"] = array(
            "name" => "is_active",
            "title" => __("Is Active", "wpjobboard"),
            "value" => "",
        );
        $default["url"] = array(
            "name" => "url",
            "title" => __("URL", "wpjobboard"),
            "value" => "http://example.com/resume/john-doe/",
        );
        $default["admin_url"] = array(
            "name" => "admin_url",
            "title" => __("Admin URL", "wpjobboard"),
            "value" => "http://example.com/wp-admin/admin.php?page=wpjb-resumes&action=edit&id=2222",
        );
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
            "user" => array(
                "header" => __("User", "wpjobboard"),
                "data" => $this->getUser("resume")
            ),
            "country" => array(
                "header" => __("Country", "wpjobboard"),
                "data" => $this->getCountry()
            ),
            "meta" => array(
                "header" => __("Custom Fields", "wpjobboard"),
                "data" => $this->getMeta("resume")
            ),
            "tag" => array(
                "header" => __("Tags", "wpjobboard"),
                "data" => $this->getTags()
            ),
            "education" => array(
                "header" => __("Education", "wpjobboard"),
                "data" => $this->getEducation()
            ),
            "experience" => array(
                "header" => __("Experience", "wpjobboard"),
                "data" => $this->getExperience()
            ),
            "file" => array(
                "header" => __("Files", "wpjobboard"),
                "data" => $this->getFiles("resume")
            ),
        );

        return $data;
    }
    
    public function objectUser()
    {
        $data = array();
        $dates = array();
        $formatted = array();
        
        $default["ID"] = array(
            "name" => "ID",
            "title" => __("ID"),
            "value" => 3333,
        );
        $default["user_login"] = array(
            "name" => "user_login",
            "title" => __("Username"),
            "value" => "test_user",
        );
        $default["user_nicename"] = array(
            "name" => "user_nicename",
            "title" => __("Slug (URL friendly name)", "wpjobboard"),
            "value" => "john_doe",
        );
        $default["user_email"] = array(
            "name" => "user_email",
            "title" => __("Email", "wpjobboard"),
            "value" => "john.doe@example.com",
        );
        $default["user_url"] = array(
            "name" => "user_url",
            "title" => __("URL", "wpjobboard"),
            "value" => "http://example.com/",
        );
        $default["user_registered"] = array(
            "name" => "user_registered",
            "title" => __("Registration Date", "wpjobboard"),
            "value" => date("Y-m-d H:i:s", strtotime("Today -2 DAY")),
        );
        $default["user_status"] = array(
            "name" => "user_status",
            "title" => __("Status", "wpjobboard"),
            "value" => "",
        );
        $default["display_name"] = array(
            "name" => "display_name",
            "title" => __("Display name publicly as"),
            "value" => "John Doe",
        );
        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
        );

        return $data;
    }
    
    public function objectContactForm()
    {
        $data = array();
        $dates = array();
        $formatted = array();
        
        $default= array();
        
        $cf = new Wpjb_Form_Resumes_Contact();
        foreach($cf->getFields() as $key => $field) {
            if($field->getType() == "hidden") {
                continue;
            }
            $default[$key] = array(
                "name" => $key,
                "title" => $field->getLabel(),
                "value" => ""
            );
        }

        
        $data = array(
            "default" => array(
                "header" => __("Properties", "wpjobboard"),
                "data" => $default
            ),
        );

        return $data;
    }
    
    public function getFiles($for)
    {
        $select = new Daq_Db_Query();
        $select->select("*");
        $select->from("Wpjb_Model_Meta t1");
        $select->where("meta_object = ?", $for);
        
        $result = $select->execute();
        $metas = array();
        
        foreach($result as $k => $v) {
            
            if($v->meta_type == 1) {
                $config = $this->getBuiltinFile($for, $v->name);
            } else {
                $config = $v->getConfig();
                if( empty( $config) ) {
                    continue;
                }
                $config["value"] = $this->generateMetaValue($v);
            }
            
            if($config === null || $config["value"] === null) {
                continue;
            }
            
            if($config["type"] !== "ui-input-file") {
                continue;
            }
            
            $metas[$v->name] = array(
                "name" => $v->name,
                "title" => $config["title"],
                "value" => array(
                    "url" => "http://example.com/wp-content/upload/wpjobboard/".$v->name."/9999/file.pdf",
                    "basename" => "file.pdf",
                    "size" => "5000"
                )
            );
        }
        
        return $metas;
    }
    
    public function getTags($for = "job")
    {
        $tags = array();
        
        $categories = Wpjb_Utility_Registry::getCategories();
        $c = array(
            "id" => "",
            "type" => "category",
            "slug" => "",
            "title" => ""
        );
        
        if(isset($categories[0])) {
            $c["id"] = $categories[0]->id;
            $c["slug"] = $categories[0]->slug;
            $c["title"] = $categories[0]->title;
        }
        
        $tags["category"] = array(
            "name" => "category",
            "title" => __("Categories", "wpjobboard"),
            "value" => $c
        );
        
        if($for != "job") {
            return $tags;
        }
        
        $types = Wpjb_Utility_Registry::getJobTypes();
        $t = array(
            "id" => "",
            "type" => "type",
            "slug" => "",
            "title" => ""
        );
        
        if(isset($types[0])) {
            $t["id"] = $types[0]->id;
            $t["slug"] = $types[0]->slug;
            $t["title"] = $types[0]->title;
        }
        
        $tags["type"] = array(
            "name" => "type",
            "title" => __("Job Types", "wpjobboard"),
            "value" => $t
        );
        
        return $tags;
    }
    
    public function getUser($for) {
        $user = $this->objectUser();
        $data = array();
        foreach($user["default"]["data"] as $key => $item) {
            $data[$key] = array(
                "name" => $item["name"],
                "title" => $item["title"],
                "value" => $item["value"],
            );
        }
        return $data;
    }
    
    public function getMeta($for)
    {
        $select = new Daq_Db_Query();
        $select->select("*");
        $select->from("Wpjb_Model_Meta t1");
        $select->where("meta_object = ?", $for);
        
        $result = $select->execute();
        $metas = array();
        
        foreach($result as $k => $v) {
            
            if($v->meta_type == 1) {
                $config = $this->getBuiltinMeta($for, $v->name);
            } else {
                $config = $v->getConfig();
                if( empty( $config) ) {
                    continue;
                }
                $config["value"] = $this->generateMetaValue($v);
            }
            
            if($config === null || $config["value"] === null) {
                continue;
            }
            
            $metas[$v->name] = array(
                "name" => $v->name,
                "title" => $config["title"],
                "value" => join(", ", (array)$config["value"]),
                "values" => (array)$config["value"],
            );
        }
        
        return $metas;
    }
    
    public function getBuiltinMeta($for, $name)
    {
        $defaults = array(
            "job" => array(
                "geo_latitude" => array(
                    "title" => __("Geographic Latitude (Number)", "wpjobboard"),
                    "value" => "40.748817"
                ),
                "geo_longitude" => array(
                    "title" => __("Geographic Longitude (Number)", "wpjobboard"),
                    "value" => "-73.985428"
                ),
                "geo_status" => array(
                    "title" => __("Is Geolocalized", "wpjobboard"),
                    "value" => "2"
                ),
                "job_description_format" => array(
                    "title" => __("Job Description Format ('html' or 'text')"),
                    "value" => "html"
                ),
                "job_source" => array(
                    "title" => __("Filled For Imported Jobs Only", "wpjobboard"),
                    "value" => ""
                )
            ),
            "company" => array(
                "geo_latitude" => array(
                    "title" => __("Geographic Latitude (Number)", "wpjobboard"),
                    "value" => "40.6408543"
                ),
                "geo_longitude" => array(
                    "title" => __("Geographic Longitude (Number)", "wpjobboard"),
                    "value" => "-73.9933537"
                ),
                "geo_status" => array(
                    "title" => __("Is Geolocalized", "wpjobboard"),
                    "value" => "2"
                ),
                "company_info_format" => array(
                    "title" => __("Company Info Format ('html' or 'text')"),
                    "value" => "html"
                ),
            ),
            "resume" => array(
                "geo_latitude" => array(
                    "title" => __("Geographic Latitude (Number)", "wpjobboard"),
                    "value" => "40.8024917"
                ),
                "geo_longitude" => array(
                    "title" => __("Geographic Longitude (Number)", "wpjobboard"),
                    "value" => "-73.9497141"
                ),
                "geo_status" => array(
                    "title" => __("Is Geolocalized", "wpjobboard"),
                    "value" => "2"
                ),
            ),
            "application" => array(
                "linkedin_profile_url" => array(
                    "title" => __("LinkedIn Profile URL", "wpjobboard"),
                    "value" => "https://linkedin.com/"
                ),
            )
        );
        
        if(isset($defaults[$for]) && isset($defaults[$for][$name])) {
            return $defaults[$for][$name];
        } else {
            return null;
        }
    }
    
    public function getBuiltinFile($for, $name)
    {
        $defaults = array(
            "job" => array(
                "company_logo" => array(
                    "title" => __("Company Logo", "wpjobboard"),
                    "type" => "ui-input-file",
                    "value" => array(
                        "url" => "http://example.com/wp-content/uploads/wpjobboard/job/9999/company-logo/company-logo.png",
                        "basename" => "company-logo.png",
                        "size" => "7300"
                    )
                ),
            ),
            "company" => array(
                "company_logo" => array(
                    "title" => __("Company Logo", "wpjobboard"),
                    "type" => "ui-input-file",
                    "value" => array(
                        "url" => "http://example.com/wp-content/uploads/wpjobboard/company/1111/company-logo/company-logo.png",
                        "basename" => "company-logo.png",
                        "size" => "4500"
                    )
                ),
            ),
            "resume" => array(
                "image" => array(
                    "title" => __("Your Photo", "wpjobboard"),
                    "type" => "ui-input-file",
                    "value" => array(
                        "url" => "http://example.com/wp-content/uploads/wpjobboard/resume/2222/image/my-avatar.jpg",
                        "basename" => "my-avatar.jpg",
                        "size" => "12100"
                    )
                ),
            ),
            "apply" => array(
                "file" => array(
                    "title" => __("File", "wpjobboard"),
                    "type" => "ui-input-file",
                    "value" => array(
                        "url" => "http://example.com/wp-content/uploads/wpjobboard/apply/5555/file/document.pdf",
                        "basename" => "document.pdf",
                        "size" => "220000"
                    )
                ),
            ),
        );
        
        if(isset($defaults[$for]) && isset($defaults[$for][$name])) {
            return $defaults[$for][$name];
        } else {
            return null;
        }
    }
    
    public function getEducation()
    {
        $detail = array();
        $detail["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 9999,
            "var" => '{$%s.education.0.%s}'
        );
        $detail["resume_id"] = array(
            "name" => "resume_id",
            "title" => __("Resume ID", "wpjobboard"),
            "value" => 2222,
            "var" => '{$%s.education.0.%s}'
        );
        $detail["type"] = array(
            "name" => "type",
            "title" => __("Type", "wpjobboard"),
            "value" => "2",
            "var" => '{$%s.education.0.%s}'
        );
        $detail["started_at"] = array(
            "name" => "started_at",
            "title" => __("Started At", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -2 Year")),
            "var" => '{$%s.education.0.%s}'
        );
        $detail["completed_at"] = array(
            "name" => "completed_at",
            "title" => __("Completed At", "wpjobboard"),
            "value" => date("Y-m-d"),
            "var" => '{$%s.education.0.%s}'
        );
        $detail["is_current"] = array(
            "name" => "is_current",
            "title" => __("Is Current", "wpjobboard"),
            "value" => "1",
            "var" => '{$%s.education.0.%s}'
        );
        $detail["grantor"] = array(
            "name" => "grantor",
            "title" => __("Grantor", "wpjobboard"),
            "value" => "New York University",
            "var" => '{$%s.education.0.%s}'
        );
        $detail["detail_title"] = array(
            "name" => "detail_title",
            "title" => __("Title", "wpjobboard"),
            "value" => "Accounting",
            "var" => '{$%s.education.0.%s}'
        );
        $detail["detail_description"] = array(
            "name" => "detail_description",
            "title" => __("Description", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
            "var" => '{$%s.education.0.%s}'
        );
        return $detail;
    }
    
    public function getExperience()
    {
        $detail = array();
        $detail["id"] = array(
            "name" => "id",
            "title" => __("ID"),
            "value" => 9999,
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["resume_id"] = array(
            "name" => "resume_id",
            "title" => __("Resume ID", "wpjobboard"),
            "value" => 2222,
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["type"] = array(
            "name" => "type",
            "title" => __("Type", "wpjobboard"),
            "value" => "2",
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["started_at"] = array(
            "name" => "started_at",
            "title" => __("Started At", "wpjobboard"),
            "value" => date("Y-m-d", strtotime("Today -2 Year")),
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["completed_at"] = array(
            "name" => "completed_at",
            "title" => __("Completed At", "wpjobboard"),
            "value" => date("Y-m-d"),
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["is_current"] = array(
            "name" => "is_current",
            "title" => __("Is Current", "wpjobboard"),
            "value" => "1",
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["grantor"] = array(
            "name" => "grantor",
            "title" => __("Grantor", "wpjobboard"),
            "value" => "ACME Corp.",
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["detail_title"] = array(
            "name" => "detail_title",
            "title" => __("Title", "wpjobboard"),
            "value" => "Senior Sales Assistant",
            "var" => '{$%s.experience.0.%s}'
        );
        $detail["detail_description"] = array(
            "name" => "detail_description",
            "title" => __("Description", "wpjobboard"),
            "value" => $this->getLoremIpsum(),
            "var" => '{$%s.experience.0.%s}'
        );
        return $detail;
    }
    
    public function generateText($min, $max) 
    {
        $find = array("\r\n", ".", ",");
        $repl = array(" ", "", "");
        $words = explode(" ", str_replace($find, $repl, $this->getLoremIpsum()));
        $count = count($words);
        
        $end = rand(0, $count-$max);
        $length = rand($min, $max);
        $use = array();
        
        for($i=$end-$length; $i<$end; $i++) {
            if(isset($words[$i])) {
                $use[] = $words[$i];
            }
        }

        return ucfirst(join(" ", $use));
    }
    
    public function generateMetaValue($meta)
    {
        if(in_array($meta->conf("type"), array("ui-input-label", "ui-input-label"))) {
            $value = null;
        } elseif(in_array($meta->conf("type"), array("ui-input-text", "ui-input-radio"))) {
            $value = $this->generateText(2, 6);
        } elseif(in_array($meta->conf("type"), array("ui-input-textarea"))) {
            $value = $this->generateText(15, 20);
        } elseif(in_array($meta->conf("type"), array("ui-input-checkbox", "ui-input-select"))) {
            $max = rand(1, 5);
            $value = array();
            for($i=0; $i<$max; $i++) {
                $value[] = $this->generateText(15, 20);
            }
        } else {
            $value = $meta->conf("type");
        }
        
        return $value;
    }
    
    public function getCountry()
    {
        $country = array();
        $country["code"] = array(
            "name" => "code",
            "title" => __("ISO 3166-1 numeric - Three Digit Country Code", "wpjobboard"),
            "value" => "820",
            "var" => '{$%s.country.%s}'
        );
        $country["iso2"] = array(
            "name" => "iso2",
            "title" => __("ISO 3166-1 apha-2 - Two Letter Country Code", "wpjobboard"),
            "value" => "US",
            "var" => '{$%s.country.%s}'
        );
        $country["iso3"] = array(
            "name" => "iso3",
            "title" => __("ISO 3166-1 apha-3 - Three Letter Country Code", "wpjobboard"),
            "value" => "USA",
            "var" => '{$%s.country.%s}'
        );
        $country["name"] = array(
            "name" => "name",
            "title" => __("Country Name", "wpjobboard"),
            "value" => "United States",
            "var" => '{$%s.country.%s}'
        );
        
        return $country;
    }
    
    public function getLoremIpsum() {
        $p = array();
        $p[] = 
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec placerat sodales nulla. ".
            "Quisque ullamcorper rutrum dolor, vitae eleifend turpis mattis sed. Fusce dignissim arcu non ".
            "faucibus laoreet. Nunc sodales orci sed nisi vehicula pulvinar. Aliquam et eros lacinia velit ".
            "egestas gravida quis venenatis magna. Cras rhoncus, metus rhoncus accumsan tempor, justo nulla ".
            "viverra metus, sit amet pulvinar quam massa quis libero. Phasellus ullamcorper tincidunt libero ".
            "eu varius.";
        $p[] = 
            "Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec suscipit purus eu ipsum ".
            "imperdiet, in finibus mauris posuere. Nulla elementum, purus eget fringilla tincidunt, lectus lacus ".
            "porta leo, non sagittis augue nibh a arcu. Duis tincidunt sem et quam tincidunt posuere. Aenean non ".
            "sollicitudin sem. Vivamus lacus ex, faucibus at finibus quis, semper nec libero. In condimentum ".
            "purus nec mauris tincidunt, ut mollis ligula ultrices. Nulla tempus sem leo, consequat dictum lorem ".
            "iaculis et. In faucibus elit sit amet mi ultricies pellentesque.";
        
        return join("\r\n", $p);
    }
    
    public function getJobVar()
    {
        return $this->collectVarsFrom($this->objectJob());
    }
    
    public function getResumeVar()
    {
        $resume = $this->collectVarsFrom($this->objectResume());
        $collect = array(
            "education" => $this->getEducation(),
            "experience" => $this->getExperience()
        );
        
        foreach($collect as $var => $loop) {
            $data = array();
            foreach($loop as $item) {
                $data[$item["name"]] = $item["value"];
            }
            $resume[$var] = array($data);
        }

        
        return $resume;
    }
    
    public function getAlertVar()
    {
        return $this->collectVarsFrom($this->objectAlert());
    }
    public function getApplicationVar()
    {
        return $this->collectVarsFrom($this->objectApplication());
    }
    
    public function getCompanyVar()
    {
        return $this->collectVarsFrom($this->objectCompany());
    }
    
    public function getPaymentVar()
    {
        return $this->collectVarsFrom($this->objectPayment());
    }
    
    public function getUserVar()
    {
        return $this->collectVarsFrom($this->objectUser());
    }
    
    protected function collectVarsFrom($vars)
    {
        $data = array();
        
        foreach($vars as $key => $groups) {
            foreach($groups["data"] as $name => $groupData) {
                if($key == "default") {
                    $data[$groupData["name"]] = $groupData["value"];
                } elseif($key == "meta") {
                    $data["meta"][$name] = $groupData;
                } elseif($key == "tag") {
                    if(!isset($data["tag"][$name])) {
                        $data["tag"][$name] = array();
                    }
                    $data["tag"][$name][] = $groupData["value"];
                } elseif($key == "file") {
                    if(!isset($data["file"][$name])) {
                        $data["file"][$name] = array();
                    }
                    $data["file"][$name][] = $groupData["value"];
                } else {
                    if(!isset($data[$key])) {
                        $data[$key] = array();
                    }
                    $data[$key][$name] = $groupData["value"];
                }
            }
        }
        
        return $data;
    }
    
    public function getEmailVars($email = null)
    {
        list($email_name) = explode("-", $email->name);
        
        $data = array(
            "notify_employer_register" => array(
                'username' => array(
                    'name' => 'username',
                    'title' => __("Username selected when registering", "wpjobboard"),
                    'value' => 'john_doe'
                ),
                'password' => array(
                    'name' => 'password',
                    'title' => __("Unencrypted password", "wpjobboard"),
                    'value' => 'qwerty1234'
                ),
                'login_url' => array(
                    'name' => 'login_url',
                    'title' => __("Full url to login form", "wpjobboard"),
                    'value' => 'http://example.com/login/'
                ),
                'manual_verification' => array(
                    'name' => 'manual_verification',
                    'title' => __("If employer manual verification is active.", "wpjobboard"),
                    'value' => 0,
                ),
            ), 
            "notify_canditate_register" => array(
                'username' => __("Username selected when registering", "wpjobboard"),
                'password' => __("Unencrypted password", "wpjobboard"),
                'login_url' => array(
                    'name' => 'login_url',
                    'title' => __("Full url to login form", "wpjobboard"),
                    'value' => 'http://example.com/login/'
                ),
                'manual_verification' => __("If candidate manual verification is active.", "wpjobboard"),
            ), 
            "notify_admin_grant_access" => array(
                'company_edit_url' => array(
                    'name' => 'company_edit_url',
                    'title' => __("Absolute URL to company profile page (in wp-admin)", "wpjobboard"),
                    'value' => 'http://example.com/wp-admin/admin.php?page=wpjb-employers&action=edit&id=2222'
                )
            ), 
            "notify_job_alerts" => array(
                'unsubscribe_url' => array(
                    'name' => 'unsubscribe_url',
                    'title' => __("URL user can use to unsubscribe from email alerts", "wpjobboard"),
                    'value' => 'http://example.com/wpjobboard/action/alert/?delete=1234567890'
                ),
                'jobs' => array(
                    'name' => 'jobs',
                    'title' => __("Array of matched Job objects", "wpjobboard"),
                    'value' => array($this->getJobVar())
                )
            ), 
            "notify_employer_resume_paid" => array(
                'resume_unique_url' => array(
                    'name' => 'resume_unique_url',
                    'title' => __("Unique URL to Resume details page", "wpjobboard"),
                    'value' => 'http://example.com/resume/john-doe/?access=1234567890'
                )
            ), 
            "notify_applicant_status_change" => array(
                'status' => array(
                    'name' => 'status',
                    'title' => __("Application status", "wpjobboard"),
                    'value' => 'New'
                )
            ),
            "notify_account_approved" => array(
                'login_url' => array(
                    'name' => 'login_url',
                    'title' => __("Full url to login form", "wpjobboard"),
                    'value' => 'http://example.com/login/'
                )
            )
        );
        
        if(isset($data[$email->name])) {
            $customs = $data[$email->name];
        } elseif(isset($data[$email_name])) {
            $customs = $data[$email_name];
        } else {
            $customs = array();
        }
        
        return apply_filters("wpjb_email_template_customs", $customs, $email->name);
    }
    
    public function getEmailObjects($email)
    {
        list($email_name) = explode("-", $email->name);
        
        $data = array(
            "notify_admin_new_job" => array("job", "payment", "company"),
            "notify_admin_new_employer" => array("company"),
            "notify_admin_new_candidate" => array("resume"),
            "notify_admin_payment_received" => array("payment"),
            "notify_employer_new_job" => array("job", "payment", "company"),
            "notify_employer_job_expires" => array("job"),
            "notify_admin_new_application" => array("job", "application", "resume"),
            "notify_admin_general_application" => array("application", "resume"),
            "notify_applicant_applied" => array("job", "application"),
            "notify_employer_register" => array("company"), //3
            "notify_canditate_register" => array("resume"), //3
            "notify_admin_grant_access" => array("company"), //1
            "notify_employer_verify" => array("company"),
            "notify_employer_new_application" => array("job", "application", "resume"),
            "notify_job_alerts" => array("alert"), //3
            "notify_employer_job_paid" => array("job"),
            "notify_employer_resume_paid" => array("resume"),
            "notify_applicant_status_change" => array("job", "application"),
            "notify_account_approved" => array("user"),
            "notify_candidate_message" => array("company", "resume", "contact_form")
        );
        
        if(isset($data[$email->name])) {
            $objects = $data[$email->name];
        } elseif(isset($data[$email_name])) {
            $objects = $data[$email_name];
        } else {
            $objects = array();
        }
        
        return apply_filters("wpjb_email_template_objects", $objects, $email->name);
    }
    
    public function getVariables()
    {
        return array(
            array(
                "var" => "job",
                "title" => __("Job Variable", "wpjobboard"),
                "item" => $this->objectJob()
            ),
            array(
                "var" => "payment",
                "title" => __("Payment Variable", "wpjobboard"),
                "item" => $this->objectPayment()
            ),
            array(
                "var" => "company",
                "title" => __("Company Variable", "wpjobboard"),
                "item" => $this->objectCompany()
            ),
            array(
                "var" => "application",
                "title" => __("Application Variable", "wpjobboard"),
                "item" => $this->objectApplication()
            ),
            array(
                "var" => "alert",
                "title" => __("Alert Variable", "wpjobboard"),
                "item" => $this->objectAlert()
            ),
            array(
                "var" => "resume",
                "title" => __("Resume Variable", "wpjobboard"),
                "item" => $this->objectResume()
            ),
            array(
                "var" => "user",
                "title" => __("User Variable", "wpjobboard"),
                "item" => $this->objectUser()
            ),
            array(
                "var" => "contact_form",
                "title" => __("Contact Form Variable", "wpjobboard"),
                "item" => $this->objectContactForm()
            )
        );
    }
    
    public function getTemplateDemoData($email) 
    {
        $objects = $this->getEmailObjects($email);
        $customs = $this->getEmailVars($email);
        $data = array();
        
        foreach($objects as $object) {
            $func = sprintf("get%sVar", ucfirst($object));
            $data[$object] = $this->$func();
        }
        foreach($customs as $custom) {
            $data[$custom["name"]] = $custom["value"];
        }
        
        return $data;
    }
}
