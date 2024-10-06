
php.beforeSend = function (XMLHttpRequest){
	if(typeof SF.ajax.key === 'undefined') {
		if($('#sf_ajax_key').length>0) {
			SF.ajax.key = $('#sf_ajax_key').val();
	}
	}
	XMLHttpRequest.setRequestHeader("Sf-Ajax-Key",SF.ajax.key);
    $('.ajax-status').parent().css("display","block");
    $('#sf-status').css("display","block");
    $(document.body).addClass('sf-ajax');
    SF.onBeforeSend();
    SF.is_ajax=true;
};
 
php.complete = function (){
	$('.ajax-status').parent().css("display","none");
	$('#sf-status').css("display","none");
	$(document.body).removeClass('sf-ajax');
	$(document.body).css("padding-right","0px");
    SF.onComplete();
	SF.is_ajax=false;
};

php.error = function (xmlEr, typeEr, except) {
	$(".sf_err").remove();
    SF.onError(xmlEr);
};

php.messages.defaultCallBack = function(msg, params) {
	alert(msg);
}

SF = {
		
	is_ajax:false,
			
	a : function (el,action,actionData,oncomplete) {
		if(SF.is_ajax) return;
		SF.ajax.ajaxAction(el,action,actionData,oncomplete);
	},
	
	e : function (event,actionParams) {
		if(SF.is_ajax) return;
		SF.ajax.eventAction(event,actionParams);
	},
	
	p : function (id,actionParams) {
		SF.ajax.pollAction(id,actionParams);
	},
	
	l:function(action,params,oncomplete) {
		if(SF.is_ajax) return;
		SF.ajax.linkAction(action,params,oncomplete);
	},
	
	loadDatepicker:function(id, options) {
		options.beforeShow=function(a,b){
			setTimeout(function(){
				$(".ui-datepicker").css("z-index","10000");
			},0);
		};
		$( "#"+id ).datepicker(options);
	},

	isScrolledIntoView:function(elem) {
	    var docViewTop = $(window).scrollTop();
	    var docViewBottom = docViewTop + $(window).height();

	    var elemTop = $(elem).offset().top;
	    var elemBottom = elemTop + $(elem).height();

	    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	},
	
	scrollToElement:function(el) {
		if($(el).length==0) return;
		if(SF.isScrolledIntoView(el)) return;
		$('html, body').animate({
	        scrollTop: $(el).offset().top
	    }, 0);
	},
	
	attachTooltip: function(id, cl) {
		$("#"+id).attr("title","");
		content=$("#"+id+"-content").html();
		$("#"+id).tooltip({ content: content, tooltipClass:cl});
	},
	
	bs_attachTooltip: function(id, placement) {
		$("#"+id).tooltip({
			title:function(a){
				return $("#"+id+"-content").html();
			},
			placement:placement,
			html:true
		});
	},
	
	loadSummernote: function(id, options) {
		var editor = $("#"+id);
		options.onChange = function(contents, $editable){
			$("#"+id).html(contents);
		};
		editor.summernote(options);
	},
	
	loadCKEditor: function(id, options) {
		options.on = {
			instanceReady: function( evt ) {
				$('#'+id).closest('.sf-editor').css('height','auto');
			}
		};
		CKEDITOR.replace(id, options);

	},
	
	processEditors:function(sfData) {
		if(typeof CKEDITOR !== 'undefined') {
			try {
			sfData.sf_editor_height = {};
			for(var instid in CKEDITOR.instances) {
				var data = CKEDITOR.instances[instid].getData();
				$("#"+instid).val(data);
				var height = $("#"+instid).closest('.sf-editor').height();
				var ch = CKEDITOR.instances[instid].ui.space('contents').getStyle('height');
				ch=parseInt(ch);
				sfData.sf_editor_height[instid] = height+';'+ch;
			}
			} catch (e) {
			}
		}
	},
	
	setDeafultButton:function(form_id, button_ids) {
		let form = $("form#"+form_id);
		form.keypress(function (e) {
			if ($(e.target).is("textarea")) {
				return true;
			}
			if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {

                for(let button_id in button_ids) {
                	let scope = button_ids[button_id];
                	if($(e.target).closest(scope).length>0) {
		        		$('#'+button_id).click();
		        		return false;
					}
				}


		    } else {
		    	return true;
		    }
		});
	},

    onBeforeSend: function() {},

    onComplete: function() {},

    onError:function(xmlEr) {}
	
};



