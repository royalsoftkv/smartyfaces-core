(function (factory) {
  /* global define */
  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else {
    // Browser globals: jQuery
    factory(window.jQuery);
  }
}(function ($) {
  // template
  var tmpl = $.summernote.renderer.getTemplate();

  $.summernote.addPlugin({
    /** @property {String} name name of plugin */
    name: 'copypaste',
    clipboard:null,

    buttons: { // buttons
      cut: function (lang, options) {

        return tmpl.iconButton(options.iconPrefix + 'cut', {
          event : 'cut',
          title: 'cut',
        });
      },
      copy: function (lang, options) {
    	  
    	  return tmpl.iconButton(options.iconPrefix + 'copy', {
    		  event : 'copy',
    		  title: 'copy',
    	  });
      },
      paste: function (lang, options) {
    	  
    	  return tmpl.iconButton(options.iconPrefix + 'paste', {
    		  event : 'paste',
    		  title: 'paste',
    	  });
      },

    },

    events: { // events
      cut: function (event, editor, layoutInfo) {
        // Get current editable node
        var $editable = layoutInfo.editable();
        
        if($(".note-control-selection:visible").length == 0) {
        	//cut text
          var range = window.getSelection().getRangeAt(0),
          content = range.extractContents();
          span = document.createElement('SPAN');
  
          span.appendChild(content);
          var htmlContent = span.innerHTML;
          this.clipboard = htmlContent;
        } else {
        	var img = $(".note-control-selection").data('target');
        	this.clipboard = $(img).clone();
        	img.remove();
        }
      },
      copy: function (event, editor, layoutInfo) {
    	  // Get current editable node
    	  var $editable = layoutInfo.editable();
    	  
    	  if($(".note-control-selection:visible").length == 0) {
    		  //cut text
    		  var range = window.getSelection().getRangeAt(0),
    		  content = range.extractContents();
    		  span = document.createElement('SPAN');
    		  
    		  span.appendChild(content);
    		  var htmlContent = span.innerHTML;
    		  this.clipboard = htmlContent;
    		  range.insertNode(span);
    	  } else {
    		  var img = $(".note-control-selection").data('target');
    		  this.clipboard = $(img).clone();
    	  }
      },
      paste: function (event, editor, layoutInfo) {
    	  // Get current editable node
    	  var $editable = layoutInfo.editable();
    	  editor.pasteHTML($editable, $(this.clipboard).clone());
      },
    }
  });
}));
