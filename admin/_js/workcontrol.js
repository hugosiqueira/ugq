/* 
 * Biblioteca de eventos jQuery do Painel Work Control Pro Content Manager
 * Created on : 06/01/2016, 11:15:16
 * Author     : UpInside Treinamentos
 */

$(function () {
    //MOBILE MENU CONTROL
    $('.mobile_menu').click(function () {
        if ($('.dashboard_nav, .dashboard_nav_normalize').css('left') !== '-220px') {
            $('.dashboard_nav, .dashboard_nav_normalize').animate({left: '-220px'}, 300);
            $('.dashboard_fix').animate({'margin-left': '0px'}, 300);
        } else {
            $('.dashboard_nav, .dashboard_nav_normalize').animate({left: '0px'}, 300);
            $('.dashboard_fix').animate({'margin-left': '220px'}, 300);
        }
    });

    //WC LOGIN FIX
    setInterval(function () {
        $.post('_ajax/Dashboard.ajax.php', {callback: 'Dashboard', callback_action: 'wc_login_fix'}, function (data) {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        }, 'json');
    }, 5 * 60 * 1000);

    if ($(window).outerWidth() < '480') {
        $.getScript('../_cdn/jquery.mobile.js', function () {
            $(window).on("swipeleft", function () {
                //$('.mobile_menu').click();
                if ($('.dashboard_nav, .dashboard_nav_normalize').css('left') !== '-220px') {
                    $('.dashboard_nav, .dashboard_nav_normalize').animate({left: '-220px'}, 300);
                    $('.dashboard_fix').animate({'margin-left': '0px'}, 300);
                }
            });

            $(window).on("swiperight", function () {
                //$('.mobile_menu').click();
                if ($('.dashboard_nav, .dashboard_nav_normalize').css('left') === '-220px') {
                    $('.dashboard_nav, .dashboard_nav_normalize').animate({left: '0px'}, 300);
                    $('.dashboard_fix').animate({'margin-left': '220px'}, 300);
                }
            });
        });
    }

    //WC LOAD MODAL
    $('.jwc_load_modal').click(function () {
        $('.workcontrol_upload').fadeIn().css('display', 'flex');
    });

    //WC TAB
    $('.wc_tab').click(function () {
        if (!$(this).hasClass('wc_active')) {
            var WcTab = $(this).attr('href');

            $('.wc_tab').removeClass('wc_active');
            $(this).addClass('wc_active');

            $('.wc_tab_target.wc_active').fadeOut(200, function () {
                $(WcTab).fadeIn(300).addClass('wc_active');
            }).removeClass('wc_active');
        }

        if (!$(this).hasClass('wc_active_go')) {
            return false;
        }
    });

    //WC TAB AUTOCLICK
    if (window.location.hash) {
        $("a[href='" + window.location.hash + "']").click();

        setTimeout(function () {
            $(".jwc_open_" + wcUrlParam('open')).click();
        }, 100);
    }

    //IMAGE ERROR
    $('img').error(function () {
        var s, w, h;
        s = $(this).attr('src');
        w = 800;
        h = 400;
        $(this).attr('src', '../tim.php?src=admin/_img/no_image.jpg&w=' + w + "&h=" + h);
    });

    //NEW LINE ACTION
    $('textarea').keypress(function (event) {
        if (event.which === 13) {
            var s = $(this).val();
            $(this).val(s + "\n");
        }
    });

    //############## GET CEP
    $('.wc_getCep').change(function () {
        var cep = $(this).val().replace('-', '').replace('.', '');
        if (cep.length === 8) {
            $.get("https://viacep.com.br/ws/" + cep + "/json", function (data) {
                if (!data.erro) {
                    $('.wc_bairro').val(data.bairro);
                    $('.wc_complemento').val(data.complemento);
                    $('.wc_localidade').val(data.localidade);
                    $('.wc_logradouro').val(data.logradouro);
                    $('.wc_uf').val(data.uf);
                }
            }, 'json');
        }
    });

    //AUTOSAVE ACTION
    $('html').on('change', 'form.auto_save', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var form = $(this);
        var callback = form.find('input[name="callback"]').val();
        var callback_action = form.find('input[name="callback_action"]').val();

        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        form.ajaxSubmit({
            url: '_ajax/' + callback + '.ajax.php',
            data: {callback_action: callback_action},
            dataType: 'json',
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.workcontrol_upload_progrees').text(porcento);

                if (completo <= '80') {
                    $('.workcontrol_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.workcontrol_upload').fadeOut('slow', function () {
                        $('.workcontrol_upload_progrees').text('0%');
                    });
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },
            success: function (data) {
                if (data.name) {
                    var input = form.find('.wc_name');
                    if (!input.val() || input.val() != data.name) {
                        input.val(data.name);
                    }

                    var inputfield = form.find('input[name*=_name]');
                    if (inputfield) {
                        inputfield.val(data.name);
                    }
                }

                if (data.gallery) {
                    form.find('.gallery').fadeTo('300', '0.5', function () {
                        $(this).html($(this).html() + data.gallery).fadeTo('300', '1');
                    });
                }

                if (data.view) {
                    $('.wc_view').attr('href', data.view);
                }

                if (data.reorder) {
                    $('.wc_drag_active').removeClass('btn_yellow');
                    $('.wc_draganddrop').removeAttr('draggable');
                }

                //CLEAR INPUT FILE
                if (!data.error) {
                    form.find('input[type="file"]').val('');
                }
            }
        });
    });

    $('html').on('click', '.j_wc_action', function () {
        
        $(this).stop();
        
        var form = $(this);
        var callback = $(this).attr('data-callback');
        var callback_action = $(this).attr('data-callback-action');
        var key = $(this).attr('data-value');

        $.post('_ajax/' + callback + '.ajax.php', {callback: callback, callback_action: callback_action, key: key}, function (data) {
            //EXIBE CALLBACKS
            if (data.trigger) {
                Trigger(data.trigger);
            }

            //REDIRECIONA
            if (data.redirect) {
                $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                $('.workcontrol_upload').fadeIn().css('display', 'flex');
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                    if (window.location.hash) {
                        window.location.reload();
                    }
                }, 1500);
            }

            //INTERAGE COM TINYMCE
            if (data.tinyMCE) {
                tinyMCE.activeEditor.insertContent(data.tinyMCE);
                $('.workcontrol_imageupload').fadeOut('slow', function () {
                    $('.workcontrol_imageupload .image_default').attr('src', '../tim.php?src=admin/_img/no_image.jpg&w=500&h=300');
                });
            }

            //GALLETY UPDATE HTML
            if (data.gallery) {
                form.find('.gallery').fadeTo('300', '0.5', function () {
                    $(this).html($(this).html() + data.gallery).fadeTo('300', '1');
                });
            }

            //DATA CONTENT IN j_content
            if (data.content) {
                if (typeof (data.content) === 'string') {
                    $('.j_content').fadeTo('300', '0.5', function () {
                        $(this).html(data.content).fadeTo('300', '1');
                    });
                } else if (typeof (data.content) === 'object') {
                    $.each(data.content, function (key, value) {
                        $(key).fadeTo('300', '0.5', function () {
                            $(this).html(value).fadeTo('300', '1');
                        });
                    });
                }
            }

            //DATA DINAMIC CONTENT
            if (data.divcontent) {
                if (typeof (data.divcontent) === 'string') {
                    $(data.divcontent[0]).html(data.divcontent[1]);
                } else if (typeof (data.divcontent) === 'object') {
                    $.each(data.divcontent, function (key, value) {
                        $(key).html(value);
                    });
                }
            }

            //DATA DINAMIC FADEOUT
            if (data.divremove) {
                if (typeof (data.divremove) === 'string') {
                    $(data.divremove).fadeOut();
                } else if (typeof (data.divremove) === 'object') {
                    $.each(data.divremove, function (key, value) {
                        $(value).fadeOut();
                    });
                }
            }

            //DATA CLICK
            if (data.forceclick) {
                if (typeof (data.forceclick) === 'string') {
                    setTimeout(function () {
                        $(data.forceclick).click();
                    }, 250);
                } else if (typeof (data.forceclick) === 'object') {
                    $.each(data.forceclick, function (key, value) {
                        setTimeout(function () {
                            $(value).click();
                        }, 250);
                    });
                }
            }

            //DATA DOWNLOAD IN j_downloa
            if (data.download) {
                $('.j_download').fadeTo('300', '0.5', function () {
                    $(this).html(data.download).fadeTo('300', '1');
                });
            }

            //DATA HREF VIEW
            if (data.view) {
                $('.wc_view').attr('href', data.view);
            }

            //DATA REORDER
            if (data.reorder) {
                $('.wc_drag_active').removeClass('btn_yellow');
                $('.wc_draganddrop').removeAttr('draggable');
            }

            //DATA CLEAR
            if (data.clear) {
                form.trigger('reset');
                if (form.find('.label_publish')) {
                    form.find('.label_publish').removeClass('active');
                }
            }

            //DATA CLEAR INPUT
            if (data.inpuval) {
                if (data.inpuval === 'null') {
                    $('.wc_value').val("");
                } else {
                    $('.wc_value').val(data.inpuval);
                }
            }

            //CLEAR INPUT FILE
            if (!data.error) {
                form.find('input[type="file"]').val('');
            }

            //CLEAR NFE XML
            if (data.nfexml) {
                $('.wc_nfe_xml').html("<a target='_blank' href='" + data.nfexml + "' title='Ver XML'>Ver XML</a>");
            }

            //DATA NFE PDF
            if (data.nfepdf) {
                $('.wc_nfe_pdf').html("<a target='_blank' href='" + data.nfepdf + "' title='Ver PDF'>Ver PDF</a>");
            }

            //FIX FOR HIGHLIGHT
            setTimeout(function () {
                if ($('*[class="brush: php;"]').length) {
                    $("head").append('<link rel="stylesheet" href="../_cdn/highlight.min.css">');
                    $.getScript('../_cdn/highlight.min.js', function () {
                        $('*[class="brush: php;"]').each(function (i, block) {
                            hljs.highlightBlock(block);
                        });
                    });
                }
            }, 500);
        }, 'json');
        
        return false;
    });

    //Coloca todos os formulários em AJAX mode e inicia LOAD ao submeter!
    $('html').on('submit', 'form:not(.ajax_off)', function () {
        
        var form = $(this);
        var callback = form.find('input[name="callback"]').val();
        var callback_action = form.find('input[name="callback_action"]').val();

        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        form.ajaxSubmit({
            url: '_ajax/' + callback + '.ajax.php',
            data: {callback_action: callback_action},
            dataType: 'json',
            beforeSubmit: function () {
                form.find('.form_load').fadeIn('fast');
                $('.trigger_ajax').fadeOut('fast');
            },
            uploadProgress: function (evento, posicao, total, completo) {
                var porcento = completo + '%';
                $('.workcontrol_upload_progrees').text(porcento);

                if (completo <= '80') {
                    $('.workcontrol_upload').fadeIn().css('display', 'flex');
                }
                if (completo >= '99') {
                    $('.workcontrol_upload').fadeOut('slow', function () {
                        $('.workcontrol_upload_progrees').text('0%');
                    });
                }
                //PREVENT TO RESUBMIT IMAGES GALLERY
                form.find('input[name="image[]"]').replaceWith($('input[name="image[]"]').clone());
            },
            success: function (data) {
                //REMOVE LOAD
                form.find('.form_load').fadeOut('slow', function () {
                    //EXIBE CALLBACKS
                    if (data.trigger) {
                        Trigger(data.trigger);
                    }

                    //REDIRECIONA
                    if (data.redirect) {
                        $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                        $('.workcontrol_upload').fadeIn().css('display', 'flex');
                        window.setTimeout(function () {
                            window.location.href = data.redirect;
                            if (window.location.hash) {
                                window.location.reload();
                            }
                        }, 1500);
                    }

                    //INTERAGE COM TINYMCE
                    if (data.tinyMCE) {
                        tinyMCE.activeEditor.insertContent(data.tinyMCE);
                        $('.workcontrol_imageupload').fadeOut('slow', function () {
                            $('.workcontrol_imageupload .image_default').attr('src', '../tim.php?src=admin/_img/no_image.jpg&w=500&h=300');
                        });
                    }

                    //GALLETY UPDATE HTML
                    if (data.gallery) {
                        form.find('.gallery').fadeTo('300', '0.5', function () {
                            $(this).html($(this).html() + data.gallery).fadeTo('300', '1');
                        });
                    }

                    //DATA CONTENT IN j_content
                    if (data.content) {
                        if (typeof (data.content) === 'string') {
                            $('.j_content').fadeTo('300', '0.5', function () {
                                $(this).html(data.content).fadeTo('300', '1');
                            });
                        } else if (typeof (data.content) === 'object') {
                            $.each(data.content, function (key, value) {
                                $(key).fadeTo('300', '0.5', function () {
                                    $(this).html(value).fadeTo('300', '1');
                                });
                            });
                        }
                    }

                    //DATA DINAMIC CONTENT
                    if (data.divcontent) {
                        if (typeof (data.divcontent) === 'string') {
                            $(data.divcontent[0]).html(data.divcontent[1]);
                        } else if (typeof (data.divcontent) === 'object') {
                            $.each(data.divcontent, function (key, value) {
                                $(key).html(value);
                            });
                        }
                    }

                    //DATA DINAMIC FADEOUT
                    if (data.divremove) {
                        if (typeof (data.divremove) === 'string') {
                            $(data.divremove).fadeOut();
                        } else if (typeof (data.divremove) === 'object') {
                            $.each(data.divremove, function (key, value) {
                                $(value).fadeOut();
                            });
                        }
                    }

                    //DATA CLICK
                    if (data.forceclick) {
                        if (typeof (data.forceclick) === 'string') {
                            setTimeout(function () {
                                $(data.forceclick).click();
                            }, 250);
                        } else if (typeof (data.forceclick) === 'object') {
                            $.each(data.forceclick, function (key, value) {
                                setTimeout(function () {
                                    $(value).click();
                                }, 250);
                            });
                        }
                    }

                    //DATA DOWNLOAD IN j_downloa
                    if (data.download) {
                        $('.j_download').fadeTo('300', '0.5', function () {
                            $(this).html(data.download).fadeTo('300', '1');
                        });
                    }

                    //DATA HREF VIEW
                    if (data.view) {
                        $('.wc_view').attr('href', data.view);
                    }

                    //DATA REORDER
                    if (data.reorder) {
                        $('.wc_drag_active').removeClass('btn_yellow');
                        $('.wc_draganddrop').removeAttr('draggable');
                    }

                    //DATA CLEAR
                    if (data.clear) {
                        form.trigger('reset');
                        if (form.find('.label_publish')) {
                            form.find('.label_publish').removeClass('active');
                        }
                    }

                    //DATA CLEAR INPUT
                    if (data.inpuval) {
                        if (data.inpuval === 'null') {
                            $('.wc_value').val("");
                        } else {
                            $('.wc_value').val(data.inpuval);
                        }
                    }

                    //CLEAR INPUT FILE
                    if (!data.error) {
                        form.find('input[type="file"]').val('');
                    }

                    //CLEAR NFE XML
                    if (data.nfexml) {
                        $('.wc_nfe_xml').html("<a target='_blank' href='" + data.nfexml + "' title='Ver XML'>Ver XML</a>");
                    }

                    //DATA NFE PDF
                    if (data.nfepdf) {
                        $('.wc_nfe_pdf').html("<a target='_blank' href='" + data.nfepdf + "' title='Ver PDF'>Ver PDF</a>");
                    }

                    //FIX FOR HIGHLIGHT
                    setTimeout(function () {
                        if ($('*[class="brush: php;"]').length) {
                            $("head").append('<link rel="stylesheet" href="../_cdn/highlight.min.css">');
                            $.getScript('../_cdn/highlight.min.js', function () {
                                $('*[class="brush: php;"]').each(function (i, block) {
                                    hljs.highlightBlock(block);
                                });
                            });
                        }
                    }, 500);
                });
            }
        });
        return false;
    });

    //WC COMBO BOX
    $('.jwc_combo').change(function () {
        var callback = $(this).attr('data-c');
        var callback_action = $(this).attr('data-ca');
        var key = $(this).find('option').filter(":selected").val();

        $.post('_ajax/' + callback + '.ajax.php', {callback: callback, callback_action: callback_action, key: key}, function (data) {
            if (data.target) {
                $(data.target).html(data.content);
            }
        }, 'json');
    });

    //Ocultra Trigger clicada
    $('html').on('click', '.trigger_ajax, .trigger_modal', function () {
        $(this).fadeOut('slow', function () {
            $(this).remove();
        });
    });

    //Publish Effect
    $('.label_publish').click(function () {
        if (!$(this).find('input').is(':checked')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });

    //############# EAD SUPPORT
    $('html').on('click', '.j_ead_support_action', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var SendId = $(this).attr('id');
        var Callback_Action = $(this).attr('data-action');

        if (Callback_Action === 'ead_support_delete') {
            $("#" + SendId).find('.ead_support_response_edit_modal.remove').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');

            //OPEN RESPONSE EDIT
        } else if (Callback_Action === 'ead_support_edit') {
            $("#" + SendId).find('.ead_support_response_edit_modal.response').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');

            //SET SUPPORT TO COMPLETED
        } else if (Callback_Action === 'ead_support_set_answered') {
            $(this).fadeOut();
            $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'ead_support_set_answered', id: SendId}, function () {
                $('.j_ead_support_status').fadeOut(function () {
                    $(this).html("<span class='status bar_blue radius'>Respondido</span>").fadeIn();
                });
            }, 'json');

            //OPEN REPLY EDIT
        } else if (Callback_Action === 'ead_support_reply_edit') {
            wc_tinyMCE_basic();
            $("#" + SendId).find('.ead_support_response_edit_modal.reply').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');

            //SET SUPPORT TO PUBLISH
        } else if (Callback_Action === 'ead_support_publish') {
            $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'ead_support_publish', id: SendId}, function () {
                $('.ead_support_publish').fadeOut(function () {
                    $(this).html("<span class='btn btn_green icon-checkmark j_ead_support_action' data-action='ead_support_unpublish' id='" + SendId + "'>Publicado</span>").fadeIn();
                });
            }, 'json');

            //SET SUPPORT TO UNPUBLISH
        } else if (Callback_Action === 'ead_support_unpublish') {
            $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'ead_support_unpublish', id: SendId}, function () {
                $('.ead_support_publish').fadeOut(function () {
                    $(this).html("<span class='btn btn_yellow icon-eye-blocked j_ead_support_action' data-action='ead_support_publish' id='" + SendId + "'>Publicar</span>").fadeIn();
                });
            }, 'json');

            //SET SUPPORT TO PUBLISH AND REDIRECT
        } else if (Callback_Action === 'ead_support_publish_redirect') {
            $('.jead_support_publish').click();

            $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
            $('.workcontrol_upload').fadeIn().css('display', 'flex');
            window.setTimeout(function () {
                window.location.href = $('#support_next').attr('href');
                if (window.location.hash) {
                    window.location.reload();
                }
            }, 1500);
        }
    });

    $('.ead_support_response').on('click', '.j_ead_support_action_close', function (e) {
        $('.ead_support_response_edit_modal').fadeOut(200, function () {
            $(this).find('form').fadeOut(0);
        });
    });

    //############# EAD COMMENT
    $('html').on('click', '.j_comment_action', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var SendId = $(this).attr('id');
        var Callback_Action = $(this).attr('data-action');

        if (Callback_Action === 'comment_delete') {
            $("#" + SendId).find('.ead_support_response_edit_modal.remove').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');

            //OPEN RESPONSE EDIT
        } else if (Callback_Action === 'comment_edit') {
            $("#" + SendId).find('.ead_support_response_edit_modal.response').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');

            //SET SUPPORT TO COMPLETED
        } else if (Callback_Action === 'comment_completed') {
            $(this).fadeOut();
            $.post('_ajax/Comments.ajax.php', {callback: 'Comments', callback_action: 'aprove', id: SendId}, function () {
                $('.j_comment_status').fadeOut(function () {
                    $(this).html("<span class='status bar_green radius'>Concluído</span>").fadeIn();
                });
            }, 'json');

            //OPEN REPLY EDIT
        } else if (Callback_Action === 'ead_support_reply_edit') {
            wc_tinyMCE_basic();
            $("#" + SendId).find('.ead_support_response_edit_modal.reply').fadeIn(200, function () {
                $(this).find('form').fadeIn(0);
            }).css('display', 'flex');
        }
    });

    $('.ead_support_response').on('click', '.j_comment_action_close', function (e) {
        $('.ead_support_response_edit_modal').fadeOut(200, function () {
            $(this).find('form').fadeOut(0);
        });
    });

    $('.reload_notes').click(function () {
        var UserId = $(this).attr('id');
        $.post('_ajax/Users.ajax.php', {callback: 'Users', callback_action: 'list_notes_all', user_id: UserId}, function (data) {
            //CONTENT UPDATE
            //DATA CONTENT IN j_content
            if (data.content) {
                if (typeof (data.content) === 'string') {
                    $('.j_content').fadeTo('300', '0.5', function () {
                        $(this).html(data.content).fadeTo('300', '1');
                    });
                } else if (typeof (data.content) === 'object') {
                    $.each(data.content, function (key, value) {
                        $(key).fadeTo('300', '0.5', function () {
                            $(this).html(value).fadeTo('300', '1');
                        });
                    });
                }
            }
        }, 'json');
    });

    //############# POSTS
    //CAPA VIEW
    $('.wc_loadimage').change(function () {
        var input = $(this);
        var target = $('.' + input.attr('name'));
        var fileDefault = target.attr('default');

        if (!input.val()) {
            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            return false;
        }

        if (this.files && (this.files[0].type.match("image/jpeg") || this.files[0].type.match("image/png"))) {
            TriggerClose();
            var reader = new FileReader();
            reader.onload = function (e) {
                target.fadeOut('fast', function () {
                    $(this).attr('src', e.target.result).width('100%').fadeIn('fast');
                });
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            Trigger('<div class="trigger trigger_alert trigger_ajax"><b class="icon-warning">ERRO AO SELECIONAR:</b> O arquivo <b>' + this.files[0].name + '</b> não é válido! <b>Selecione uma imagem JPG ou PNG!</b></div>');
            target.fadeOut('fast', function () {
                $(this).attr('src', fileDefault).fadeIn('slow');
            });
            input.val('');
            return false;
        }
    });

    //############# PRODUTOS

    //CATEGORY SIZES
    $('.jwc_pdtsection_selector').change(function () {
        var SectionSizes = $(this).find("option:selected").attr('class');
        $('.jwc_pdtsection_selector_target').val(SectionSizes);
    });

    //STOCK SIZE
    $('.jwc_product_stock').change(function () {
        var getPdtId = $('input[name="pdt_id"]').val();
        var getSizesByCat = $(this).find("option:selected").val();
        $.post('_ajax/Products.ajax.php', {callback: 'Products', callback_action: 'cat_sizes', catId: getSizesByCat, pdtId: getPdtId}, function (data) {
            if (data.cat_sizes) {
                $('.jwc_product_stock_target').html(data.cat_sizes);
            }
        }, 'json');
    });

    //GALLERY IMAGE REMOVE
    $('.pdt_single_image').on('click', 'img', function () {
        var imgDef = $(this);
        var imgDel = $(this).attr('id');
        var CallBack = $(this).attr('rel');
        var Delete = confirm('Deseja DELETAR essa imagem?');
        if (Delete === true) {
            $.post('_ajax/' + CallBack + '.ajax.php', {callback: CallBack, callback_action: 'gbremove', img: imgDel}, function (data) {
                imgDef.fadeOut('fast', function () {
                    $(this).remove();
                });
            });
        }
    });

    //CANCEL ORDER
    $('.j_order_cancel').click(function () {
        var CancelId = "callback=Orders&callback_action=cancel&order_id=" + $(this).attr('id');
        $('.workcontrol_upload').fadeIn().css('display', 'flex');
        $.post("_ajax/Orders.ajax.php", CancelId, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
            }
            if (data.success) {
                $('.j_order_cancel').fadeOut('fast', function () {
                    $('.j_delete_action').fadeIn('fast');
                });

                $('.j_statustext').text('Cancelado');
                $('.workcontrol_upload').fadeOut('fast');
            }
        }, 'json');
    });

    //OPEN STOCK MANAGER
    $('.wc_pdt_stock').click(function () {
        $('.workcontrol_pdt_size').fadeIn('fast');
    });

    //CLOSE STOCK MANAGER
    $('.workcontrol_pdt_size_close').click(function () {
        $('.workcontrol_pdt_size').fadeOut('fast');
        $('.trigger_ajax').fadeOut('fast', function () {
            $(this).remove();
        });
    });

    //############# GERAIS
    //DELETE CONFIRM
    $('html, body').on('click', '.j_delete_action', function (e) {
        var RelTo = $(this).attr('rel');
        $(this).fadeOut(10, function () {
            $('.' + RelTo + '[id="' + $(this).attr('id') + '"] .j_delete_action_confirm:eq(0)').fadeIn(10);
        });

        e.preventDefault();
        e.stopPropagation();
    });

    //DELETE CONFIRM ACTION
    $('html, body').on('click', '.j_delete_action_confirm', function (e) {
        var Prevent = $(this);
        var DelId = $(this).attr('id');
        var RelTo = $(this).attr('rel');
        var Callback = $(this).attr('callback');
        var Callback_action = $(this).attr('callback_action');

        $('.workcontrol_upload p').html("Processando requisição, aguarde!");
        $('.workcontrol_upload').fadeIn().css('display', 'flex');

        $.post('_ajax/' + Callback + '.ajax.php', {callback: Callback, callback_action: Callback_action, del_id: DelId}, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
                $('.' + RelTo + '[id="' + Prevent.attr('id') + '"] .j_delete_action_confirm:eq(0)').fadeOut('fast', function () {
                    $('.' + RelTo + '[id="' + Prevent.attr('id') + '"] .j_delete_action:eq(0)').fadeIn('fast');
                });
            } else {
                $('.' + RelTo + '[id="' + DelId + '"]').fadeOut('fast');
            }

            //REDIRECIONA
            if (data.redirect) {
                $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                $('.workcontrol_upload').fadeIn().css('display', 'flex');
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                    if (window.location.hash) {
                        window.location.reload();
                    }
                }, 1500);
            } else {
                $('.workcontrol_upload').fadeOut();
            }

            //CONTENT UPDATE
            if (data.content) {
                $('.j_content').fadeTo('300', '0.5', function () {
                    $(this).html(data.content).fadeTo('300', '1');
                    HeaderRender('wc_normalize_height');
                });
            }

            //INPUT CLEAR
            if (data.inpuval) {
                if (data.inpuval === 'null') {
                    $('.wc_value').val("");
                } else {
                    $('.wc_value').val(data.inpuval);
                }
            }

            //DINAMIC CONTENT
            if (data.divcontent) {
                $(data.divcontent[0]).html(data.divcontent[1]);
            }
        }, 'json');

        e.preventDefault();
        e.stopPropagation();
    });

    //AJAX ACTIVE ACTION
    $('.jwc_active_action').click(function () {
        var Prevent = $(this);
        var ThisId = $(this).attr('id');
        var RelTo = $(this).attr('rel');
        var Callback = $(this).attr('callback');
        var Callback_action = $(this).attr('callback_action');
        $.post('_ajax/' + Callback + '.ajax.php', {callback: Callback, callback_action: Callback_action, id: ThisId}, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
            }

            if (data.active === 1) {
                Prevent.fadeOut(200, function () {
                    $('#' + ThisId).find('.jwc_inactive').fadeIn();
                    $('#' + ThisId).find('.jwc_status').text('Ativo').removeClass('font_red').addClass('font_green');
                });
            }

            if (data.active === 0) {
                Prevent.fadeOut(200, function () {
                    $('#' + ThisId).find('.jwc_active').fadeIn();
                    $('#' + ThisId).find('.jwc_status').text('Inativo').removeClass('font_green').addClass('font_red');
                });
            }
        }, 'json');
    });

    if ($('.jwc_api_test').length) {
        $(".jwc_api_test").click(function () {
            if (!WcTestApi) {
                var WcTestApi = $(this).attr("href");
            }
            $(this).attr("href", WcTestApi + "&times=" + $.now());
        });
    }

    //MODAL UPLOAD
    $('.workcontrol_imageupload_close').click(function () {
        $("div#" + $(this).attr("id")).fadeOut("fast");
    });

    //SEARCH REMOVE
    $(".wc_delete_search").click(function () {
        var DeleteSearch = confirm("Ao continuar todos os dados de pesquisa seram removidos!");
        if (DeleteSearch !== true) {
            return false;
        }
    });

    //CONFIG CLEAR
    $(".wc_resetconfig").click(function () {
        var DeleteSearch = confirm("Ao continuar todas as configurações serão setadas com o valor das constantes!");
        if (DeleteSearch !== true) {
            return false;
        }
    });

    //APP ORDER CREATE
    var Callback = "Orders.ajax.php";
    var CallbackAction = "wcOrderCreateApp";

    //NAME
    $('.jwc_ordercreate_name').keyup(function () {
        var Search = $(this).val();
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'Search': Search}, function (data) {
            if (data.result) {
                $('.jwc_ordercreate_name_r').html(data.result);
            }
        }, 'json');
    });

    //ADDR SELECT
    $('.jwc_ordercreate_name_r').on('click', 'input', function () {
        var UserId = $(this).attr('value');
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'AddrUser': UserId}, function (data) {
            if (data.result) {
                $('.jwc_ordercreate_name_addr').html(data.result);
            }
            $('.jwc_client').fadeOut();
            $('html, body').animate({scrollTop: $("#addr").offset().top}, 800);
        }, 'json');

    });

    //ADDR SET
    $('.jwc_ordercreate_name_addr').on('click', 'input', function () {
        var AddrId = $(this).attr('value');
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'setAddr': AddrId}, function (data) {
            $('.jwc_addr').fadeOut();
            $('.jwc_ordercreate_products, .wc_ordercreate_name_pdt').fadeIn();
            $('html, body').animate({scrollTop: $("#pdts").offset().top}, 800);
        }, 'json');
    });

    //PDTS
    $('.jwc_ordercreate_products').keyup(function () {
        var PdtSearch = $(this).val();
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'PdtSearch': PdtSearch}, function (data) {
            if (data.result) {
                $('.jwc_ordercreate_name_pdt').html(data.result).fadeIn();
            }
        }, 'json');
    });

    //ADD
    $('.jwc_ordercreate_name_pdt').on('click', '.jwc_order_create_add .btn', function (data) {
        var StockId = $(this).attr('id');
        var StockQtd = $("article#" + StockId).find('option:selected').val();
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'StockId': StockId, 'StockQtd': StockQtd}, function (data) {
            if (data.result) {
                $('.jwc_pdt').fadeOut();
                $('.jwc_order_create_cart').html(data.result);
                $('#' + StockId).css("border-color", 'green');
            }
            if (data.trigger) {
                Trigger(data.trigger);
            }
        }, 'json');
    });

    //REMOVE
    $('.jwc_order_create_cart').on('click', '.jwc_order_create_item_remove', function () {
        var RemoveId = $(this).attr('id');
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': CallbackAction, 'Remove': RemoveId}, function (data) {
            $('.item_' + RemoveId).fadeOut(function () {
                $(this).remove();
            });
            if (data.result) {
                $('.jwc_order_create_cart').html(data.result);
            }
        }, 'json');
    });

    //FINISH
    $('.jwc_order_create_cart').on('click', '.jwc_orderapp_finish_order', function () {
        $('.workcontrol_upload').fadeIn().css('display', 'flex');
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': 'OrderAppFinish'}, function (data) {
            $('.wc_shipment_calculate').html(data.cart_shipment);
            $('.jwc_order_create_shipment_cartprice, .jwc_order_create_shipment_carttotal').text(data.wc_cart_total);
            $('.workcontrol_upload, .trigger').fadeOut(200);
            $('.wc_orderapp_finish').fadeIn(198);
        }, 'json');
    });

    //SHIP SELECT
    $('.wc_shipment_calculate').on('click', 'input', function () {
        var ShipCode = $(this).attr('id');
        var ShipValue = $(this).attr('value');
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': 'AppOrderCreate', 'action': 'setship', ShipCode: ShipCode, ShipValue: ShipValue}, function (data) {
            if (data.wc_cart_total) {
                $('.jwc_order_create_shipment_carttotal').text(data.wc_cart_total);
            }
            if (data.wc_cart_cupom) {
                $('.jwc_order_create_shipment_cartcupom').text(data.wc_cart_cupom);
            }
        }, 'json');
    });

    //ADD CUPOM
    $('.jwc_order_create_shipment_cupom').keyup(function () {
        var OrderDisount = $(this).val();
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': 'AppOrderCreate', 'action': 'setcupom', OrderDisount: OrderDisount}, function (data) {
            if (data.wc_cart_total) {
                $('.jwc_order_create_shipment_carttotal').text(data.wc_cart_total);
            }
            if (data.wc_cart_cupom) {
                $('.jwc_order_create_shipment_cartcupom').text(data.wc_cart_cupom);
            }
        }, 'json');
    });

    //ORDER CREATE
    $('.jwc_order_create_shipment_ordercreate').click(function () {
        $.post('_ajax/' + Callback, {callback: 'Orders', 'callback_action': 'AppOrderCreate', 'action': 'create'}, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
            } else {
                TriggerClose();
                $('.jwc_order_created_link').attr('href', data.wc_cart_link);
                $('.jwc_order_created_pay').attr('href', data.wc_cart_pay);
                $('.jwc_order_created_paytext').text(data.wc_cart_pay);
                $('.box_finich').fadeOut(200, function () {
                    $('.box_share').fadeIn(200);
                    //CLOSE AND CLEAR
                    $('.jwc_orderapp_finish_close').click(function () {
                        $('.wc_orderapp_finish').fadeOut(200, function () {
                            window.location.href = 'dashboard.php?wc=orders/create&reset=true';
                        });
                    });
                });
            }
        }, 'json');
    });

    //CLOSE FINISH MODAL
    $('.jwc_orderapp_finish_close').click(function () {
        $('.wc_orderapp_finish').fadeOut(200);
    });


    //######## WC CODES
    $('.jwc_codes_create').click(function () {
        $('.wc_codes_create').fadeIn();
        return false;
    });

    $('.jwc_codes_close').click(function () {
        $('.wc_codes_create').fadeOut(400, function () {
            window.location.reload();
        });
        return false;
    });

    $('.jwc_codes_edit').click(function () {
        var code_id = $(this).attr('id');
        $.post('_ajax/Codes.ajax.php', {callback: 'Codes', callback_action: 'edit', code_id: code_id}, function (data) {
            if (data.trigger) {
                Trigger(data.trigger);
            } else {
                $.each(data.data, function (key, value) {
                    $('input[name="' + key + '"], textarea[name="' + key + '"]').val(value);
                });
                $('.wc_codes_create').fadeIn();
            }
        }, 'json');
    });

    //######## WC DRAG AND DROP
    $("html").on('click', '.wc_drag_active', function () {
        $(this).toggleClass('btn_yellow');

        if ($('.wc_draganddrop').attr('draggable')) {
            $('.wc_draganddrop').removeAttr('draggable');
            $('html').unbind("drag dragover dragleave drop");
        } else {
            $('.wc_draganddrop').attr('draggable', true);

            //DRAG EVENT
            $("html").on("drag", ".wc_draganddrop", function (event) {
                event.preventDefault();
                event.stopPropagation();
                wcDragContent = $(this);
                wcDragPosition = $(this).index();
            });

            //DRAG OVER EVENT
            $("html").on("dragover", ".wc_draganddrop", function (event) {
                event.preventDefault();
                event.stopPropagation();

                $(this).css('border', '1px dashed #ccc');
            });

            //DRAGB LEAVE EVENT
            $("html").on("dragleave", ".wc_draganddrop", function (event) {
                event.preventDefault();
                event.stopPropagation();

                $(this).css('border', '0');
            });

            //DROP EVENT
            $("html").on("drop", ".wc_draganddrop", function (event) {
                event.preventDefault();
                event.stopPropagation();

                var wcDropElement = $(this);
                var CallBack = $(this).attr('callback');
                var CallBackAction = $(this).attr('callback_action');

                $(wcDropElement).css('border', '0');
                if (wcDragPosition > wcDropElement.index()) {
                    wcDropElement.before(wcDragContent);
                } else {
                    wcDropElement.after(wcDragContent);
                }

                Reorder = new Array();
                $.each($(".wc_draganddrop"), function (i, el) {
                    Reorder.push([el.id, i + 1]);
                });
                $.post('_ajax/' + CallBack + '.ajax.php', {callback: CallBack, callback_action: CallBackAction, Data: Reorder});
            });
        }
    });

    //STUDENT ORDER VIEW :: CLOSE
    $('.j_student_order_close').click(function () {
        $('.student_gerent_orders_detail').fadeOut(200);
    });

    //STUDENT ORDER VIEW :: OPEN
    $('.j_student_order_open').click(function () {
        var getOrderId = $(this).attr('id');
        $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'student_get_order', 'order_id': getOrderId}, function (data) {
            if (data.order) {
                $('.j_order_detail').html(data.order);
                $('.student_gerent_orders_detail').fadeIn(200).css('display', 'flex');
            }
        }, 'json');
    });

    //COPY TO CLIPBOARD
    $('.jwc_copy').click(function () {
        $("input[name='" + $(this).attr('id') + "'], textarea[name='" + $(this).attr('id') + "']").select();
        document.execCommand('copy');

        var ButtonClip = $(this);
        ButtonClip.removeClass('icon-new-tab').addClass('icon-checkmark active');
    });


    $('.wc_clip').click(function () {
        $('#' + $(this).attr('rel')).select();
        document.execCommand('copy');

        var ButtonClip = $(this);
        ButtonClip.removeClass('btn_blue icon-embed2').addClass('icon-checkmark btn_green');
    });

    //############## SOCIAL SHARE
    function HeaderRender(Class) {
        var maxHeight = 0;
        $("." + Class + ":visible").each(function () {
            if ($(this).height() > maxHeight) {
                maxHeight = $(this).height();
            }
        }).height(maxHeight);
    }

    $(window).load(function () {
        HeaderRender('wc_normalize_height');
    });

    //WC LOGIN FIX
    setInterval(function () {
        $.post('_ajax/Dashboard.ajax.php', {callback: 'Dashboard', callback_action: 'wc_login_fix'}, function (data) {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        }, 'json');
    }, 5 * 60 * 1000);

    //WC LICENSE
    $('.wc_domain_license').click(function () {
        $("body").append("<div class='wc_domain_license_box'><div class='wc_domain_license_box_content'><span class='icon-warning icon-notext font_yellow'></span><p><b>SOFTWARE NÃO LICENCIADO!</b></p><p>Você está utilizando uma versão não licenciada do Work Control® estando sujeito a multa e processos legais por cópia não licenciada.</p><p>Utilizar este software sem licença é crime!<br>Lei Nº 9.610, de 19 de fevereiro de 1998 de direitos autorais.</p><p style='font-size: 0.7rem; color: #ccc'>Work Control® - Todos os Direitos Reservados para UpInside Treinamentos.<br>CNPJ: 10.548.698/0001-38 / cursos@upinside.com.br</p></div></div>");
        $('.wc_domain_license_box').click(function () {
            $(this).fadeOut(200, function () {
                $(this).remove();
            });
        });
    });

    //WC FILTERS
    $('.jwc_filters').click(function () {
        if ($('.wc_filters').is(':visible')) {
            $('.wc_filters').slideUp();
        } else {
            $('.wc_filters').slideDown();
        }
    });

    $('.support_response_filter').click(function () {
        var Form = $('form[name="user_manager"]').submit();
        var FormGetData = Form.serialize();
        $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'filter_support', FormGetData: FormGetData}, function (data) {
            //REDIRECIONA
            if (data.redirect) {
                $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                $('.workcontrol_upload').fadeIn().css('display', 'flex');
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                    if (window.location.hash) {
                        window.location.reload();
                    }
                }, 1500);
            }
        }, 'json');
        return false;
    });

    $('.support_response_filter_list').click(function () {
        var Form = $('form[name="user_manager"]').submit();
        var FormGetData = Form.serialize();
        $.post('_ajax/Courses.ajax.php', {callback: 'Courses', callback_action: 'filter_support_list', FormGetData: FormGetData}, function (data) {
            //REDIRECIONA
            if (data.redirect) {
                $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
                $('.workcontrol_upload').fadeIn().css('display', 'flex');
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                    if (window.location.hash) {
                        window.location.reload();
                    }
                }, 1500);
            }
        }, 'json');
        return false;
    });
});

