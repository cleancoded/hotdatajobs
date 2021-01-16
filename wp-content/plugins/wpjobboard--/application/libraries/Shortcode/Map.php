<?php

class Wpjb_Shortcode_Map extends Wpjb_Shortcode_Abstract 
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_map] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_map")) {
            add_shortcode("wpjb_map", array($this, "main"));
        }
    }
    
    /**
     * Displays login form
     * 
     * This function is executed when [wpjb_map] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/wpjb_map/ documentation
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'wpjb-gmaps-markerclusterer' );
        wp_enqueue_script( 'wpjb-gmaps-infobox' );

        $params = shortcode_atts(array(
            "data" => "jobs",
            "center" => "",
            "auto_locate" => 0,
            "zoom" => 12,
            "width" => "100%",
            "height" => "400px"
        ), $atts);

        if($params["auto_locate"]) {
            $init_func = "wpjb_map_init_auto_locate";
        } else {
            $init_func = "wpjb_map_init";
        }

        ob_start();

        $gapip = array();

        if(wpjb_conf("google_api_key")) {
            $gapip["key"] = wpjb_conf("google_api_key");
        }

        $gapip["v"] = "3.exp";
        $gapip["signed_in"] = "true";

        $gapi = (is_ssl() ? "https": "http") . "://maps.googleapis.com/maps/api/js?" . http_build_query( $gapip );

        ?>

        <script type="text/javascript" src="<?php echo $gapi ?>"></script>
        <script type="text/javascript">
        // jQuery.extend()

        if (typeof ajaxurl === 'undefined') {
            ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
        }    
        var wpjbDefaults = {
            zoom: <?php echo (int)$params["zoom"] ?>,
            address: '<?php esc_html_e($params["center"]) ?>',
            auto_locate: '<?php esc_html_e($params["auto_locate"]) ?>',
            objects: '<?php esc_html_e($params["data"]) ?>',

            images: {
                closeBoxURL: "<?php echo plugins_url() ?>/wpjobboard/public/images/map-close.png",
                pin: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/map-marker.png",
                loader: '<?php echo admin_url() ?>/images/wpspin_light-2x.gif'

            },

            mapOptions: {},
            markerOptions: {},
            infoBoxOptions: {},
            mcOptions: {
                styles: [
                    {
                        height: 53,
                        width: 53,
                        textSize: 20,
                        textColor: "white",
                        url: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/clusterer-1.png"

                    },
                    {
                        height: 59,
                        width: 59,
                        textSize: 20,
                        textColor: "white",
                        url: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/clusterer-2.png"
                    },
                    {
                        height: 66,
                        width: 66,
                        textSize: 22,
                        textColor: "white",
                        url: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/clusterer-3.png"

                    },
                    {
                        height: 78,
                        width: 78,
                        textSize: 22,
                        textColor: "white",
                        url: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/clusterer-4.png"
                    },
                    {
                        height: 90,
                        width: 90,
                        textSize: 24,
                        textColor: "white",
                        url: "<?php echo plugins_url() ?>/wpjobboard/public/images/map/clusterer-5.png"

                    }
                ],
                gridSize: 50
            }

        };

        var wpjbMap = null; 
        var wpjbMapFirstLoad = true;
        var wpjbMarkers = [];
        var wpjbMarkerClusterer = null;
        var wpjbCluster = {};
        var wpjbInfoWindow = null;
        var wpjbMapCallbacks = {
            loadData: {}
        };


        function wpjb_map_init() {

            jQuery(".wpjb-map-overlay").css("visibility", "visible");
            jQuery.ajax({
              url:"<?php echo (is_ssl() ? "https": "http") ?>://maps.googleapis.com/maps/api/geocode/json?address="+wpjbDefaults.address+"&sensor=false<?php echo (wpjb_conf("google_api_key")) ? sprintf("&key=%s", wpjb_conf("google_api_key")) : "" ?>",
              type: "POST",
              success: function(res) {
                  wpjb_map_initialize(res.results[0].geometry.location);
              }
            });
        }

        function wpjb_map_init_auto_locate() {
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {

                        var pos = new google.maps.LatLng(
                            position.coords.latitude,
                            position.coords.longitude
                        );

                        wpjb_map_initialize({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });

                    }, function() {
                        wpjb_map_init();
                    }
                );
            } else {
              // Browser doesn't support Geolocation
              wpjb_map_init();
            }
        }

        function wpjb_map_initialize(geoLoc) {

            //var geoLoc = res.results[0].geometry.location;
            var mapOptions = {
                zoom: wpjbDefaults.zoom,
                center: new google.maps.LatLng(geoLoc.lat, geoLoc.lng),
                panControl: false,
                loginControl: false,
                streetViewControl: false,
                mapTypeControl: true,
                mapTypeControlOptions: {
                  style: google.maps.MapTypeControlStyle.DEFAULT,
                  mapTypeIds: [
                    google.maps.MapTypeId.ROADMAP,
                    google.maps.MapTypeId.TERRAIN
                  ]
                },
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.LARGE,
                    position: google.maps.ControlPosition.LEFT_CENTER
                }
            };

            wpjbMap = new google.maps.Map(document.getElementById('wpjb-map-canvas'), mapOptions);
            wpjbMarkerClusterer = new MarkerClusterer(wpjbMap, wpjbMarkers, wpjbDefaults.mcOptions);
            wpjbInfoWindow = new google.maps.InfoWindow();
            wpjbInfoWindow = new InfoBox({
                boxClass: "wpjb-map-infobox",
                content: document.getElementById("wpjb-map-infobox"),
                disableAutoPan: false,
                maxWidth: 150,
                pixelOffset: new google.maps.Size(20, -74),
                zIndex: null,
                closeBoxMargin: "5px 0 0 0",
                closeBoxURL: wpjbDefaults.images.closeBoxURL,
                infoBoxClearance: new google.maps.Size(1, 1)
            });

            google.maps.event.addListener(wpjbMarkerClusterer, 'clusterclick', function(cluster) {
                wpjbMarkerClusterer.zoomOnClick_ = true;
                var allInOne = true;
                var lat = cluster.markers_[0].position.lat();
                var lng = cluster.markers_[0].position.lng();
                jQuery.each(cluster.markers_, function(index, item) {
                    if(lat != item.position.lat() || lng != item.position.lng()) {
                        allInOne = false;
                    }
                });

                if(allInOne) {
                    wpjbMarkerClusterer.zoomOnClick_ = false;
                    wpjb_map_load_item(cluster.markers_, 0);
                }

            });

            google.maps.event.addListener(wpjbInfoWindow,'closeclick',function(){
                wpjbCluster = {};
            });

            wpjb_map_load_data();
        }

        function wpjb_map_load_data() {
            jQuery(".wpjb-map-overlay").css("visibility", "visible");

            var data = {
                action: "wpjb_map_data",
                objects: wpjbDefaults.objects
            };

            var callbacks = jQuery.Callbacks();
            jQuery.each(wpjbMapCallbacks.loadData, function(index, cb) {
                callbacks.add( cb );
            });

            callbacks.fire( data );
            callbacks.empty();

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                dataType: "json",
                success: wpjb_map_load_data_success
            });
        }

        function wpjb_map_load_data_success(response) {

            wpjbMarkers = [];
            wpjbMarkerClusterer.clearMarkers();

            if(!wpjbMapFirstLoad && typeof response[0] != "undefined") {
                var latlng = new google.maps.LatLng( 
                    response[0].geometry.coordinates[1], 
                    response[0].geometry.coordinates[0]
                );

                wpjbMap.panTo( latlng );
            } else {
                wpjbMapFirstLoad = false;
            }

            jQuery.each(response, function(index, item) {

                var marker = new google.maps.Marker({
                    title: item.properties.title,
                    wpjbObject: item.properties.object,
                    wpjbId: item.properties.id,
                    position: new google.maps.LatLng(item.geometry.coordinates[1], item.geometry.coordinates[0]),
                    map: wpjbMap,
                    icon: wpjbDefaults.images.pin,
                    animation: google.maps.Animation.DROP

                });

                google.maps.event.addListener(marker, 'click', function() {
                    wpjb_map_load_item([marker], 0);
                });

                wpjbMarkers.push(marker);

                // @todo: create some cool events

            });

            wpjbMarkerClusterer.addMarkers(wpjbMarkers);

            jQuery(".wpjb-map-overlay").css("visibility", "hidden");
        }

        function wpjb_map_load_details_success(response) {
            wpjbInfoWindow.setContent(response);



            jQuery(".wpjb-infobox-prev, .wpjb-infobox-next").click(function(e) {
                e.preventDefault();
                var j = wpjbCluster.index;

                if(jQuery(this).hasClass("wpjb-infobox-next")) {
                    j++;
                } else {
                    j--;
                }

                if(typeof wpjbCluster.markers[j] == 'undefined') {
                    return;
                }
                wpjbCluster.index = j;
                wpjb_map_load_item(wpjbCluster.markers, j)

            });
        }

        function wpjb_map_load_item(markers, index) {
            // AJAX LOAD DATA

            wpjbInfoWindow.close();
            wpjbInfoWindow.setContent('<img src="'+wpjbDefaults.images.loader+'" alt="" />');
            wpjbInfoWindow.open(wpjbMap, markers[index]);

            var data = {
                action: "wpjb_map_details",
                object: markers[index].wpjbObject,
                id: markers[index].wpjbId,
                index: index,
                total: markers.length
            };

            if(index == 0) {
                wpjbCluster = {
                    index: 0,
                    markers: markers.slice()
                };
            }


            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                dataType: "html",
                success: wpjb_map_load_details_success
            });
        }

        google.maps.event.addDomListener(window, 'load', <?php echo $init_func ?>);



        </script>

        <div class="wpjb-map-holder">
            <div class="wpjb-map-overlay standard ">&nbsp</div>
            <div id="wpjb-map-canvas" class="wpjb-google-map" style="height:<?php esc_attr_e($params["height"]) ?>; width:<?php esc_attr_e($params["width"]) ?>; margin:0; padding:0"></div>
        </div>

        <?php

        return ob_get_clean();
    }
}
