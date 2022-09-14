$(function () {

    //GET PROJETC BASE
    BASE = $("link[rel='base']").attr("href");

    //FORM SUBMIT
    $('.wc_ead').on("submit", "form", function (event) {
        var Form = $(this);

        Form.find('button').prop('disabled', true);

        Form.ajaxSubmit({
            url: BASE + '/_ead/wc_ead.ajax.php',
            data: {callback: Form.attr("name")},
            dataType: 'json',
            beforeSubmit: function () {
                Form.find('.jwc_load').fadeIn('fast');
            },
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.jwc_ead_upload_progress').text(porcento);

                if (completo <= '80') {
                    $('.jwc_ead_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.jwc_ead_upload').fadeOut();
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                Form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },
            success: function (data) {
                //REMOVE LOAD
                Form.find('.jwc_load').fadeOut('slow', function () {

                    //MODAL
                    if (data.modal) {
                        wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
                    }

                    //ALERT
                    if (data.alert) {
                        wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
                    }

                    //TASK FORUM
                    if (data.ead_support) {
                        if (data.ead_support_id) {
                            if (!$('#' + data.ead_support_id + ' .wc_ead_course_task_forum_response .wc_ead_course_task_forum_ticket').length) {
                                $('.jwc_allsupport #' + data.ead_support_id + ' .wc_ead_course_task_forum_response').html(data.ead_support_content);
                                $('.jwc_mysupport #' + data.ead_support_id + ' .wc_ead_course_task_forum_response').html(data.ead_support_content);
                            } else {
                                $(data.ead_support_content).insertAfter('.jwc_allsupport #' + data.ead_support_id + ' .wc_ead_course_task_forum_response .wc_ead_course_task_forum_ticket:last-child');
                                $(data.ead_support_content).insertAfter('.jwc_mysupport #' + data.ead_support_id + ' .wc_ead_course_task_forum_response .wc_ead_course_task_forum_ticket:last-child');
                            }
                        } else {
                            $('.wc_ead_course_task_forum_none').fadeOut(200);
                            $('.jwc_content').html(data.ead_support_content).fadeIn(300);
                        }
                    }

                    //FIX FOR HIGHLIGHT
                    setTimeout(function () {
                        if ($('*[class="brush: php;"]').length) {
                            $("head").append('<link rel="stylesheet" href="../../_cdn/highlight.min.css">');
                            $.getScript('../../_cdn/highlight.min.js', function () {
                                $('*[class="brush: php;"]').each(function (i, block) {
                                    hljs.highlightBlock(block);
                                });
                            });
                        }
                    }, 500);

                    //REVIEW
                    if (data.review) {
                        $('.jwc_review_target').html(data.review);
                    }

                    //RDIRECT
                    if (data.redirect) {
                        setTimeout(function () {
                            window.location.href = data.redirect;
                        }, 1000);
                    }

                    //CLOSE MODAL
                    if (data.close) {
                        $(data.close).fadeOut();
                    }

                    //FORUM CLEAR
                    if (data.clear) {
                        Form.trigger('reset');
                    }
                });
            },
            complete: function () {
                setTimeout(function () {
                    Form.find('button').prop('disabled', false);
                }, 3000);
            }
        });
        return false;
    });

    //ALL SUPPORT
    $('.wc_ead').on('click', '.wc_ead_allsupport', function () {
        if (!$(this).hasClass('btn_green')) {
            $(this).removeClass('btn_blue').addClass('btn_green');
            $('.wc_ead_mysupport').removeClass('btn_green').addClass('btn_blue');
            $('.jwc_mysupport').fadeOut(function () {
                $('.jwc_allsupport').fadeIn(400);
            });
        }
    });

    //MY SUPPORT
    $('.wc_ead').on('click', '.wc_ead_mysupport', function () {
        if (!$(this).hasClass('btn_green')) {
            $(this).removeClass('btn_blue').addClass('btn_green');
            $('.wc_ead_allsupport').removeClass('btn_green').addClass('btn_blue');
            $('.jwc_allsupport').fadeOut(function () {
                $('.jwc_mysupport').fadeIn(400);
            });
        }
    });

    //ALERT SETUP
    $('.wc_ead').on('click', '.wc_ead_alert_close', function () {
        var EadAlert = $(".wc_ead_alert_box");
        $('.wc_ead_alert').fadeOut(200, function () {
            setTimeout(function () {
                EadAlert.removeClass("blue green red yellow");
                EadAlert.find('.wc_ead_alert_title').html("{TITLE}");
                EadAlert.find('.wc_ead_alert_content').html("{CONTENT}");
            }, 210);
        });
    });

    //ALERT DISPLAY
    function wc_ead_alert(Color, Title, Content) {
        var EadAlert = $(".wc_ead_alert_box");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        EadAlert.addClass(Color);
        EadAlert.find('.wc_ead_alert_title').html(Title);
        EadAlert.find('.wc_ead_alert_content').html(Content);
        $('.wc_ead_alert').fadeIn(200).css('display', 'flex');
    }

    //MODAL SETUP
    $('.wc_ead').on('click', '.wc_ead_modal_close', function () {
        var Modal = $(".wc_ead_modal_box");
        $('.wc_ead_modal').fadeOut(200, function () {
            setTimeout(function () {
                Modal.find('.wc_ead_modal_title').removeClass("blue green red yellow");
                Modal.find('.wc_ead_modal_title span').removeClass().html("{TITLE}");
                Modal.find('.wc_ead_modal_content').html("{CONTENT}");
            }, 210);
        });
    });

    //MODAL DISPLAY
    function wc_ead_modal(Color, Icon, Title, Content) {
        var Modal = $(".wc_ead_modal_box");

        //REMOVE LOAD
        $(".jwc_load").fadeOut(200);

        Modal.find('.wc_ead_modal_title').addClass(Color);
        Modal.find('.wc_ead_modal_title span').addClass("icon-" + Icon).html(Title);
        Modal.find('.wc_ead_modal_content').html(Content);
        $('.wc_ead_modal').fadeIn(200).css('display', 'flex');
    }

    //CERTIFICATION GET
    $('.jwc_ead_certification').click(function () {
        $('.jwc_ead_load').fadeIn().css('display', 'flex');
        $.post(BASE + "/_ead/wc_ead.ajax.php", {callback: 'wc_ead_studend_certification', enrollment_id: $(this).attr('id')}, function (data) {
            $('.jwc_ead_load').fadeOut(function () {
                if (data.alert) {
                    wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
                }

                if (data.reload) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }

                if (data.modal) {
                    wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
                }

                if (data.certification) {
                    wcEadWin(data.certification);
                }
            });
        }, 'json');
    });

    //WC EAD DEFAULT WIN
    function wcEadWin(WinSetupObject) {
        var WC_WIN = $('.jwc_ead_win');
        var WC_WIN_CONTENT = WC_WIN.html();

        WC_WIN.html(
                WC_WIN_CONTENT.replace("{{IMAGE}}", WinSetupObject.Image)
                .replace("{{ICON}}", WinSetupObject.Icon)
                .replace("{{TITLE}}", WinSetupObject.Title)
                .replace("{{CONTENT}}", WinSetupObject.Content)
                .replace("{{LINK}}", WinSetupObject.Link)
                .replace("{{LINK_ICON}}", WinSetupObject.LinkIcon)
                .replace("{{LINK_TITLE}}", WinSetupObject.LinkTitle)
                .replace("{{LINK_NAME}}", WinSetupObject.LinkTitle)
                );
        WC_WIN.fadeIn().css('display', 'flex');
    }

    //IMAGE LOAD
    $('.wc_loadimage').change(function () {
        var input = $(this);
        var target = $('.' + input.attr('id'));
        var fileDefault = target.attr('default');

        if (!input.val()) {
            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            return false;
        }

        if (this.files && (this.files[0].type.match("image/jpeg") || this.files[0].type.match("image/png"))) {
            var reader = new FileReader();
            reader.onload = function (e) {
                target.fadeOut('fast', function () {
                    $(this).attr('src', e.target.result).fadeIn('fast');
                });
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            wc_ead_alert("yellow", "Imagem Inválida:", "Envie uma imagem JPG ou PNG com 500x500px!");

            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            input.val('');
            return false;
        }
    });

    //TASK MANAGER
    if ($('.jwc_ead_task').length) {
        var TaskTarget = $('.jwc_ead_task');
        var TaskRepeat = setInterval(function () {
            $.post(BASE + '/_ead/wc_ead.ajax.php', {callback: 'wc_ead_student_task_manager'}, function (data) {
                if (data.aprove) {
                    TaskTarget.fadeTo(400, 0.5, function () {
                        TaskTarget.html(data.aprove).fadeTo(400, 1);
                    });
                }

                if (data.check) {
                    TaskTarget.fadeTo(400, 0.5, function () {
                        TaskTarget.html(data.check).fadeTo(400, 1);
                    });
                }

                if (data.stop) {
                    clearTimeout(TaskRepeat);
                }
            }, 'json');
        }, 10000);
    }

    //TASK MANAGER :: MANUAL CHECK
    $('.wc_ead').on('click', '.jwc_ead_task_check', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var TaskTarget = $('.jwc_ead_task');
        $.post(BASE + '/_ead/wc_ead.ajax.php', {callback: 'wc_ead_student_task_manager_check'}, function (data) {
            if (data.check) {
                TaskTarget.fadeTo(400, 0.5, function () {
                    TaskTarget.html(data.check).fadeTo(400, 1);
                });
            }

            if (data.modal) {
                wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
            }

            if (data.alert) {
                wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
            }
        }, 'json');
    });

    //TASK MANAGER :: MANUAL UNCHECK
    $('.wc_ead').on('click', '.jwc_ead_task_uncheck', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var TaskTarget = $('.jwc_ead_task');
        $.post(BASE + '/_ead/wc_ead.ajax.php', {callback: 'wc_ead_student_task_manager_uncheck'}, function (data) {
            if (data.check) {
                TaskTarget.fadeTo(400, 0.5, function () {
                    TaskTarget.html(data.check).fadeTo(400, 1);
                });
            }

            if (data.modal) {
                wc_ead_modal(data.modal[0], data.modal[1], data.modal[2], data.modal[3]);
            }

            if (data.alert) {
                wc_ead_alert(data.alert[0], data.alert[1], data.alert[2]);
            }
        }, 'json');
    });

    //TASK MANAGER :: CLOSE MODAL REPLY
    $('.wc_ead').on('click', '.j_wc_ticket_close', function () {
        $('.wc_ead_course_task_modal').fadeOut(200);
    });

    //TASK MANAGER :: OPEN MODAL REPLY
    $('.wc_ead').on('click', '.jwc_ticket_review', function () {
        $('.jwc_ticket_review_content').find("input[name='support_id']").val($(this).attr("id"));
        $('.jwc_ticket_review_content').fadeIn(200).css('display', 'flex');
    });

    //TASK MANAGER :: OPEN MODAL REVIEW
    $('.wc_ead').on('click', '.jwc_ticket_reply', function () {
        $('.jwc_ticket_reply_content').find("input[name='support_id']").val($(this).attr("id"));
        $('.jwc_ticket_reply_content').fadeIn(200).css('display', 'flex');
    });

    //STUDENT FIX LOGIN ON PLAY
    if ($('.jwc_ead_restrict').length) {
        setInterval(function () {
            $.post(BASE + '/_ead/wc_ead.ajax.php', {callback: 'wc_ead_login_fix'}, function (data) {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }, 'json');
        }, 60000);
    }

    //WC EAD BONUS CLOSE
    $('.wc_ead').on('click', '.jwc_ead_close_bonus', function () {
        $('.wc_ead_win').fadeOut(200, function () {
            window.location.reload();
        });
    });

    //WC EAD TAB AUTOCLICK
    if (window.location.hash) {
        $("a[href='" + window.location.hash + "']").click();
        if (window.location.hash == '#orders') {
            $('html, body').animate({scrollTop: 0}, 300);
        }
    }

    //NEW LINE ACTION
    $('textarea').keypress(function (event) {
        if (event.which === 13) {
            var s = $(this).val();
            $(this).val(s + "\n");
        }
    });

    //WC EAD TEXT EDITOR
    if ($('.jwc_ead_editor').length) {
        tinyMCE.init({
            selector: "textarea.jwc_ead_editor",
            language: 'pt_BR',
            menubar: false,
            theme: "modern",
            height: 200,
            verify_html: true,
            skin: 'light',
            entity_encoding: "raw",
            theme_advanced_resizing: true,
            plugins: [
                "paste autolink link"
            ],
            toolbar: "styleselect | removeformat |  bold | italic | link | unlink",
            content_css: BASE + "/admin/_css/tinyMCE.css",
            style_formats: [
                {title: 'Normal', block: 'p'},
                {title: 'Código', block: 'pre', classes: 'brush: php;'}
            ],
            link_title: false,
            target_list: false,
            media_dimensions: false,
            media_poster: false,
            media_alt_source: false,
            media_embed: false,
            extended_valid_elements: "a[href|target=_blank|rel|class]",
            image_dimensions: false,
            relative_urls: false,
            remove_script_host: false,
            resize: false,
            paste_as_text: true
        });
    }
});