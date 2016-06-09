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

check_step = 0;
task_result = 0;
function check_task(task, task_id)
{
    intervalID = null;

    function check_task_start()
    {
        get_task_info(task_id);

        if (task_status != 'waiting' && task_status != '') {
            clearInterval(intervalID);
            check_step = 1;
            check_task(task, task_id);
        }
    }

    function check_task_end()
    {
        get_task_info(task_id);

        if ((task_status == 'success' || task_status == 'error') && task_status != '') {
            clearInterval(intervalID);
            check_step = 2;
            check_task(task, task_id);
        }
    }
    
    function check_task_result()
    {
        if (task != 'gsstart' && task != 'gsstop' && task != 'gsrest') {
            clearInterval(intervalID);

            task_result = 1;
            check_step = 3;
            check_task(task, task_id);
        }

        if (server_status == 0) {
            get_server_status(page.server_id);
        }
        
        if (task == 'gsstart' || task == 'gsrest') {
            if (server_status == 2) {
                clearInterval(intervalID);

                task_result = 1;
                check_step = 3;
                check_task(task, task_id);
            } else if (server_status == 3) {
                clearInterval(intervalID);

                task_result = 0;
                check_step = 3;
                check_task(task, task_id);
            }
        }

        if (task == 'gsstop') {
            if (server_status == 3) {
                clearInterval(intervalID);

                task_result = 1;
                check_step = 3;
                check_task(task, task_id);
            } else if (server_status == 2) {
                clearInterval(intervalID);

                task_result = 0;
                check_step = 3;
                check_task(task, task_id);
            }
        }
        
        // check_task(task, task_id);
    }

    if (check_step == 0) {
        $(".task-progress-header").html(message_title[task]);
        $("#vts-content").html("");
        $("#vts-content").append("<span class=\"vts-progress\">" + messages[task][0] + "...</span>");
        intervalID = setInterval(check_task_start, check_timeout);
    }

    if (!intervalID && check_step == 1) {
        $('.vts-progress').last().remove();
        $("#vts-content").append("<span class=\"vts-progress\">&#10003; " + messages[task][0] + "</span><br />\n");
        $("#vts-content").append("<span class=\"vts-progress\">" + messages[task][1] + "...</span>");
        
        intervalID = setInterval(check_task_end, check_timeout);
    }

    if (!intervalID && check_step == 2) {
        $('.vts-progress').last().remove();
        $("#vts-content").append("<span class=\"vts-progress\">&#10003; " + messages[task][1] + "</span><br />\n");
        $("#vts-content").append("<span class=\"vts-progress\">" + messages[task][2] + "...</span>\n");
        intervalID = setInterval(check_task_result, check_timeout);
    }

    if (!intervalID && check_step == 3) {
        // End
        $('.vts-progress').last().remove();
        $("#vts-content").append("<span class=\"vts-progress\">&#10003; " + messages[task][2] + "</span><br />\n");

        if (task_result) {
            $('#view-task-status').arcticmodal('close');
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
            $('#view-task-status').arcticmodal('close');
            
            noty({
                layout: 'center',
                type: 'error',
                text: error_messages[task]
            });
        }

        // Var nulled
        check_step      = 0;
        task_status     = '';
        task_result     = 0;
    }
}

function add_task(task)
{
    if (block == true) {
        return;
    }

    block = true;
    ShowLoad();
    
    $.ajax({
        url: sprintf("%sajax/tasks/add_task", page.site_url),
        type: "POST",
        data:
        {
            'task': task,
            'server_id': {server_id},
            '{csrf_token_name}': '{csrf_hash}'
        },
        dataType: "json",
        success: function(response) {
            if (response.status == '0') {
                noty({layout: 'bottomCenter', type: 'error', text: response.error_text});
                return;
            } else {
                // noty({layout: 'bottomCenter', type: 'success', text: "Success"});
                $('#view-task-status').arcticmodal();
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

function server_act(task)
{
    noty({
        layout: 'center',
        type: 'confirm',
        text: confirm_msg[task],
        buttons: [
            {addClass: 'btn btn-primary', text: '{lang_yes}', onClick: function($noty) {
                    $noty.close();
                    add_task(task);
                }
            },
            {addClass: 'btn btn-danger', text: '{lang_no}', onClick: function($noty) {
                    $noty.close();
                }
            }
        ]
    });
}
