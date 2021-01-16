<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ResumeSearch
 *
 * @author greg
 */
class Wpjb_Model_ResumeSearch extends Daq_Db_OrmAbstract
{
    protected $_name = "wpjb_resume_search";
    
    protected function _init()
    {
        
    }
    
    public static function createFrom(Wpjb_Model_Resume $resume)
    {
        $query = new Daq_Db_Query();
        $object = $query->select()
            ->from(__CLASS__." t")
            ->where("resume_id = ?", $resume->getId())
            ->limit(1)
            ->execute();

        if(empty($object)) {
            $object = new self;
        } else {
            $object = $object[0];
        }

        $country = Wpjb_List_Country::getByCode($resume->candidate_country);

        $location = array(
            $country['iso2'],
            $country['iso3'],
            $country['name'],
            $resume->candidate_state,
            $resume->candidate_location,
            $resume->candidate_zip_code
        );

        $uid = $resume->user_id;
        $fullname = get_user_meta($uid, "first_name", true)." ".get_user_meta($uid, "last_name", true);
        
        $narrow = array();
        $broad = array();
        
        $narrow[] = $resume->headline;
        
        $broad[] = $resume->headline;
        $broad[] = strip_tags($resume->description);
        
        foreach($resume->getDetails() as $detail) {
            $narrow[] = $detail->detail_title;
            $narrow[] = $detail->grantor;
            
            $broad[] = $detail->detail_title;
            $broad[] = $detail->grantor;        
            $broad[] = strip_tags($detail->detail_description);
        }
        
        $object->resume_id = $resume->getId();
        $object->fullname = $fullname;
        $object->location = join(" ", $location);
        $object->details = join("\r\n", $narrow);
        $object->details_all = join("\r\n", $broad);
        $object->save();
        
        do_action("wpjb_customize_resume_search", $object);
    }
    
    protected static function _found($query, $grouped)
    {
        if($grouped) {
            return count($query->fetchAll());
        } else {
            return $query->fetchColumn();
        }
    }
    
    public static function search($params)
    {
        $query = null;
        $location = null;
        $radius = null;
        $fullname = null;
        $page = null;
        $category = null;
        $email = null;
        $featured_level = null;

        /**
         * @var $count int
         * items per page or maximum number of elements to return
         */
        $count = 25;
        $date_from = null;
        $date_to = null;
        
        /**
         * @var $sort_order mixed
         * string or array, specify sort column and order (either DESC or ASC),
         * you can add more then one sort order. 
         */
        $sort_order = "t1.featured_level, t1.modified_at DESC, t1.id DESC";
        
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
         * @var $filter string
         * narrow jobs to certain type:
         * - all: all resumes
         * - active: only active resumes
         * - inactive: inactive resumes
         */
        $filter = "active";
        
        extract($params);
        
        $select = new Daq_Db_Query();
        $select->select();
        $select->from("Wpjb_Model_Resume t1");
        
        if($filter == "active") {
            $select->where("is_active = 1");
            $select->where("is_public = 1");
        } elseif($filter == "inactive") {
            $select->where("(is_active = 0 OR is_public = 0)");
        }

        $is_searchable = wpjb_conf("cv_members_are_searchable", 0);
        if( isset( $is_searchable[0] ) && $is_searchable[0] == 1) {

            $mq = new Daq_Db_Query();
            $meta_id = $mq->select()->from("Wpjb_Model_Meta t")->where("t.name = ?", "is_searchable")->where("t.meta_object = ?", "pricing")->fetchColumn();
            
            $q = new Daq_Db_Query();
            $q->select()->from("Wpjb_Model_Pricing tp1")->join("tp1.meta tp2")->where("tp1.is_active = ?", 1);
            $t1 = Daq_Db::getInstance()->quoteInto("tp2.meta_id = ?", $meta_id);
            $q->where("($t1 AND tp2.value LIKE ?)", 1);
            
            $pricings = array();
            foreach( $q->execute() as $p) {
                $pricings[] = $p->id;
            }
            
            $select->join("t1.membership t4");
            $select->where( "t4.expires_at >= ?", date( "Y-m-d" ) );
            $select->where( "t4.started_at <= ?", date( "Y-m-d" ) );
            $select->where( "t4.package_id IN(?)", $pricings);
        }
        
        if($sort_order) {
            $select->order($sort_order);
        }
        
        if($featured_level) {
            $select->where("t1.featured_level >= ?", $featured_level);
        }
        
        if($email) {
            $select->join("t1.user t2");
            $select->where("t2.user_email LIKE ?", "%$email%");
        }
        
        if($date_from) {
            $select->where("modified_at >= ?", $date_from);
        }

        if($date_to) {
            $select->where("modified_at <= ?", $date_to);
        }
        
        if($fullname || $query || $location) {
            $select->join("t1.search t2");
        }
        
        if(isset($country) && $country) {
            $select->where("t1.candidate_country = ?", $country);
        }
        
        if($radius && $location) {
            list($distance, $dunit) = explode(" ", trim($radius));
            $select->having("distance < " . intval($distance));
            $select->order("distance ASC");
        } elseif($location) {
            $locations = explode(' ', $location);
            foreach($locations as $l) {
                $l = trim($l);
                $l = rtrim($l, ',');
                $select->where("t2.location LIKE ?", "%$l%");
            }
        }
        
        if($fullname) {
            $select->where("t2.fullname LIKE ?", "%$fullname%");
        } 
        
        if(!empty($category)) {
            if(!is_array($category)) {
                $category = explode(",", $category);
            }
            $select->join("t1.tagged t2c", "t2c.object='resume'");
            $select->where("t2c.tag_id IN(?)", array_map("intval", array_map("trim", $category)));
        }
        
        $groupResults = false;
        
        if(!empty($meta)) {
            $meta = apply_filters( "wpjr_resume_query_meta", $meta );
            
            $resume = new Wpjb_Model_Resume();
            $m = 1;
            foreach($meta as $k => $v) {
                if(!is_numeric($k)) {
                    $k = $resume->meta->$k->id;
                }
                /*
                foreach((array)$v as $ve) {
                    $select->join("t1.meta t3m$m");
                    $t1 = Daq_Db::getInstance()->quoteInto("t3m$m.meta_id = ?", $k);
                    $t2 = Daq_Db::getInstance()->quoteInto("t3m$m.value = ?", $ve);
                    $select->where("($t1 AND $t2)");
                    $m++;
                }
                */
                
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
        
        $fulltext = "MATCH(t2.details) AGAINST (? IN BOOLEAN MODE)";
        $q = $fulltext;
        $fulltext = str_replace("?", '\'"'.esc_sql($query).'"\'', $fulltext);
        
        $itemsFound = 0;
        $t = null;
        $custom_columns = "";
        
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

            $qLng = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lng WHERE tmp_lng.object_id=t1.id AND meta_id=14)";
            $qLat = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lat WHERE tmp_lat.object_id=t1.id AND meta_id=13)";

            $custom_columns .= ", @lng := $qLng AS `lng`, @lat := $qLat AS `lat`, ($u*acos(cos(radians($lat))*cos( radians(@lat))*cos(radians(@lng)-radians($lng))+sin(radians($lat))*sin(radians(@lat)))) AS `distance`";
        }
        
