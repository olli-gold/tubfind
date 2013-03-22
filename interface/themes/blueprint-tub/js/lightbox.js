// keep a handle to the current opened dialog so we can access it later
var __dialogHandle = {dialog: null, processFollowup:false, followupModule: null, followupAction: null, recordId: null};

function getLightbox(module, action, id, lookfor, message, followupModule, followupAction, followupId, postParams) {
    // Optional parameters
    if (followupModule === undefined) {followupModule = '';}
    if (followupAction === undefined) {followupAction = '';}
    if (followupId     === undefined) {followupId     = '';}

    var params = {
        method: 'getLightbox',
        lightbox: 'true',
        submodule: module,
        subaction: action,
        id: id,
        lookfor: lookfor,
        message: message,
        followupModule: followupModule,
        followupAction: followupAction,
        followupId: followupId
    };

    // create a new modal dialog
    $dialog = $('<div id="modalDialog"><div class="dialogLoading">&nbsp;</div></div>')
        .load(path + '/AJAX/JSON?' + $.param(params), postParams)
            .dialog({
                modal: true,
                autoOpen: false,
                closeOnEscape: true,
                title: message,
                width: 600,
                height: 350,
                close: function () {
                    // check if the dialog was successful, if so, load the followup action
                    if (__dialogHandle.processFollowup && __dialogHandle.followupModule
                            && __dialogHandle.followupAction && __dialogHandle.recordId) {
                        getLightbox(__dialogHandle.followupModule, __dialogHandle.followupAction,
                                __dialogHandle.recordId, null, message);
                    }
                }
            });

    // save information about this dialog so we can get it later for followup processing
    __dialogHandle.dialog = $dialog;
    __dialogHandle.processFollowup = false;
    __dialogHandle.followupModule = followupModule;
    __dialogHandle.followupAction = followupAction;
    __dialogHandle.recordId = id;

    // done
    return $dialog.dialog('open');
}

function hideLightbox() {
    if (!__dialogHandle.dialog) {
        return false;
    }
    __dialogHandle.dialog.dialog('close');
}

function displayLightboxFeedback($form, message, type) {
    $container = $form.parent();
    $container.empty();
    $container.append('<div class="' + type + '">' + message + '</div>');
}

function displayFormError($form, error) {
    $form.parent().find('.error').remove();
    $form.prepend('<div class="error">' + error + '</div>');
}

function displayFormInfo($form, msg) {
    $form.parent().parent().find('.info').remove();
    $form.parent().prepend('<div class="info">' + msg + '</div>');
}

function showLoadingGraphic($form) {
    $form.parent().prepend('<div class="dialogLoading">&nbsp;</div>');
}

function hideLoadingGraphic($form) {
    $form.parent().parent().find('.dialogLoading').remove();
}

/**
 * This is called by the lightbox when it
 * finished loading the dialog content from the server
 * to register the form in the dialog for ajax submission.
 */
function lightboxDocumentReady() {
    registerAjaxLogin();
    registerAjaxSaveRecord();
    registerAjaxListEdit();
    registerAjaxEmailRecord();
    registerAjaxSMSRecord();
    registerAjaxTagRecord();
    registerAjaxEmailSearch();
    registerAjaxBulkEmail();
    registerAjaxBulkExport();
    registerAjaxBulkDelete();
    $('.mainFocus').focus();
}