SF.ajax = {
		
	url: '',
		
	ajaxAction: function(el,action,actionData,oncomplete){
	    form=$(el).closest("form");
	    data={};
		SF.processEditors(data);
	    data.sf_source=(el==null ? null : el.id);
	    if(form) data.sf_form_data=form.serialize();
	    if(action) data.sf_action=action;
	    if(actionData) data.sf_action_data=actionData;
	    $.php(SF.ajax.url,data,oncomplete);
	},
	
	eventAction: function(event,actionParams){
		SF.processEditors();
		el=event.target;
		form=$(el).closest("form");
		data={};
		data.sf_source=el.id;
		if(form) data.sf_form_data=form.serialize();
		if(event) data.sf_event=event.type;
		oncomplete=null;
		if(actionParams) {
			if(actionParams.actionData) data.sf_action_data=actionParams.actionData;
			if(actionParams.oncomplete) oncomplete=actionParams.oncomplete;
		}
		$.php(SF.ajax.url,data,oncomplete);
	},
	
	pollAction: function(id,actionParams){
		SF.processEditors();
		el=$('#'+id);
		form=$(el).closest("form");
		data={};
		data.sf_source=id;
		if(form) data.sf_form_data=form.serialize();
		data.sf_event='poll';
		oncomplete=null;
		if(actionParams) {
			if(actionParams.actionData) data.sf_action_data=actionParams.actionData;
			if(actionParams.oncomplete) oncomplete=actionParams.oncomplete;
		}
		$.php(SF.ajax.url,data,oncomplete);
	},

	linkAction : function(action, params,oncomplete){
	    data={
	        sf_link_action:true,
	        sf_action:action};
	    if(params!=undefined) data.params=params;
	    $.php(SF.ajax.url,data,oncomplete);
	},
	
	json : function(action, params, oncomplete){
		data={
			sf_link_action:true,
			sf_action:action};
		if(params!=undefined) data.params=params;
		$.php(SF.ajax.url,data,function(data, res){
			oncomplete(JSON.parse(res));
		});
	},

	loadPickListHandler:function(id){
		left=$("#"+id+"_l");
		right=$("#"+id+"_r");
		hidden=$("#"+id);
		$("#"+id+"_bnt_mr").click(function(){
			moveOptions(left.find("option:checked"),right);
			updateHidden();
		});
		$("#"+id+"_bnt_mar").click(function(){
			moveOptions(left.find("option"),right);
			updateHidden();
		});
		$("#"+id+"_bnt_ml").click(function(){
			moveOptions(right.find("option:checked"),left);
			updateHidden();
		});
		$("#"+id+"_bnt_mal").click(function(){
			moveOptions(right.find("option"),left);
			updateHidden();
		});
		$("#"+id+"_bnt_up").click(function(){
			right.find("option:checked").each(function(){
				$(this).insertBefore($(this).prev());
			});
			updateHidden();
		});
		$("#"+id+"_bnt_dn").click(function(){
			right.find("option:checked").each(function(){
				$(this).insertAfter($(this).next());
			});
			updateHidden();
		});
		$("#"+id+"_bnt_tp").click(function(){
			var selectedOptions = right.find("option:checked");
			var first = right.children("option").not(":selected").first();
			$(selectedOptions).insertBefore(first);
			updateHidden();
		});
		$("#"+id+"_bnt_bt").click(function(){
			var selectedOptions = right.find("option:checked");
			var last = right.children("option").not(":selected").last();
			$(selectedOptions).insertAfter(last);
			updateHidden();
		});
		moveOptions=function(s,d) {
			s.each(function(){
				d.append($(this).clone());
				$(this).remove();
			});
		}
		updateHidden=function(){
			arr = new Array();
			right.find("option").each(function(){
				arr.push($(this).val());
			});
			s=arr.join();
			hidden.attr("value",s);
		}
	}
		
};

