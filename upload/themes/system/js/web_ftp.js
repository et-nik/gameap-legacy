var current_dir = "/";
var current_file = "";
var file_contents = "";

var last = true;
	
var csrf_token_name = page.csrf_token_name;

function ChangeDirAndRead(dir, file)
{
	current_dir = dir;
	GetList();
	ReadFile(file);
}

function GetLastFiles()
{
	$('#last_files').html("");
	
	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/get_last_files/%s", page.site_url, page.server_id),
		type:     "GET",
		dataType: "json",
		success: function(response) {
			
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
			
			$.each(response.files, function(key, val) {
				$('#last_files').append('<a href="#" onclick="ChangeDirAndRead(\''+response.files[key].dir+'\', \''+response.files[key].file+'\')">'+response.files[key].file+'</a>&nbsp;&bull;&nbsp;');
			});
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		},
		complete: function() {
			HideLoad();
		}
	}); 
}

function GetList() 
{ 
	ShowLoad();
	
	$('#files > tbody').each(function() {
		$(this).append($('.mydiv'));
		$(this).children().not('#head').not('#up').remove();
	});
	
	ajax_data = {'dir':current_dir};
	ajax_data[csrf_token_name] = page.csrf_hash;
	
	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/get_list/%s", page.site_url, page.server_id),
		type:     "POST",
		data: 	ajax_data,
		dataType: "json",
		success: function(response) {

			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
			
			$.each(response.files, function(key, val) {
				
				icon = response.files[key].type == 'd' 
					? sprintf('<img src="%s/images/folder_16.png" />', page.template_files)
					: sprintf('<img src="%s/images/file_16.png" />', page.template_files);
				
				$('#files').append('<tr class="list">\
				<td>' + icon + '</td>\
				<td onclick="tr_action(\'' + response.files[key].file_name + '\', \'' + response.files[key].type + '\')">' + response.files[key].file_name + '</td>\
				<td>' + response.files[key].file_size + '</td>\
				<td>\
					<a href="#" title="Rename" onclick="RenameFilePopup(\''+response.files[key].file_name+'\');return false;"><img src="'+page.template_files+'/images/rename_16.png" /></a>\
					<a href="#" title="Delete" onclick="DeleteFilePopup(\''+response.files[key].file_name+'\');return false;"><img src="'+page.template_files+'/images/delete_16.png" /></a>\
				</td>\
				</tr>');

			});
			
			$("#current_dir").val(current_dir);
			last = true;
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
			last = false;
		},
		complete: function() {
			HideLoad();
		}
	}); 
}

function ReadFile(file) {
	ShowLoad();
	
	if (current_file == file & file_contents != '') {
		$("textarea[name='file_contents']").val(file_contents);
		$('#edit_file').arcticmodal();
		$('#edit_file h3').html(page.lang_server_files_edit + ' ' + file);
		
		HideLoad();

		return;
	}
	
	ajax_data = {'dir':current_dir, 'file':file};
	ajax_data[csrf_token_name] = page.csrf_hash;

	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/read_file/%s", page.site_url, page.server_id),
		type:     "POST",
		data: ajax_data,
		dataType: "json",
		success: function(response) {
			
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
			
			current_file = file;
			file_contents = utf8_decode(base64_decode(response.file_contents));
			
			$("textarea[name='file_contents']").val(file_contents);
			$('#edit_file').arcticmodal();
			$('#edit_file h3').html(page.lang_server_files_edit + ' ' + file);
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		},
		complete: function() {
			GetLastFiles();
			HideLoad();
		}
	}); 
}

function DeleteFilePopup(file) {
	$("input[name='new_file_name']").val(file);
	$('#delete_confirm').arcticmodal();
	current_file = file;
}

function DeleteFile() {
	ajax_data = {'dir':current_dir, 'file':current_file};
	ajax_data[csrf_token_name] = page.csrf_hash;

	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/delete_file/%s", page.site_url, page.server_id),
		type:     "POST",
		data: ajax_data,
		dataType: "json",
		success: function(response) {
			
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		} 
	}); 
	
	GetList();
	$('#delete_confirm').arcticmodal('close');
}

function RenameFilePopup(file) {
	$("input[name='new_file_name']").val(file);
	$('#rename').arcticmodal();
	current_file = file;
}

function RenameFile() {
	ajax_data = {'dir':current_dir, 'file':current_file, 'new_name':$("input[name='new_file_name']").val()};
	ajax_data[csrf_token_name] = page.csrf_hash;
	
	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/rename_file/%s", page.site_url, page.server_id),
		type:     "POST",
		data: ajax_data,
		dataType: "json",
		success: function(response) {
			
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		} 
	}); 
	
	GetList();
	$('#rename').arcticmodal('close');
}

function WriteFile() {
	ShowLoad();
	
	if (current_file == '') {
		noty({layout: 'bottomCenter', type: 'error', text: "empty file"});
		HideLoad();
		return;
	}

	ajax_data = {'dir':current_dir, 'file':current_file, 'file_contents':$("textarea[name='file_contents']").val()};
	ajax_data[csrf_token_name] = page.csrf_hash;
	
	$.ajax({ 
		url:     sprintf("%sajax/web_ftp/write_file/%s", page.site_url, page.server_id),
		type:     "POST",
		data: ajax_data,
		dataType: "json",
		success: function(response) {
			
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
			
			noty({layout: 'bottomCenter', type: 'success', text: page.lang_server_files_data_writed});
			
			file_contents = $("textarea[name='file_contents']").val();
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		},
		complete: function() {
			HideLoad();
		}
	});
}

function UploadFile()
{
	$("#fupload_file" ).append('<input type="hidden" name="dir" value="'+current_dir+'" />');
			
	var formData = new FormData($("#fupload_file")[0]);
	$.ajax({
		url:     sprintf("%sajax/web_ftp/upload/%s", page.site_url, page.server_id),
		type: 'POST',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		xhr: function() {
			var myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){
				myXhr.upload.addEventListener('progress',progressHandlingFunction, false);
			}
			return myXhr;
		},
		dataType: "json",
		success: function(response) {
			if (response.status == '0') {
				noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
				return;
			}
			
			noty({layout: 'bottomCenter', type: 'success', text: "File uploaded"});
		}, 
		error: function() {
			noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
		} 
	});
}

function tr_action(name, type)
{
	if (type == 'f') {
		ReadFile(name);
	}
	else if (type == 'd') {
		current_dir = current_dir + '/' + name;
		current_dir = current_dir.replace(/\/+/, '/');
		
		GetList();
	}
} 
