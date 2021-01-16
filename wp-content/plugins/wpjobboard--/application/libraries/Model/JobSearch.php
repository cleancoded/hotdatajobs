<?php
/**
 * Description of JobSearch
 *
 * @author greg
 * @package 
 */

class Wpjb_Model_JobSearch extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_job_search";
    
    protected function _init()
    {
        
    }

    public static function createFrom(Wpjb_Model_Job $job)
    {
        $query = new Daq_Db_Query();
        $object = $query->select()
            ->from(__CLASS__." t")
            ->where("job_id = ?", $job->getId())
            ->limit(1)
            ->execute();

        if(empty($object)) {
            $object = new self;
        } else {
            $object = $object[0];
        }

        $country = Wpjb_List_Country::getByCode($job->job_country);

        $location = array(
            $country['iso2'],
            $country['iso3'],
            $country['name'],
            $job->job_state,
            $job->job_city,
            $job->job_zip_code
        );

        $object->job_id = $job->getId();
        $object->title = $job->job_title;
        $object->description = strip_tags($job->job_description);
        $object->company = $job->company_name;
        $object->location = join(" ", $location);
        $object->save();
        
        do_action("wpjb_customize_job_search", $object);
    }
    
    protected static function _found($query, $grouped)
    {
        if($grouped || $query->get("having")) {
            return count($query->fetchAll());
        } else {
            return $query->fetchColumn();
        }
    }
    
    public static function search($params = array())
    {
        $category = null;
        $type = null;
        $posted = null;
        $query = null;
        $location = null;
        $page = null;
        $date_from = null;
        $date_to = null;
        $expires_from = null;
        $expires_to = null;
        
        /**
         * @var $exclude_imported boolean
         * set this value to true if you only want to get jobs entered manually
         * or imported from XML file (exclude jobs from Indeed, CB and etc.)
         */
        $exclude_imported = false;
        
        /**
         * @var $radius string
         * location radius either in km (for example "5 km") or miles ("5 mi.")
         */
        $radius = null;

        /**
         * @var $count int
         * items per page or maximum number of elements to return
         */
        $count = 20;
        
        /**
         * @var $sort_order mixed
         * string or array, specify sort column and order (either DESC or ASC),
         * you can add more then one sort order. 
         */
        $sort_order = "t1.is_featured DESC, t1.job_created_at DESC, t1.id DESC";
        
        /**
         * @var $count_only boolean
         * Count jobs only
         */
        $count_only = false;
        
        /**
         * Return only list of job ids instead of objects
         * @var $ids_only boolean
         */
        $ids_only = false;
        
        /**
         * Do not show filled jobs on the list
         * @var $hide_filled boolean
         */
        $hide_filled = wpjb_conf("front_hide_filled", false);
        
        /**
         * @var $filter string
         * narrow jobs to certain type:
         * - all: all jobs
         * - active: only active jobs
         * - expired: expired jobs
         * - expiring: jobs which will expire in X days
         * - awaiting: jobs awaiting approval
         * - new: posted no longer than X days ago
         * - inactive: jobs deactivated
         */
        $filter = "active";
        
        if(isset($params["sort_order"]) && empty($params["sort_order"])) {
            $params["sort_order"] = $sort_order;
        }
        
        extract($params);
        
        $groupResults = false;
        
        $select = new Daq_Db_Query();
        $select = $select->select("t1.*");
        $select->from("Wpjb_Model_Job t1");
        
        if( $filter == "active" ) {
            $select->where( "t1.is_active = 1");
            $select->where( "t1.job_created_at <= ?", date( "Y-m-d" ) );
            $select->where( "t1.job_expires_at >= ?", date( "Y-m-d" ) );
        } elseif( $filter == "expired" ) {
            $select->where( "t1.job_expires_at < ?", date( "Y-m-d" ) );
        } elseif( $filter == "expiring" ) {
            $time = strtotime( "today +5 day" );
            $select->where( "t1.is_active = 1" );
            $select->where( "t1.job_expires_at <= ?", date( "Y-m-d", $time ) );
            $select->where( "t1.job_expires_at >= ?", date( "Y-m-d" ) );
        } elseif( $filter == "awaiting" ) {
            $select->where( "t1.is_approved = 0" );
        } elseif( $filter == "new" ) {
            $time = strtotime( "today -5 day" );
            $select->where( "t1.job_created_at >= ?", date( "Y-m-d", $time ) );
        } elseif( $filter == "unread" ) {
            $select->where( "t1.read = 0" );
        } elseif( $filter == "inactive" ) {
            $select->where( "t1.is_active = 0" );
            $select->where( "t1.is_approved = 1" );
        } elseif( $filter == "filled" ) {
            $select->where( "t1.is_filled = 1" );
        }
        
        if(is_array($sort_order)) {
            $select->order(join(",", $sort_order));
        } else {
            $select->order($sort_order);
        }
        
        if( $hide_filled && $filter != "filled" ) {
            $select->where("is_filled = 0");
        }
        
        if( isset($is_featured) && $is_featured == 1 ) {
            $select->where("t1.is_featured = 1");
        } elseif( isset($is_featured) && $is_featured == -1 ) {
            $select->where("t1.is_featured = 0");
        }
        
        if(isset($employer_id) && $employer_id) {
            if(!is_array($employer_id)) {
                $employer_id = explode(",", $employer_id);
            }
            $select->where("t1.employer_id IN(?)", array_map("intval", $employer_id));
        }
        
        if(isset($country) && $country) {
            $select->where("t1.job_country = ?", $country);
        } elseif( isset( $job_country ) && $job_country ) {
            $select->where("t1.job_country = ?", $job_country);
        }
        
        if(isset($state) && $state) {
            $select->where("t1.job_state = ?", $state);
        } elseif( isset( $job_state ) && $job_state ) {
            $select->where("t1.job_state = ?", $job_state);
        }
        
        if(isset($city) && $city) {
            $select->where("t1.job_city = ?", $city);
        } elseif(isset($job_city) && $job_city) {
            $select->where("t1.job_city = ?", $job_city);
        }
        
        if(isset($id) && $id) {
            $select->where("t1.id IN(?)", (array)$id);
        }
        
        if(isset($id__not_in) && $id__not_in) {
            $select->where("t1.id NOT IN(?)", (array)$id__not_in);
        }
        
        if(!empty($category)) {
            if(!is_array($category)) {
                $category = explode(",", $category);
            }
            $select->join("t1.tagged t2c", "t2c.object='job'");
            $select->where("t2c.tag_id IN(?)", array_map("intval", $category));
            $groupResults = true;
        }
        if(!empty($type)) {
            if(!is_array($type)) {
                $type = explode(",", $type);
            }
            $select->join("t1.tagged t2t", "t2t.object='job'");
            $select->where("t2t.tag_id IN(?)", array_map("intval", $type));
            $groupResults = true;
        }

        if($exclude_imported) {
            $select->joinLeft("t1.meta t3ei", "t3ei.meta_id = 16");
            $select->where("t3ei.value IS NULL");
        }

        if(!empty($meta)) {
            $meta = apply_filters( "wpjb_jobs_query_meta", $meta );
            
            $job = new Wpjb_Model_Job();
            $m = 1;
            foreach($meta as $k => $v) {
                if(!is_numeric($k)) {
                    $k = $job->meta->$k->id;
                }
                
                $metaObject = new Wpjb_Model_Meta($k);
                $metaType = $metaObject->conf("type");
                $match = "";
                
                // match: all, one-or-more, exact, like
                
                if(in_array($metaType, array("ui-input-text", "ui-input-textarea"))) {
                    $select->join("t1.meta t3m$m");
                    $t1 = Daq_Db::getInstance()->quoteInto("t3m$m.meta_id = ?", $k);
                    $select->where("($t1 AND t3m$m.value LIKE ?)", "%$v%");
                } else {
                    $select->join("t1.meta t3m$m");
                    $t1 = Daq_Db::getInstance()->quoteInto("t3m$m.meta_id = ?", $k);
                    $select->where("($t1 AND t3m$m.value IN(?))", $v);
                }
                    
                $m++;
            }
            $groupResults = true;
        }

        if($date_from) {
            $select->where("job_created_at >= ?", $date_from);
        }

        if($date_to) {
            $select->where("job_created_at <= ?", $date_to);
        }
        
        if($expires_from) {
            $select->where("job_expires_at >= ?", $expires_from);
        }

        if($expires_to) {
            $select->where("job_expires_at <= ?", $expires_to);
        }
        
        if(strlen($query)>0 || strlen($location)>0) {
            $select->join("t1.search t4");
        }
        
        if($radius && $location) {
            list($distance, $dunit) = explode(" ", trim($radius));
            $select->having("distance < " . intval($distance));
            $select->order("distance ASC");
            $groupResults = true;
        } elseif($location) {
            $locations = explode(' ', $location);
            foreach($locations as $l) {
                $l = trim($l);
                $l = rtrim($l, ',');
                $select->where("t4.location LIKE ?", "%$l%");
            }
        }
        
        if($groupResults) {
            $select->group("t1.id");
        }
        
        $fulltext = "MATCH(t4.title, t4.description, t4.company, t4.location)";
        $fulltext.= "AGAINST (? IN BOOLEAN MODE)";
        
        $q = $fulltext;
        $fulltext = str_replace("?", '\'"'.esc_sql($query).'"\'', $q);
        $itemsFound = 0;
        $t = null;
        
        $custom_columns = apply_filters("wpjb_jobs_query_select_columns", "");
        if(is_array($custom_columns)) {
            $custom_columns = join(", ", $custom_columns);
        }
        if(!empty($custom_columns)) {
            $custom_columns = ", ".ltrim($custom_columns, ", ");
        }
        if($radius && $location) {

            list($distance, $dunit) = explode(" ", trim($radius));

            if($dunit == "km") {
                $u = 6371;
            } else {
                $u = 3959;
            }

            $addr = Wpjb_Service_GoogleMaps::locate($location);

            $lng = $addr->geo_longitude;
            $lat = $addr->geo_latitude;
            $prefix = $select->getDb()->prefix;

            $qLng = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lng WHERE tmp_lng.object_id=t1.id AND meta_id=6 LIMIT 1)";
            $qLat = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lat WHERE tmp_lat.object_id=t1.id AND meta_id=5 LIMIT 1)";

            $custom_columns .= ", @lng := $qLng AS `lng`, @lat := $qLat AS `lat`, ($u*acos(cos(radians($lat))*cos( radians(@lat))*cos(radians(@lng)-radians($lng))+sin(radians($lat))*sin(radians(@lat)))) AS `distance`";
        }
        
        $select->select("COUNT(*) as `cnt`".$custom_columns);
        $select = apply_filters("wpjb_jobs_query", $select, $params);
        
        if($query && strlen($query)<=apply_filters("wpjb_fulltext_min_chars", 3)) {
            $select->where("(t4.title LIKE ?", "%$query%");
            $select->orWhere("t4.description LIKE ?", "%$query%");
            $select->orWhere("t4.company LIKE ?", "%$query%");
            $select->orWhere("t4.location LIKE ?)", "%$query%");
            $itemsFound = self::_found($select, $groupResults);
        } elseif(strlen($query)>3) {
            foreach(array(1, 2, 3) as $t) {
                
                $test = clone $select;
                if($t == 1) {
                    $test->where($fulltext);
                } elseif($t == 2) {
                    $modifiers = "+".  str_replace(" ", " +", trim($query));
                    $modifiers = str_replace(array("+-", "++"), array("+", "+"), $query);
                    $test->where($q, $modifiers);
                } else {
                    $test->where($q, $query);
                }

                $itemsFound = self::_found($test, $groupResults);;
                if($itemsFound>0) {
                    break;
                }

            }
        } else {
            $itemsFound = self::_found($select, $groupResults);;
            
        }

        if($t>0) {
            if($t == 1) {
                $select->where($fulltext);
            } elseif($t == 2) {
                $modifiers = "+".  str_replace(" ", " +", $query);
                $modifiers = str_replace(array("+-", "++"), array("+", "+"), $query);
                $test->where($q, $modifiers);
            } else {
                $select->where($q, $query);
            }
        }
        
        if($groupResults) {
            $select->group("t1.id");
        }
        
        $select->select("t1.*".$custom_columns);
        
        if($page && $count) {
            $select->limitPage($page, $count);
        }
        
        if($count_only) {
            return $itemsFound;    
        }
        
        if($ids_only) {
            $select->select("t1.id".$custom_columns);
            $jobList = $select->getDb()->get_col($select->toString());
        } else {   
            $jobList = $select->execute();
        }
        
        $response = new stdClass;
        $response->job = $jobList;
        $response->page = $page;
        $response->perPage = $count;
        $response->count = count($jobList);
        $response->total = $itemsFound;
        
        if($response->perPage > 0) {
            $response->pages = ceil($response->total/$response->perPage);
        } else {
            $response->pages = 1;
        }
        
        $link = wpjb_api_url("xml/rss");
        $link2 = wpjb_link_to("search");
        $p2 = $params;
        unset($p2["page"]);
        unset($p2["count"]);
        $q2 = http_build_query($p2);
        $glue = "?";
        if(stripos($link, "?")) {
            $glue = "&";
        }
        $response->url = new stdClass;
        $response->url->feed = $link.$glue.$q2;
        $response->url->search = $link2.$glue.$q2;

        
        return $response;
    }

}

?>