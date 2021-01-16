(function() {
    tinymce.create('tinymce.plugins.wpjbbutton', {

        init : function(ed, url) {
        },
        
        wpjbbutton: function(editor) {
           
            
            editor.addButton( 'wpjbbutton', {
                title: "Insert Button",
                type: 'button',
                icon: 'icon dashicons-plus-alt',
                    onclick : function() {

var x = '<table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">';
x += '<tbody><tr><td align="left"><table border="0" cellpadding="0" cellspacing="0">';
x += '<tbody><tr><td> <a href="#" target="_blank">Button</a> </td>';
x += '</tr></tbody></table></td></tr></tbody></table>';
tinyMCE.activeEditor.selection.setContent(x);
                    }
            });
        },
        


    });
    
    tinymce.PluginManager.add('wpjbbutton', tinymce.plugins.wpjbbutton);
})();
