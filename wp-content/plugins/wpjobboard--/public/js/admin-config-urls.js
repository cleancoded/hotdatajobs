jQuery(function($) {
    
    $(".wpjb-rewrite-slug").each(function(index, item) {
        $(this).on("change keyup", function(e) {
            var $this = $(this);
            var $text = $this.closest("td").find("strong");
            
            if($this.val().length === 0) {
                $text.text($this.attr("placeholder"));
            } else {
                $text.text($this.val());
            }
        });
        
        $(this).change();
        
    });
});

