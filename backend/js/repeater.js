(function($) {
"use strict";
  jQuery(document).ready(function($){ 
    if (_wpcf7 == null) { 
      var _wpcf7 = wpcf7};
      var cf7_compose_repeater = _wpcf7.taggen.compose;
      _wpcf7.taggen.compose = function(tagType, $form)
      {
          var ref = cf7_compose_repeater.apply(this, arguments);
          if (tagType== 'repeater') {
            
            ref += " [/repeater]";
          } 
          return ref;
    };
    $("body").on("focusout",".name-add-option",function(e){
      var vl = $(this).val();
      if(!vl.search("'") == 0 ){
        $(this).val("'"+vl+"'");
      }
    })
  })
})(jQuery);