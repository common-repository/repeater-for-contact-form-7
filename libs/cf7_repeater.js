(function($) {
"use strict";
	jQuery(document).ready(function($){
		document.addEventListener( 'wpcf7invalid', function( event ) {
			console.log(event.detail.apiResponse);
			$.each(event.detail.apiResponse.invalid_fields, function( index, value ) {
				console.log(value);
				console.log(value.idref);
				if( value.idref == "yeeaddons" ){
					$('[name="'+value.field+'"]').closest(".wpcf7-form-control-wrap").append('<span class="wpcf7-not-valid-tip" aria-hidden="true">'+value.message+'</span>');
					console.log($('[name="'+value.field+'"]'));
				}
			});
		}, false );
		get_repeater_data_name();
		document.addEventListener( 'wpcf7mailsent', function( event ) {
			setTimeout(function() {
				$(".field-repeater-data").each(function(){ 
					var field = $(this);
						var data = JSON.stringify(field.data("old"));
						field.val(data).change();
					})
			}, 100);
		}, false );
		var list_names =[];
		var push_name = false;
		function change_name_and_ids(item,field_end = null,id_rand = '' ){
			var datas = JSON.parse(field_end.find(".field-repeater-data").val());
			var datas_ids = datas.id;
			datas_ids.push(id_rand);
			datas.id = datas_ids;
			field_end.find(".field-repeater-data").val(JSON.stringify(datas));	
			field_end.find(".field-repeater-data").attr("data-old",JSON.stringify(datas));
			item = $(item);
			$("input",item).each(function(){
				var name = $(this).attr("name");
				list_names.push(name);
				var type = $(this).attr("type");
				var id = $(this).attr("id");
				var index = $(this).closest(".container-repeater-field").data("step");
				if( type == "radio") {
					$(this).attr("name",name+"__"+id_rand);
				}else if(type == 'checkbox'){ 
					name = name.replace("[]","__"+id_rand+"[]");
					$(this).attr("name",name);
				}else if(type == 'file'){ 
					if($(this).attr("multiple") == "multiple"){
						name = name.replace("[]","__"+id_rand+"[]");
						$(this).attr("name",name+"[]");
					}else{
						$(this).attr("name",name+"__"+id_rand);
					}
				}else{
					$(this).attr("name",name+"__"+id_rand);
				}
				$(this).attr("id",id+"-"+id_rand+"");
			})
			$("textarea",item).each(function(){
				var name = $(this).attr("name");
				list_names.push(name);
				var id = $(this).attr("id");
				$(this).attr("name",name+"__"+id_rand);
				$(this).attr("id",id+"-"+id_rand+"");
			})
			$("select",item).each(function(){
				var name = $(this).attr("name");
				list_names.push(name);
				var id = $(this).attr("id");
				if($(this).attr("multiple") == "multiple"){ 
					name = name.replace("[]","__"+id_rand+"[]");
				}else{
					$(this).attr("name",name+"__"+id_rand);
				}
				$(this).attr("id",id+"-"+id_rand+"");
			})
			if(!push_name){
				push_name = true;
				[...new Set(list_names)];
				if(list_names.length > 0){
					$('input[name="_yeeaddons_cf7_repeater_fields"]').val(list_names.join("|"));
				}
			}
			return item;
		}
		function add_repeater_data(button){
			var id_rand = Math.floor(Math.random() * 10000);
			var item = $('<div class="repeater-field-item"><div class="repeater-field-header"></div><div class="repeater-field-content"></div></div>');
			item.attr("data-id",id_rand);
			var html_field = get_repeater_data(button,id_rand);
			var header = get_repeater_data_header(button);
			item.find(".repeater-field-header").append(header);
			item.find(".repeater-field-content").append(html_field);
			button.find(".cf7-field-repeater-reponese").append(item);
			update_repeater_count_header();
			$( "input" ).trigger( "done_load_repeater" );
		}
		function get_repeater_data(step_field,id_rand){
			var html_step = change_name_and_ids(step_field.find(".cf7-field-repeater-data-html").html(),step_field,id_rand);
			return html_step;
		}
		function get_repeater_data_name(){
			var i = 1;
			$(".cf7-repeater").each(function(){
				var datas = $(this).find(".field-repeater-data").data("datas") + " yeeaddons_test";
				var title = explode_datas(datas,"title");
				var button_text = explode_datas(datas,"button_text");
				var data_header = $(this).find(".repeater-field-header-data").html();
				data_header =$(data_header);
				data_header.find(".repeater-field-header-title-text").html(title);
				$(".repeater-field-button-add",$(this)).html(button_text);
				$(this).find(".repeater-field-header-data").html(data_header);
				var html_step = $("<div class='container-repeater-field'></div>");
				var names = [];
				var step_field_data = $(this).find(".field-repeater-data");
				var field_end = $(this).find(".cf7-field-repeater-data-html").html();
				var button = $(this);
				$("input,select,textarea",$(field_end)).each(function(index){ 
					names.push($(this).attr("name"));
				})
				step_field_data.val(JSON.stringify({"count":1,"fields":names,"id":[]}));
				step_field_data.attr("data-old",JSON.stringify({"count":1,"fields":names,"id":[]}));
				var initial_rows = 0;
				initial_rows =  explode_datas(datas,"initial_rows");
				if( cf7_repeater.pro != "ok" ) {
					if(initial_rows > 1){
						alert("Free version: initial_rows < 2");
						initial_rows =1;
					}
				}
				if( cf7_repeater.pro == "ok" ) {
					var initial_rows_map_field = explode_datas(datas,"initial_rows_map");
					var initial_rows_map_number = $('[name="'+initial_rows_map_field+'"]').val();
					if(initial_rows_map_number > 0){
						$('[name="'+initial_rows_map_field+'"]').attr("data-repeater",$(this).find(".field-repeater-data").attr("name"));
						$('[name="'+initial_rows_map_field+'"]').attr("repeater_initial_rows","ok");
						initial_rows = initial_rows_map_number;
						$(this).find(".repeater-field-button-add").addClass("hidden");
						$(this).addClass("repeater-remove-toolbar");
					}
				}
				setTimeout(function() {
				for (var j = 0; j < initial_rows; j++) {
					add_repeater_data(button);
				}
				}, 100);
				i++;
			})
		}
		$("body").on("change","[repeater_initial_rows='ok']",function (e){
			if( cf7_repeater.pro == "ok" ) {
				var repeater_id = $(this).data("repeater");
				$("#"+repeater_id).find(".repeater-field-item").remove();
				var number = $(this).val();
				for (let i = 0; i < number; i++) {
				$("#"+repeater_id).find(".repeater-field-button-add").click();
				}
			}	
		})
		function get_repeater_data_header(start_field){
			var html_step = start_field.find(".repeater-field-header-data").html()
			return html_step;
		}
		function update_repeater_count_header(){
			$(".cf7-repeater").each(function(){
					var i = 1;
					$(".repeater-field-item",$(this)).each(function(){
						$(this).find(".repeater-field-header-count").html(i);
						i++;
					})
					var datas = JSON.parse($(this).find(".field-repeater-data").val());
					datas.count = i-1;
					$(this).find(".field-repeater-data").val(JSON.stringify(datas));
					$(this).find(".field-repeater-data").attr("data-old",JSON.stringify(datas));
			});
		}
		function check_max_row(step_field){
			var max = explode_datas(step_field.find(".field-repeater-data").data("datas"));
			var number_item = $('.repeater-field-item',step_field).length;
			if( number_item >= max ){
				return false;
			}else{
				return true;
			}
		}
		function check_min_row(step_field){
			var min =  explode_datas(step_field.find(".field-repeater-data").data("datas"),"initial_rows");
			var number_item = $('.repeater-field-item',step_field).length;
			if( number_item <= min ){
				return false;
			}else{
				return true;
			}
		}
		function explode_datas(str="", type = "max"){
			if(type == "max"){
				const regex = /max\:(.\d?) /gm;
				let m;
				let max = 10;
				while ((m = regex.exec(str)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				    m.forEach((match, groupIndex) => {
				    	max = match; 
				    });
				}
				return max;
			}else if( type == "initial_rows" ){
				const regex = /initial_rows\:(.\d?) /gm;
				let m;
				let max = 1;
				while ((m = regex.exec(str)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				    m.forEach((match, groupIndex) => {
				    	max = match; 
				    });
				}
				return max;
			}
			else if( type == "title" ){
				const regex = /title\:'(.*?)' /gm;
				let m;
				let max = "";
				while ((m = regex.exec(str)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				    m.forEach((match, groupIndex) => {
				    	max = match; 
				    });
				}
				return max;
			}else if( type == "button_text" ){
				const regex = /button\:'(.*?)' /gm;
				let m;
				let max = "Add more";
				while ((m = regex.exec(str)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				    m.forEach((match, groupIndex) => {
				    	max = match; 
				    });
				}
				return max;
			}else if( type == "initial_rows_map" ){
				const regex = /initial_rows_map\:(.*?) /gm;
				let m;
				let max = "";
				while ((m = regex.exec(str)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				    m.forEach((match, groupIndex) => {
				    	max = match; 
				    });
				}
				return max;
			}
		}
		function removeAR(arr) {
		    var what, a = arguments, L = a.length, ax;
		    while (L > 1 && arr.length) {
		        what = a[--L];
		        while ((ax= arr.indexOf(what)) !== -1) {
		            arr.splice(ax, 1);
		        }
		    }
		    return arr;
		}
	    $("body").on("click",".repeater-field-button-add",function(e){
	    	e.preventDefault();
	    	if( check_max_row($(this).closest(".cf7-repeater")) ){
	    		add_repeater_data($(this).closest(".cf7-repeater"));
	    	}else{
	    		$(this).addClass('hidden');
	    	}
	    })
	    $("body").on("click",".repeater-field-header-acctions-toogle",function(e){
	    	e.preventDefault();
	    	if( $(this).hasClass("icon-down-open")){
	    		$(this).removeClass("icon-down-open");
	    		$(this).addClass("icon-up-open");
	    	}else{
	    		$(this).addClass("icon-down-open");
	    		$(this).removeClass("icon-up-open");
	    	}
	    	$(this).closest(".repeater-field-item").find(".repeater-field-content").slideToggle("slow");
	    	$(this).closest(".repeater-field-item").find(".repeater-field-header").toggleClass('repeater-content-show');
	    })
	    $("body").on("click",".repeater-field-header-acctions-remove",function(e){
	    	e.preventDefault();
	    	if( check_min_row($(this).closest(".cf7-repeater")) ){
	    		var id = $(this).closest(".repeater-field-item").data("id");
	    		var datas = JSON.parse($(this).closest(".cf7-repeater").find(".field-repeater-data").val());
				var datas_ids = datas.id;
				datas_ids = removeAR(datas_ids,id);
				datas.id = datas_ids;
				$(this).closest(".cf7-repeater").find(".field-repeater-data").val(JSON.stringify(datas));
				$(this).closest(".cf7-repeater").find(".field-repeater-data").attr("data-old",JSON.stringify(datas));
	    		$(this).closest(".repeater-field-item").remove();
	    	}else{
	    	}
	    	update_repeater_count_header();
	    })
	})
})(jQuery);