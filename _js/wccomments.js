$(function () {
    $('.dashboard_content').on('click', '.wc_comment_action', function () {        
        var CommId = $(this).attr('rel');
        var Action = $(this).attr('action');
        var Comment = $('#' + CommId);

        $.post('_ajax/Comments.ajax.php', {callback: 'Comments', callback_action: Action, id: CommId}, function (data) {
            //LIKES
            if (data.like) {
                Comment.find("a[action='like']").fadeOut(400);
                if (Comment.find(".comm_likes#" + CommId + " .na").length) {
                    Comment.find(".comm_likes#" + CommId + " .na").html(data.admin);
                } else {
                    Comment.find(".comm_likes#" + CommId + " span").after(", " + data.admin);
                }
            }

            //APROVAR
            if (data.aprove) {
                Comment.find('.comm_content').css('border-color', '#00B494');
                Comment.find(".aprove").html(data.aprove);
                if (data.alias) {
                    $('#' + data.alias).find('.comm_content').css('border-color', '#00B494');
                    $('#' + data.alias).find(".aprove").html(data.aprove);
                }
            }

            //DELETAR
            if (data.remove) {
                Comment.fadeOut(function () {
                    $(this).remove();
                });
                
                if (data.alias) {
                    $('#' + data.alias).find('.comm_content').css('border-color', '#00B494');
                    $('#' + data.alias).find(".aprove").html(data.aprove);
                }
            }

            //ERROR
            if (data.trigger) {
                Trigger(data.trigger);
            }
        }, 'json');
        return false;
    });

});