function registerAjaxLogin() {
    $('#modalDialog > form[name="loginForm"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var form = this;
        $.ajax({
            url: path + '/AJAX/JSON?method=getSalt',
            dataType: 'json',
            success: function(response) {
                if (response.status == 'OK') {
                    var salt = response.data;

                    // get the user entered username/password
                    var password = form.password.value;
                    var username = form.username.value;

                    // encrypt the password with the salt
                    password = rc4Encrypt(salt, password);

                    // hex encode the encrypted password
                    password = hexEncode(password);

                    // login via ajax
                    $.ajax({
                        url: path + '/AJAX/JSON?method=login',
                        dataType: 'json',
                        data: {username:username, password:password},
                        success: function(response) {
                            if (response.status == 'OK') {
                                // Hide "log in" options and show "log out" options:
                                $('#loginOptions').hide();
                                $('#logoutOptions').show();

                                // Update user save statuses if the current context calls for it:
                                if (typeof(checkSaveStatuses) == 'function') {
                                    checkSaveStatuses();
                                }

                                // refresh the comment list so the "Delete" links will show
                                $('.commentList').each(function(){
                                    recordId = $(this).attr('id').substr('commentList'.length);
                                    refreshCommentList(recordId);
                                });

                                // if there is a followup action, then it should be processed
                                __dialogHandle.processFollowup = true;

                                // and we close the dialog
                                hideLightbox();
                            } else {
                                displayFormError($(form), response.data);
                            }
                        }
                    });
                } else {
                    displayFormError($(form), response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxSaveRecord() {
    $('#modalDialog > form[name="saveRecord"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var recordId = this.id.value;
        var url = path + '/AJAX/JSON?' + $.param({method:'saveRecord',id:recordId});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    // close the dialog
                    hideLightbox();
                    // Update user save statuses if the current context calls for it:
                    if (typeof(checkSaveStatuses) == 'function') {
                        checkSaveStatuses();
                    }
                    // Update tag list if appropriate:
                    if (typeof(refreshTagList) == 'function') {
                        refreshTagList(recordId);
                    }
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });

    $('a.listEdit').unbind('click').click(function(){
        var id = this.id.substr('listEdit'.length);
        hideLightbox();
        getLightbox('MyResearch', 'ListEdit', id, null, this.title, 'Record', 'Save', id);
        return false;
    });
}

function registerAjaxListEdit() {
    $('#modalDialog > form[name="listEdit"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var url = path + '/AJAX/JSON?' + $.param({method:'addList'});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    // if there is a followup action, then it should be processed
                    __dialogHandle.processFollowup = true;

                    // close the dialog
                    hideLightbox();
                } else if (response.status == 'NEED_AUTH') {
                    // TODO: redirect to login prompt?
                    // For now, we'll just display an error message; short of
                    // strange user behavior involving multiple open windows,
                    // it is very unlikely to get logged out at this stage.
                    displayFormError($form, response.data);
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxEmailRecord() {
    $('#modalDialog > form[name="emailRecord"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        showLoadingGraphic($(this));
        $(this).hide();
        var url = path + '/AJAX/JSON?' + $.param({method:'emailRecord',id:this.id.value});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                hideLoadingGraphic($form);
                if (response.status == 'OK') {
                    displayFormInfo($form, response.data);
                    // close the dialog
                    setTimeout(function() { hideLightbox(); }, 2000);
                } else {
                    $form.show();
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxSMSRecord() {
    $('#modalDialog > form[name="smsRecord"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        showLoadingGraphic($(this));
        $(this).hide();
        var url = path + '/AJAX/JSON?' + $.param({method:'smsRecord',id:this.id.value});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            clearForm: true,
            success: function(response, statusText, xhr, $form) {
                hideLoadingGraphic($form);
                if (response.status == 'OK') {
                    displayFormInfo($form, response.data);
                    // close the dialog
                    setTimeout(function() { hideLightbox(); }, 2000);
                } else {
                    $form.show();
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxTagRecord() {
    $('#modalDialog > form[name="tagRecord"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var id = this.id.value;
        var url = path + '/AJAX/JSON?' + $.param({method:'tagRecord',id:id});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    hideLightbox();
                    refreshTagList(id);
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function refreshTagList(id) {
    $('#tagList').empty();
    var url = path + '/AJAX/JSON?' + $.param({method:'getRecordTags',id:id});
    $.ajax({
        dataType: 'json',
        url: url,
        success: function(response) {
            if (response.status == 'OK') {
                $.each(response.data, function(i, tag) {
                    var href = path + '/Search/Results?' + $.param({tag:tag.tag});
                    var html = (i>0 ? ', ' : ' ') + '<a href="' + href + '">' + tag.tag +'</a> (' + tag.cnt + ')';
                    $('#tagList').append(html);
                });
            } else if (response.data && response.data.length > 0) {
                $('#tagList').append(response.data);
            }
        }
    });
}

function registerAjaxEmailSearch() {
    $('#modalDialog > form[name="emailSearch"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        showLoadingGraphic($(this));
        $(this).hide();
        var url = path + '/AJAX/JSON?' + $.param({method:'emailSearch'});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            data: {url:window.location.href},
            success: function(response, statusText, xhr, $form) {
                hideLoadingGraphic($form);
                if (response.status == 'OK') {
                    displayFormInfo($form, response.data);
                    // close the dialog
                    setTimeout(function() { hideLightbox(); }, 2000);
                } else {
                    $form.show();
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxBulkEmail() {
    $('#modalDialog > form[name="bulkEmail"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var url = path + '/AJAX/JSON?' + $.param({method:'emailSearch'});
        var ids = [];
        $(':input[name="ids[]"]', this).each(function() {
            ids.push(this.value);
        });
        var searchURL = path + '/Search/Results?lookfor=' + ids.join('+') + '&type=ids';
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            data: {url:searchURL},
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    displayLightboxFeedback($form, response.data, 'info');
                    setTimeout("hideLightbox();", 3000);
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}

function registerAjaxBulkExport() {
    $('#modalDialog > form[name="bulkExport"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var url = path + '/AJAX/JSON?' + $.param({method:'exportFavorites'});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    $form.parent().empty().append(response.data.result_additional);
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });    
}

function registerAjaxBulkDelete() {
    $('#modalDialog > form[name="bulkDelete"]').unbind('submit').submit(function(){
        if (!$(this).valid()) { return false; }
        var url = path + '/AJAX/JSON?' + $.param({method:'deleteFavorites'});
        $(this).ajaxSubmit({
            url: url,
            dataType: 'json',
            success: function(response, statusText, xhr, $form) {
                if (response.status == 'OK') {
                    displayLightboxFeedback($form, response.data.result, 'info');
                    setTimeout("hideLightbox(); window.location.reload();", 3000);
                } else {
                    displayFormError($form, response.data);
                }
            }
        });
        return false;
    });
}