SF.upload = {
		
	submit: function(el,id) {
		let form=$(el).closest("form");
		form.attr("target","sf_iframe");
		form.attr("enctype","multipart/form-data");
		form.attr("action",SF.ajax.url+"&file_upload=true&sf_source="+el.id+"&form_id="+form.attr("id"));
		form.find("#sf_upload_form").addClass('upload-sending');
		form.find("#"+id+"_div #sf_upload_data").val(form.serialize());
		form.submit();
	},

	stop: function(form_id,sf_source,files) {
		let form=$("#"+form_id);
		form.removeAttr("target");
		form.attr("enctype","multipart/form-data");
		form.attr("action","");
		form.find("#sf_upload_form").removeClass('upload-sending');
		let data={
				sf_form_data:form.serialize(),
		        sf_source:sf_source,
		        sf_files:files
		};
		$.php(SF.ajax.url,data);		
	},
	
	abort:function(form_id,id,error) {
		let form=$("#"+form_id);
		form.removeAttr("target");
		form.removeAttr("enctype");
		form.attr("action","");
		form.find("#"+id).val("");
		form.find("#sf_upload_form").removeClass('upload-sending');
		form.find("#sf_upload_form input[type=file]").addClass('is-invalid');
		form.find("#"+id+"_div #sf_upload_error").show();
		form.find("#"+id+"_div #sf_upload_error_msg").html(error);		
	},
	
	reset:function(id) {
		$("#"+id+"_div input[type=file]").removeClass('is-invalid');
		$("#"+id+"_div #sf_upload_error").css("display","none");		
	}
		
};

function scrollToElement(el) {
	if($(el).length==0) return;
	$('html, body').animate({
        scrollTop: $(el).offset().top
    }, 0);
}

SF.inplace = {

	show:function(el) {
		lbl = $(el)
		lbl.hide();
		fld=lbl.next();
		fld.show();
	},
	
	blur:function(el,emptytext) {
		fld=$(el);
		fld.hide();
		lbl=fld.prev();
		if(fld.val().trim().length>0){
			lbl.text(fld.val());
		} else {
			lbl.text(emptytext);
		}	
		lbl.show();
	}
		
}

SF.dm = {
		
	paginate: function(el,action,val) {
		SF.a(el,null,{action:action,param:val});
	},
	
	go: function(el,val) {
		SF.a(el,null,{action:'go',param:val});
	},

	sort : function(el, colindex) {
		SF.a(el,null,{action:'sort'});
	},
	
	selectAll: function(el) {
		$(el).closest('table.sf-datatable').find('.sf-input-checkbox').each(function(){
			$(this).attr("checked","checked");
		});
	},
	
	selectNone: function(el) {
		$(el).closest('table.sf-datatable').find('.sf-input-checkbox').each(function(){
			$(this).removeAttr("checked");
		});
	},
	
	selectInvert: function(el) {
		$(el).closest('table.sf-datatable').find('.sf-input-checkbox').each(function(){
			if($(this).is(":checked")) {
				$(this).removeAttr("checked");
			} else {
				$(this).attr("checked","checked");
			}
		});
	}
		
}

SF.tabs = {
	init : function (id, options) {
		$("#"+id+"-tabs").tabs(options);
	},
	action : function (id) {
		$("#"+id+"-tabs").on( "tabsbeforeactivate", function( event, ui ) {
			index=$(this).find('ul.ui-tabs-nav li').index(ui.newTab);
			$("#"+id).val(index);
			SF.a($('#'+id).get(0),null,null);
		});
	},
	bs_action:function(id, index) {
		$("#"+id).val(index);
		SF.a($('#'+id).get(0),null,null);
	}
}

SF.reorder = {
	save:function(el) {
		let table=$(el).closest("table.ui-sortable");
		let order=table.data("order_string");
		if(order){
			SF.a(el,null,{action:'save',param:{order:order}});
		}
	},
	move:function(el,direction,row) {
		SF.a(el,null,{action:'move',param:{direction:direction,row:row}});
	},
	init:function(id) {
		let table=$("table#"+id);
		table.addClass('ui-sortable');
		let bodyEl = table.find("tbody").get(0);

		let sortable = new Sortable(bodyEl, {
			direction: 'vertical',
			handle:'.order_handle',
			onUpdate: function (/**Event*/evt) {
				let s = [];
				table.find("> tbody > tr").each(function(index){
					let id=$(this).attr("order-id");
					$(this).find("span.ordinal").html(index+1);
					s[index]=id;
				});
				let order = s.join(",");
				console.log(order);
				table.data("order_string",order);
			},
		});
		table.find("> tbody > tr").each(function(index){
			$(this).attr("order-id",index);
		});
	}
};

SF.socket = {
	connect: function() {
		var socket = io('http://localhost:2100');
		socket.on('connect', function (data) {
			console.log("connected");
		});
	}
};

SF.popup = {
	open(id) {
		let options = {};
		let modal = new bootstrap.Modal(document.getElementById(id), options);
		modal.show();
	}
};
