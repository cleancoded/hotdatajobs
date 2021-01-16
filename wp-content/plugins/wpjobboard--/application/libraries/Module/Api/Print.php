<?php
/**
 * Description of Plain
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Api_Print extends Daq_Controller_Abstract
{
    public function indexAction() {
        
        $id = absint( $_GET['id'] );
        $application = new Wpjb_Model_Application( $id );
        
        if( !$application->exists() ) {
            throw new Exception( sprintf( "Object with ID %d does not exist.", $id ) );
        }
        
        $job = new Wpjb_Model_Job($application->job_id);
        $company = Wpjb_Model_Company::current();
        
        if( ( $company == null || $company->id != $job->employer_id ) && !current_user_can( 'manage_options' ) ) {
            throw new Exception( sprintf( "You do not own Application with ID %d.", $id ) );
        }

        $current_status = wpjb_get_application_status($application->status);
        
        wp_head();
        ob_start() 
        ?>
        <body>
            <script type="text/javascript">
                window.onload = function() { window.print(); }
            </script>
            <style type="text/css">
                .print-flex {
                    display: flex;
                    flex-direction: column;
                    flex-basis: 100%;
                }
                .print-flex-row {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between; 
                    align-content: space-between;
                }
                div.print-flex-avatar {
                    flex-direction: column;
                    justify-content: flex-start;
                    align-items: center;
                    text-align: center;
                    font-weight: bold;
                    font-size: 1.7em;
                    margin-bottom: 2%;
                }
                div.print-flex-avatar img {
                    border-radius: 50%;
                    margin-bottom: 2%;
                }
                .print-flex-row .print-flex-row-el {
                    display: flex;
                    flex: 1;
                    
                }
                .print-flex-column .print-flex-row-el {
                    display: flex;
                    border-bottom: 1px solid #aaa;
                    padding: 5px 0px;
                }
                .print-flex-column {
                    display: flex;
                    flex-direction: column;
                }
                div.print-flex-stars {
                    justify-content: center;
                    text-align: center;
                    align-content: center;
                    padding: 3% 0px;
                }
                .print-flex-30 {
                    flex-basis: 30%;
                    font-weight: bold;
                }
                .print-flex-65 {
                    flex-basis: 65%;
                }
                .print-flex-list div {
                    justify-content: space-around;
                }
                
                @media print {
                    .wpjb-page-job-application {page-break-after: always;}
                }

            </style>
            <div class="wpjb wpjb-page-job-application">
                
                <div class="print-flex">
                    <div class="print-flex-row print-flex-avatar">
                        <?php echo get_avatar( $application->email, 64 ) ?>
                        <?php if($application->applicant_name): ?>
                        <?php esc_html_e($application->applicant_name) ?>
                        <?php else: ?>
                        <?php _e("ID"); echo ": "; echo $application->id; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="print-flex-row print-flex-list">
                        <div class="print-flex-row-el">
                            <span class="wpjb-glyphs wpjb-icon-briefcase">
                                <?php echo esc_html( $job->job_title ) ?>
                            </span>
                        </div>
                        <div class="print-flex-row-el">
                            <span class="wpjb-glyphs wpjb-icon-clock">
                                <?php echo esc_html( sprintf( __("%s ago.", "wpjobboard" ), wpjb_time_ago( $application->applied_at ) ) ) ?>
                            </span>
                        </div>
                        <div class="print-flex-row-el">
                            <span class="wpjb-glyphs wpjb-icon-check">
                                <?php echo esc_html( $current_status["label"] ) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="print-flex-row print-flex-stars" >
                        <?php $rated = absint($application->meta->rating->value()) ?>
                        <span class="wpjb-manage-action wpjb-star-ratings" data-id="<?php echo esc_html($application->id) ?>">
                            <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin wpjb-star-rating-loader" style="vertical-align: top; display:none"></span>
                            <span class="wpjb-star-rating-bar">
                                <?php for($i=0; $i<5; $i++): ?><span class="wpjb-star-rating wpjb-motif wpjb-glyphs wpjb-icon-star-empty <?php if($rated>$i): ?>wpjb-star-checked<?php endif; ?>" data-value="<?php echo $i+1 ?>" ></span><?php endfor ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="print-flex-column">
                        <div class="print-flex-row-el">
                            <div class="print-flex-30">
                                <?php _e("Applicant E-mail", "wpjobboard") ?>
                            </div>
                            <div class="print-flex-65">
                                <?php esc_html_e($application->email) ?>
                            </div>
                        </div>
                        
                        <div class="print-flex-row-el">
                            <div class="print-flex-30">
                                <?php _e("Date Sent", "wpjobboard") ?>
                            </div>
                            <div class="print-flex-65">
                                <?php echo wpjb_date_display(get_option("date_format"), $application->applied_at) ?>
                            </div>
                        </div>
                        
                        <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
                        <div class="print-flex-row-el">
                            <div class="print-flex-30">
                                <?php esc_html_e($value->conf("title")); ?>
                            </div>
                            <div class="print-flex-65">
                                <?php if($application->doScheme($k)): ?>
                                <?php elseif($value->conf("type") == "ui-input-file"): ?>
                                    <?php foreach($application->file->{$value->name} as $file): ?>
                                    <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                                    <?php echo wpjb_format_bytes($file->size) ?><br/>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <?php esc_html_e(join(", ", (array)$value->values())) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="print-flex-row-el">
                            <div class="print-flex-30">
                                <?php _e("Attached Files", "wpjobboard") ?>
                            </div>
                            <div class="print-flex-65">
                                <?php foreach($application->getFiles() as $file): ?>
                                <a href="<?php echo esc_attr($file->url) ?>"><?php echo esc_html($file->basename) ?></a>
                                ~ <?php echo esc_html(wpjb_format_bytes($file->size)) ?>
                                <br/>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="print-flex-column">
                        <h3><?php _e("Message", "wpjobboard") ?></h3>
                        <div class="wpjb-text">
                            <?php wpjb_rich_text($application->message) ?>
                        </div>
                    </div>
                    
                    <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
                    <div class="print-flex-column">
                        <h3><?php esc_html_e($value->conf("title")); ?></h3>
                        <div class="wpjb-text">
                            <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text") ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                </div>
           </div>
        </body> 
        <?php
        $render = ob_get_clean();
        echo apply_filters( "wpjb_print_application", $render, $application );
        exit;       
    }
    
    public function multipleAction() {
        
        $ids = json_decode( base64_decode( $_GET['id'] ) );

        if( !current_user_can( 'manage_options' ) ) {
            throw new Exception( sprintf( "You do not own Application with ID %d.", $id ) );
        }

        wp_head();
        ob_start() 
        ?>
        <body>
            <script type="text/javascript">
                window.onload = function() { window.print(); }
            </script>
            <style type="text/css">
                .print-flex {
                    display: flex;
                    flex-direction: column;
                    flex-basis: 100%;
                }
                .print-flex-row {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between; 
                    align-content: space-between;
                }
                div.print-flex-avatar {
                    flex-direction: column;
                    justify-content: flex-start;
                    align-items: center;
                    text-align: center;
                    font-weight: bold;
                    font-size: 1.7em;
                    margin-bottom: 2%;
                }
                div.print-flex-avatar img {
                    border-radius: 50%;
                    margin-bottom: 2%;
                }
                .print-flex-row .print-flex-row-el {
                    display: flex;
                    flex: 1;
                    
                }
                .print-flex-column .print-flex-row-el {
                    display: flex;
                    border-bottom: 1px solid #aaa;
                    padding: 5px 0px;
                }
                .print-flex-column {
                    display: flex;
                    flex-direction: column;
                }
                div.print-flex-stars {
                    justify-content: center;
                    text-align: center;
                    align-content: center;
                    padding: 3% 0px;
                }
                .print-flex-30 {
                    flex-basis: 30%;
                    font-weight: bold;
                }
                .print-flex-65 {
                    flex-basis: 65%;
                }
                .print-flex-list div {
                    justify-content: space-around;
                }
                
                @media print {
                    .wpjb-page-job-application {page-break-after: always;}
                }

            </style>
            
            <?php foreach( $ids as $id ): ?>
                <?php $application = new Wpjb_Model_Application( $id ); ?>
                <?php if( !$application->exists() ): continue; endif; ?>
                <?php $current_status = wpjb_get_application_status($application->status); ?>
                <div class="wpjb wpjb-page-job-application">

                    <div class="print-flex">
                        <div class="print-flex-row print-flex-avatar">
                            <?php echo get_avatar( $application->email, 64 ) ?>
                            <?php if($application->applicant_name): ?>
                            <?php esc_html_e($application->applicant_name) ?>
                            <?php else: ?>
                            <?php _e("ID"); echo ": "; echo $application->id; ?>
                            <?php endif; ?>
                        </div>

                        <div class="print-flex-row print-flex-list">
                            <div class="print-flex-row-el">
                                <span class="wpjb-glyphs wpjb-icon-briefcase">
                                    <?php echo esc_html( $job->job_title ) ?>
                                </span>
                            </div>
                            <div class="print-flex-row-el">
                                <span class="wpjb-glyphs wpjb-icon-clock">
                                    <?php echo esc_html( sprintf( __("%s ago.", "wpjobboard" ), wpjb_time_ago( $application->applied_at ) ) ) ?>
                                </span>
                            </div>
                            <div class="print-flex-row-el">
                                <span class="wpjb-glyphs wpjb-icon-check">
                                    <?php echo esc_html( $current_status["label"] ) ?>
                                </span>
                            </div>
                        </div>

                        <div class="print-flex-row print-flex-stars" >
                            <?php $rated = absint($application->meta->rating->value()) ?>
                            <span class="wpjb-manage-action wpjb-star-ratings" data-id="<?php echo esc_html($application->id) ?>">
                                <span class="wpjb-glyphs wpjb-icon-spinner wpjb-animate-spin wpjb-star-rating-loader" style="vertical-align: top; display:none"></span>
                                <span class="wpjb-star-rating-bar">
                                    <?php for($i=0; $i<5; $i++): ?><span class="wpjb-star-rating wpjb-motif wpjb-glyphs wpjb-icon-star-empty <?php if($rated>$i): ?>wpjb-star-checked<?php endif; ?>" data-value="<?php echo $i+1 ?>" ></span><?php endfor ?>
                                </span>
                            </span>
                        </div>

                        <div class="print-flex-column">
                            <div class="print-flex-row-el">
                                <div class="print-flex-30">
                                    <?php _e("Applicant E-mail", "wpjobboard") ?>
                                </div>
                                <div class="print-flex-65">
                                    <?php esc_html_e($application->email) ?>
                                </div>
                            </div>

                            <div class="print-flex-row-el">
                                <div class="print-flex-30">
                                    <?php _e("Date Sent", "wpjobboard") ?>
                                </div>
                                <div class="print-flex-65">
                                    <?php echo wpjb_date_display(get_option("date_format"), $application->applied_at) ?>
                                </div>
                            </div>

                            <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type_exclude"=>"ui-input-textarea")) as $k => $value): ?>
                            <div class="print-flex-row-el">
                                <div class="print-flex-30">
                                    <?php esc_html_e($value->conf("title")); ?>
                                </div>
                                <div class="print-flex-65">
                                    <?php if($application->doScheme($k)): ?>
                                    <?php elseif($value->conf("type") == "ui-input-file"): ?>
                                        <?php foreach($application->file->{$value->name} as $file): ?>
                                        <a href="<?php esc_attr_e($file->url) ?>" rel="nofollow"><?php esc_html_e($file->basename) ?></a>
                                        <?php echo wpjb_format_bytes($file->size) ?><br/>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <?php esc_html_e(join(", ", (array)$value->values())) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="print-flex-row-el">
                                <div class="print-flex-30">
                                    <?php _e("Attached Files", "wpjobboard") ?>
                                </div>
                                <div class="print-flex-65">
                                    <?php foreach($application->getFiles() as $file): ?>
                                    <a href="<?php echo esc_attr($file->url) ?>"><?php echo esc_html($file->basename) ?></a>
                                    ~ <?php echo esc_html(wpjb_format_bytes($file->size)) ?>
                                    <br/>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <div class="print-flex-column">
                            <h3><?php _e("Message", "wpjobboard") ?></h3>
                            <div class="wpjb-text">
                                <?php wpjb_rich_text($application->message) ?>
                            </div>
                        </div>

                        <?php foreach($application->getMeta(array("visibility"=>0, "meta_type"=>3, "empty"=>false, "field_type"=>"ui-input-textarea")) as $k => $value): ?>
                        <div class="print-flex-column">
                            <h3><?php esc_html_e($value->conf("title")); ?></h3>
                            <div class="wpjb-text">
                                <?php wpjb_rich_text($value->value(), $value->conf("textarea_wysiwyg") ? "html" : "text") ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>
               </div>
            <?php endforeach; ?>
        </body> 
        <?php
        $render = ob_get_clean();
        echo $render;
        exit;      
    }

}

?>
