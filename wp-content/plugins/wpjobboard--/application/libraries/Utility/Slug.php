<?php
/**
 * Description of Slug
 *
 * @author greg
 * @package 
 */

class Wpjb_Utility_Slug
{
    const MODEL_JOB = "job";
    const MODEL_RESUME = "resume";
    const MODEL_COMPANY = "company";
    const MODEL_TYPE = "type";
    const MODEL_CATEGORY = "category";

    public static function generate($model, $title, $id = null)
    {
        $insatnce = Wpjb_Project::getInstance();
        $slug = self::_default($model, $title, $id);

        if($insatnce->env("uses_cpt") && in_array($model, array("job", "company", "resume"))) {
            return self::_cpt($model, $slug, $id);
        } else {
            return $slug;
        }
    }
    
    protected static function _default($model, $title, $id = null)
    {   
        $list = array(
            "company"   => array("model" => "Wpjb_Model_Company", "field" => "company_slug"),
            "job"       => array("model" => "Wpjb_Model_Job", "field" => "job_slug"),
            "type"      => array("model" => "Wpjb_Model_Tag", "field" => "slug"),
            "category"  => array("model" => "Wpjb_Model_Tag", "field" => "slug"),
            "resume"    => array("model" => "Wpjb_Model_Resume", "field" => "candidate_slug")
        );

        $slug = sanitize_title($title);
        $slug = preg_replace("([^A-z0-9\-]+)", "", $slug);
        
        $slug = apply_filters("wpjb_generate_slug", $slug, $model, $title, $id);
        $isUnique = true;

        $query = new Daq_Db_Query();
        $query->select("t.".$list[$model]['field'])
            ->from($list[$model]['model']." t")
            ->where("(".$list[$model]['field']." = ?", $slug)
            ->orWhere($list[$model]['field']." LIKE ? )", $slug."%");

        if($id>0) {
            $query->where("id <> ?", $id);
        }

        $field = "t__".$list[$model]['field'];
        $list = array();
        $c = 0;

        foreach($query->fetchAll() as $q) {
            $list[] = $q->$field;
            $c++;
        }

        if($c > 0) {
            $isUnique = false;
            $i = 1;
            do {
                $i++;
                $newSlug = $slug."-".$i;
                if(!in_array($newSlug, $list)) {
                    $isUnique = true;
                }
            } while(!$isUnique);
        } else {
            $newSlug = $slug;
        }

        return $newSlug;
    }
    
    protected static function _cpt($model, $title, $id = 0) {
        
        $list = array(
            "company"   => array("model" => "Wpjb_Model_Company", "field" => "company_slug"),
            "job"       => array("model" => "Wpjb_Model_Job", "field" => "job_slug"),
            "resume"    => array("model" => "Wpjb_Model_Resume", "field" => "candidate_slug")
        );
        
        $class = $list[$model]["model"];
        $object = new $class($id);
        
        $slug = $title;
        $post_ID = (int)$object->post_id;
        $post_status = "publish";
        $post_type = $model;
        $post_parent = 0;
        
        return wp_unique_post_slug($slug, $post_ID, $post_status, $post_type, $post_parent);
    }
}

?>