        $select->select("COUNT(*) as `cnt`".$custom_columns);
        
        if($query && strlen($query)<=apply_filters("wpjb_fulltext_min_chars", 3)) {
            $select->where("t2.details LIKE ?", "%$query%");
            $itemsFound = self::_found($select, $groupResults);
        } elseif(strlen($query)>3) {
            foreach(array(1, 2, 3) as $t) {
                
                $test = clone $select;
                if($t == 1) {
                    $test->where($fulltext);
                } elseif($t == 2) {
                    $test->where($q, "+".  str_replace(" ", " +", $query));
                } else {
                    $test->where($q, $query);
                }

                $itemsFound = self::_found($test, $groupResults);
                if($itemsFound>0) {
                    break;
                }

            }
        } else {
            $itemsFound = self::_found($select, $groupResults);
        }
        
        if($t>0) {
            if($t == 1) {
                $select->where($fulltext);
            } elseif($t == 2) {
                $select->where($q, "+".  str_replace(" ", " +", $query));
            } else {
                $select->where($q, $query);
            }
        }
        
        if($query && is_admin()) {
            $select->orWhere("t2.fullname LIKE ?", "%$query%");
        }
        
        $select = apply_filters("wpjr_resume_query", $select, $params);
        
        $itemsFound = $select->select("COUNT(*) AS cnt".$custom_columns)->fetchColumn();

        if($groupResults) {
            $select->group("t1.id");
        }
        
        $select->select("*".$custom_columns);
        
        if($page && $count) {
            $select->limitPage($page, $count);
        }
        
        if($count_only) {
            return $itemsFound;    
        }
        
        if($ids_only) {
            $select->select("t1.id".$custom_columns);
            $list = $select->getDb()->get_col($select->toString());
        } else {   
            $list = $select->execute();
        }
        
        $response = new stdClass;
        $response->resume = $list;
        $response->page = (int)$page;
        $response->perPage = (int)$count;
        $response->count = count($list);
        $response->total = (int)$itemsFound;
        
        if($response->perPage > 0) {
            $response->pages = ceil($response->total/$response->perPage);
        } else {
            $response->pages = 1;
        }
        
        return $response;
    }
}

?>
