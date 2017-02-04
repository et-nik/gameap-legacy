block = false;

task_status = '';
check_timeout = 2000;

function get_task_info(task_id)
{
    $.ajax({ 
        url:    sprintf("%sajax/tasks/get_info/%s", page.site_url, task_id),
        type:     "GET",
        dataType: "json",
        success: function(response) {
            if (response.status == '0') {
                noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
                return;
            } else {
                task_status = response.data.status;
            }
        },
        error: function() {
            noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
        },
        complete: function() {
            HideLoad();
            block = false;
        }
    });
}

function check_task(task, task_id)
{
    get_task_info(task_id);

    if (task_status == '') {
        $("#vts-content").html("...");
        set_progress(0);
    } else if (task_status == 'waiting') {
        $("#vts-content").html(messages[task][0] + "...");
        set_progress(5);
    }
    else if (task_status == 'working') {
        $("#vts-content").html(messages[task][1] + "...");
        set_progress(40);
    }
    else if (task_status == 'success') {
        $("#vts-content").html(messages[task][2] + "...");

        if (task != 'gsstart' && task != 'gsstop' && task != 'gsrest') {
            set_progress(100);
            report_task_end(task, true);
            return;
        }
        
        set_progress(80);

        if (server_status == 0) {
            get_server_status(page.server_id);
        }

        if (server_status == 2) {
            // Server online
            if (task == 'gsstart' || task == 'gsrest') {
                set_progress(100);
                report_task_end(task, true);
                return;
            } else if (task == 'gsstop') {
                set_progress(100);
                report_task_end(task, false);
                return;
            }
        } else if (server_status == 3) {
            // Server offline
            if (task == 'gsstart' || task == 'gsrest') {
                set_progress(100);
                report_task_end(task, false);
                return;
            } else if (task == 'gsstop') {
                set_progress(100);
                report_task_end(task, true);
                return;
            }
        }
    }
    else if (task_status == 'error') {
        set_progress(100);
        report_task_end(task, false);
        return;
    }

    setTimeout(check_task, check_timeout, task, task_id);
}

function report_task_end(task, success = false)
{
    if (success) {
        tmodal_close();
        noty({
            layout: 'center',
            type: 'success',
            text: success_messages[task],
            callback: {
                onClose: function() {
                    location.reload();
                }
            }
        });
    }
    else {
        tmodal_close();
        
        noty({
            layout: 'center',
            type: 'error',
            text: error_messages[task]
        });
    }
}

function add_task(task)
{
    if (block == true) {
        return;
    }

    block = true;
    ShowLoad();

    ajax_data = {'task': task, 'server_id': page.server_id};
	ajax_data[page.csrf_token_name] = page.csrf_hash;
    
    $.ajax({
        url: sprintf("%sajax/tasks/add_task", page.site_url),
        type: "POST",
        data: ajax_data,
        dataType: "json",
        success: function(response) {
            if (response.status == '0') {
                noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
                return;
            } else {
                tmodal_open();
                $('.task-progress-header').html(message_titles[task]);
                check_task(task, response.task_id);
            }
        },
        error: function() {
            noty({layout: 'bottomCenter', type: 'error', text: "unknown error"});
        },
        complete: function() {
            HideLoad();
            block = false;
        }
    });
}