//FUNÇÕES
//############## DASHBOARD STATS
function Dashboard() {
    $.post('_ajax/Dashboard.ajax.php', {callback: 'Dashboard', callback_action: 'siteviews'}, function (data) {
        $('.wc_useronline').text(data.useron);
        $('.wc_studentonline').text(data.students);
        $('.wc_viewsusers b').text(data.users);
        $('.wc_viewsviews b').text(data.views);
        $('.wc_viewspages b').text(data.pages);
        $('.wc_viewsstats b').text(data.stats);

        //REDIRECIONA
        if (data.redirect) {
            $('.workcontrol_upload p').html("Atualizando dados, aguarde!");
            $('.workcontrol_upload').fadeIn().css('display', 'flex');
            window.setTimeout(function () {
                window.location.href = data.redirect;
                if (window.location.hash) {
                    window.location.reload();
                }
            }, 1500);
        }
    }, 'json');
}

function OnlineNow(user, url) {
    $.post('_ajax/Dashboard.ajax.php', {callback: 'Dashboard', callback_action: 'onlinenow', user: user, url: url}, function (data) {
        $('.wc_onlinenow').html(data.data);
        $('.jwc_onlinenow').html("ONLINE AGORA: " + data.now);
    }, 'json');
}

//############## MODAL MESSAGE
function Trigger(Message) {
    $('.trigger_ajax').fadeOut('fast', function () {
        $(this).remove();
    });
    $('body').before("<div class='trigger_modal'>" + Message + "</div>");
    $('.trigger_ajax').fadeIn();
}

function TriggerClose() {
    $('.trigger_ajax').fadeOut('fast', function () {
        $(this).remove();
    });
}

function wcUrlParam(name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    
    return (results ? results[1] : null);
}
