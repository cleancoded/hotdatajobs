jQuery(function($) {
    
  if($(".daq-multiselect").length == 0) {
      return;
  }

  $("select.daq-multiselect").each(function(index, item) {
      var $this = $(item);
      var $parent = $this.parent();

      var holder = $("<div></div>");
      holder.addClass("daq-multiselect-holder");

      var emptyOption = daq_selectlist_lang.hint;
      if($this.find("option.daq-multiselect-empty-option").length > 0) {
          emptyOption = $this.find("option.daq-multiselect-empty-option").text();
      }

      var input = $('<input type="text" />');
      input.attr("id", $this.attr("name"));
      input.attr("id", $this.attr("id"));
      input.attr("placeholder", emptyOption);
      input.attr("autocomplete", "off");
      input.addClass("daq-multiselect-input");
      input.focus(function(e) {
            $(this).blur();
            
            if($(this).hasClass("daq-multiselect-open")) {
                $(this).removeClass("daq-multiselect-open");
                $(this).parent().find(".daq-multiselect-options").hide();
            } else {
                $(this).addClass("daq-multiselect-open");
                $(this).parent().find(".daq-multiselect-options").css("width", $(this).outerWidth()-1);
                $(this).parent().find(".daq-multiselect-options").show();
            }

            e.stopPropagation();
      });  

      var options = $("<div></div>");
      options.addClass("daq-multiselect-options");

      var ul = $("<ul></ul>");
      var isCute = $this.hasClass("daq-multiselect-cute");

      $this.find("option").each(function(i, o) {
          
          var o = $(o);
          var li = $("<li></li>").addClass("wpjb-input-cols wpjb-input-cols-1");
          var label = $("<label></label>").attr("for", input.attr("id")+"-"+i);
          var div = "";
          var span = $("<span></span>").addClass("wpjb-input-description").text(o.text());
          
          var checkbox = $('<input type="checkbox" />');
          checkbox.attr("id", input.attr("id")+"-"+i);
          checkbox.attr("value", o.attr("value"));
          checkbox.attr("name", $this.attr("name"));
          checkbox.data("wpjb-owner", input.attr("id"));
          checkbox.change(function() {
              var owner = $("#"+$(this).data("wpjb-owner"));
              var all = $(this).closest(".daq-multiselect-options").find("input");
              var checked = [];

              all.each(function(j, c) {
                  if($(c).is(":checked")) {
                      checked.push($(c).parent().text().trim());
                  }
              });

              owner.attr("value", checked.join(", "));
          });
          if(o.is(":selected")) {
              checkbox.attr("checked", "checked");
          }
          
          
          if(isCute) {
              label.addClass("wpjb-cute-input wpjb-cute-checkbox");
              div = $("<div></div>").addClass("wpjb-cute-input-indicator");
          }
          
          label.append(checkbox).append(div).append(span);
          li.append(label);
          ul.append(li);
      });
      
      options.append(ul);

      holder.append(input).append(options);

      $this.remove();
      $parent.prepend(holder)

      options.find("input[type=checkbox]").change();
  });

  $(document).mouseup(function(e) {
        var container = $(".daq-multiselect-options");

        if ($(e.target).hasClass("daq-multiselect-input")) {
            return;
        }

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
            container.parent().find("input").removeClass("daq-multiselect-open");
        }
  });

});