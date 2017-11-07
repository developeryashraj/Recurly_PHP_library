$(document).ready(function () {
    
    $(".cancel_subscription").click(function(){
        $("#general_loading").show();
        setTimeout(function () {
            $.ajax({
            type: "POST",
            url: site_url + 'recurly/cancelSubscription',
            //data: $(form).serializeArray(),
            async: false,
            success: function (statusdata) {
                $("#general_loading").hide();

                var alert_div = $("div.alertRecurly");
                var message_class = "danger";
                alert_div.hide();

                var responseObj = jQuery.parseJSON(statusdata);
                if (responseObj.status == 9) {
                    handle_redirect();
                    return false;
                }
                if (responseObj.status == 'success') {
                    //handle_redirect(site_url + 'messages');
                    $(".message-close").click();
                    message_class = "success";
                } else {
                    var i = 0;
                    $.each(responseObj, function (field, error) {
                        if (field != "null") {

                            $("#" + field + "_validation").closest('div').addClass("has-error");
                            $("#" + field + "_validation").text(error);
                            $("#" + field + "_validation").show();

                            if (i == 0)
                                $("#" + field).focus();
                            i = i + 1;
                        }
                    });
                }
                alert_div.removeClass("alert-danger");
                alert_div.removeClass("alert-success");
                alert_div.addClass("alert-" + message_class);

                if (responseObj.msg != '') {
                    $("#message").text(responseObj.msg);
                    alert_div.show();
                    $('html, body').animate({
                        scrollTop: alert_div.offset().top - 200
                    }, 800);
                    hide_alert_msg(alert_div, 5000);
                }

                //console.log(responseObj.msg);

            }, //success
            error: function (jqXHR, textStatus, errorThrown) {
                /*if (jqXHR.status == '403') {
                    //alert(jqXHR.status+' - '+jqXHR.statusText);
                    handle_redirect(site_url + 'users/signup');
                }
                else {
                    $.fancybox({
                        'content': jqXHR.responseText
                    });
                }*/
            }//error
        });//$.ajax
        },10);
        return false;
    });
    
    
    $("#update_subscription").validate({
        onkeyup: false,
        errorClass: 'has-error-text',
        validClass: 'valid',
        errorElement: 'span',
        /*rules: {
            "data[UpdateSubscription][id]": {
                required: true,
            }
        },
        messages: {
            "data[UpdateSubscription][id]": {
                required: "Please select any of the plan.",
            }
        },*/
        highlight: function (element) {
            $(element).closest('div').addClass("has-error");
        },
        unhighlight: function (element) {
            $(element).closest('div').removeClass("has-error");
        },
        errorPlacement: function (error, element) {
            $(element).closest('div').append(error);
        },
        submitHandler: function (form) {
            $(".error").hide();
            $(".error").closest('div').removeClass("has-error");
            $("#general_loading").show();
            setTimeout(function () {
                $.ajax({
                type: "POST",
                url: site_url + 'recurly/updateSubscription',
                data: $(form).serializeArray(),
                async: false,
                success: function (statusdata) {
                    $("#general_loading").hide();

                    var alert_div = $("div.alertRecurly");
                    var message_class = "danger";
                    alert_div.hide();

                    var responseObj = jQuery.parseJSON(statusdata);
                    if (responseObj.status == 9) {
                        handle_redirect();
                        return false;
                    }
                    if (responseObj.status == 'success') {
                        //handle_redirect(site_url + 'messages');
                        $(".message-close").click();
                        message_class = "success";
                    } else {
                        var i = 0;
                        $.each(responseObj, function (field, error) {
                            if (field != "null") {

                                $("#" + field + "_validation").closest('div').addClass("has-error");
                                $("#" + field + "_validation").text(error);
                                $("#" + field + "_validation").show();

                                if (i == 0)
                                    $("#" + field).focus();
                                i = i + 1;
                            }
                        });
                    }
                    alert_div.removeClass("alert-danger");
                    alert_div.removeClass("alert-success");
                    alert_div.addClass("alert-" + message_class);

                    if (responseObj.msg != '') {
                        $("#message").text(responseObj.msg);
                        alert_div.show();
                        $('html, body').animate({
                            scrollTop: alert_div.offset().top - 200
                        }, 800);
                        hide_alert_msg(alert_div, 5000);
                    }

                }, //success
                error: function (jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status == '403') {
                        //alert(jqXHR.status+' - '+jqXHR.statusText);
                        handle_redirect(site_url + 'users/add');
                    }
                    else {
                        $.fancybox({
                            'content': jqXHR.responseText
                        });
                    }
                }//error
            });//$.ajax
            },10);
        }
    });
    
    //<editor-fold defaultstate="collapsed" desc="Add user form submit">
    $("#add_user").validate({
        onkeyup: false,
        errorClass: 'has-error-text',
        validClass: 'valid',
        errorElement: 'span',
        /*rules: {
            "data[UpdateSubscription][id]": {
                required: true,
            }
        },
        messages: {
            "data[UpdateSubscription][id]": {
                required: "Please select any of the plan.",
            }
        },*/
        highlight: function (element) {
            $(element).closest('div').addClass("has-error");
        },
        unhighlight: function (element) {
            $(element).closest('div').removeClass("has-error");
        },
        errorPlacement: function (error, element) {
            $(element).closest('div').append(error);
        },
        submitHandler: function (form) {
            $(".error").hide();
            $(".error").closest('div').removeClass("has-error");
            $("#general_loading").show();
            setTimeout(function () {
                $.ajax({
                type: "POST",
                url: site_url + 'recurly/manageUsers',
                data: $(form).serializeArray(),
                async: false,
                success: function (statusdata) {
                    $("#general_loading").hide();

                    var alert_div = $("div.alertRecurly");
                    var message_class = "danger";
                    alert_div.hide();

                    var responseObj = jQuery.parseJSON(statusdata);
                    if (responseObj.status == 9) {
                        handle_redirect();
                        return false;
                    }
                    if (responseObj.status == 'success') {
                        //handle_redirect(site_url + 'messages');
                        $(".message-close").click();
                        message_class = "success";
                    } else {
                        var i = 0;
                        $.each(responseObj, function (field, error) {
                            if (field != "null") {

                                $("#" + field + "_validation").closest('div').addClass("has-error");
                                $("#" + field + "_validation").text(error);
                                $("#" + field + "_validation").show();

                                if (i == 0)
                                    $("#" + field).focus();
                                i = i + 1;
                            }
                        });
                    }
                    alert_div.removeClass("alert-danger");
                    alert_div.removeClass("alert-success");
                    alert_div.addClass("alert-" + message_class);

                    if (responseObj.msg != '') {
                        $("#message").text(responseObj.msg);
                        alert_div.show();
                        $('html, body').animate({
                            scrollTop: alert_div.offset().top - 200
                        }, 800);
                        hide_alert_msg(alert_div, 5000);
                    }

                }, //success
                error: function (jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status == '403') {
                        //alert(jqXHR.status+' - '+jqXHR.statusText);
                        handle_redirect(site_url + 'users/add');
                    }
                    else {
                        $.fancybox({
                            'content': jqXHR.responseText
                        });
                    }
                }//error
            });//$.ajax
            },10);
        }
    });
    //</editor-fold>

});