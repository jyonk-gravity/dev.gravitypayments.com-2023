(function ($) {
    inlineEditPost = {
        init: function () {
            var t = this, quick_edit_row = $('#inline-edit'), bulk_row = $('#bulk-edit');

            t.type = $('table.widefat').hasClass('pages') ? 'page' : 'post';
            t.what = '#post-';

            // prepare the edit rows
            quick_edit_row.keyup(function (e) {
                if (e.which == 27)
                    return inlineEditPost.revert();
            });
            bulk_row.keyup(function (e) {
                if (e.which == 27)
                    return inlineEditPost.revert();
            });

            jQuery('a.cancel', quick_edit_row).click(function () {
                return inlineEditPost.revert();
            });
            jQuery('a.save', quick_edit_row).click(function () {
                return inlineEditPost.save(this);
            });
            jQuery('td', quick_edit_row).keydown(function (e) {
                if (e.which == 13)
                    return inlineEditPost.save(this);
            });

            jQuery('a.cancel', bulk_row).click(function () {
                return inlineEditPost.revert();
            });

            jQuery('#inline-edit .inline-edit-private input[value="private"]').click(function () {
                var pw = $('input.inline-edit-password-input');
                if ($(this).prop('checked')) {
                    pw.val('').prop('disabled', true);
                } else {
                    pw.prop('disabled', false);
                }
            });

            // add events
            jQuery(document).on('click', 'a.editinline', function () {
                var obj = this;
                if (typenow == $(this).attr('real')) {
                    inlineEditPost.edit(obj);
                    return false;
                } else {
                    typenow = t.type = $(this).attr('real');
                    data = {
                        action: 'get_edit_inline_html',
                        post_type: $(this).attr('real'),
                        security: jQuery('#owf_inbox_ajax_nonce').val()
                    };
                    jQuery(this).parent().children('.loading').show();
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery(obj).parent().children('.loading').hide();
                        jQuery('#wf_edit_inline_content').html(response.data);
                        inlineEditPost.edit(obj);
                        jQuery('.inline-edit-status').hide();
                        return false;
                    });
                }

            });

            $('#bulk-title-div').parents('fieldset').after(
                $('#inline-edit fieldset.inline-edit-categories').clone()
            ).siblings('fieldset:last').prepend(
                $('#inline-edit label.inline-edit-tags').clone()
            );

            // hiearchical taxonomies expandable?
            $('span.catshow').click(function () {
                $(this).hide().next().show().parent().next().addClass('cat-hover');
            });

            $('span.cathide').click(function () {
                $(this).hide().prev().show().parent().next().removeClass('cat-hover');
            });

            $('select[name="_status"] option[value="future"]', bulk_row).remove();

            $('#doaction, #doaction2').click(function (e) {
                var n = $(this).attr('id').substr(2);
                if ($('select[name="' + n + '"]').val() == 'edit') {
                    e.preventDefault();
                    t.setBulk();
                } else if ($('form#posts-filter tr.inline-editor').length > 0) {
                    t.revert();
                }
            });

            $('#post-query-submit').mousedown(function (e) {
                t.revert();
                $('select[name^="action"]').val('-1');
            });
        },
        toggle: function (el) {
            var t = this;
            $(t.what + t.getId(el)).css('display') == 'none' ? t.revert() : t.edit(el);
        },
        setBulk: function () {
            var te = '', type = this.type, tax, c = true;
            this.revert();

            $('#bulk-edit td').attr('colspan', $('.widefat:first thead th:visible').length);
            $('table.widefat tbody').prepend($('#bulk-edit'));
            $('#bulk-edit').addClass('inline-editor').show();

            $('tbody th.check-column input[type="checkbox"]').each(function (i) {
                if ($(this).prop('checked')) {
                    c = false;
                    var id = $(this).val(), theTitle;
                    theTitle = $('#inline_' + id + ' .post_title').text() || inlineEditL10n.notitle;
                    te += '<div id="ttle' + id + '"><a id="_' + id + '" class="ntdelbutton" title="' + inlineEditL10n.ntdeltitle + '">X</a>' + theTitle + '</div>';
                }
            });

            if (c)
                return this.revert();

            $('#bulk-titles').html(te);
            $('#bulk-titles a').click(function () {
                var id = $(this).attr('id').substr(1);

                $('table.widefat input[value="' + id + '"]').prop('checked', false);
                $('#ttle' + id).remove();
            });

            // enable autocomplete for tags
            if ('post' == type) {
                // support multi taxonomies?
                tax = 'post_tag';
                $('tr.inline-editor textarea[name="tax_input[' + tax + ']"]').suggest('admin-ajax.php?action=ajax-tag-search&tax=' + tax, {
                    delay: 500,
                    minchars: 2,
                    multiple: true,
                    multipleSep: ', '
                });
            }
            $('html, body').animate({ scrollTop: 0 }, 'fast');
        },
        edit: function (id) {
            var t = this, fields, editRow, rowData, status, pageOpt, pageLevel, nextPage, pageLoop = true, nextLevel,
                cur_format, f;
            t.revert();

            if (typeof (id) == 'object')
                id = t.getId(id);

            fields = ['post_title', 'post_name', 'post_author', '_status', 'jj', 'mm', 'aa', 'hh', 'mn', 'ss', 'post_password', 'post_format'];
            if (t.type == 'page')
                fields.push('post_parent', 'menu_order', 'page_template');

            // add the new blank row
            editRow = $('#inline-edit').clone(true);

            $('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

            if ($(t.what + id).hasClass('alternate'))
                $(editRow).addClass('alternate');
            $(t.what + id).hide().after(editRow);

            // populate the data
            rowData = $('#inline_' + id);
            if (!$(':input[name="post_author"] option[value="' + $('.post_author', rowData).text() + '"]', editRow).val()) {
                // author no longer has edit caps, so we need to add them to the list of authors
                $(':input[name="post_author"]', editRow).prepend('<option value="' + $('.post_author', rowData).text() + '">' + $('#' + t.type + '-' + id + ' .author').text() + '</option>');
            }
            if ($(':input[name="post_author"] option', editRow).length == 1) {
                $('label.inline-edit-author', editRow).hide();
            }

            // hide unsupported formats, but leave the current format alone
            cur_format = $('.post_format', rowData).text();
            $('option.unsupported', editRow).each(function () {
                var $this = $(this);
                if ($this.val() != cur_format)
                    $this.remove();
            });

            for (f = 0; f < fields.length; f++) {
                $(':input[name="' + fields[f] + '"]', editRow).val($('.' + fields[f], rowData).text());
            }

            if ($('.comment_status', rowData).text() == 'open')
                $('input[name="comment_status"]', editRow).prop('checked', true);
            if ($('.ping_status', rowData).text() == 'open')
                $('input[name="ping_status"]', editRow).prop('checked', true);
            if ($('.sticky', rowData).text() == 'sticky')
                $('input[name="sticky"]', editRow).prop('checked', true);

            // hierarchical taxonomies
            $('.post_category', rowData).each(function () {
                var term_ids = $(this).text();

                if (term_ids) {
                    taxname = $(this).attr('id').replace('_' + id, '');
                    $('ul.' + taxname + '-checklist :checkbox', editRow).val(term_ids.split(','));
                }
            });

            //flat taxonomies
            $('.tags_input', rowData).each(function () {
                var terms = $(this).text(),
                    taxname = $(this).attr('id').replace('_' + id, ''),
                    textarea = $('textarea.tax_input_' + taxname, editRow);

                if (terms)
                    textarea.val(terms);
            });

            // handle the post status
            status = $('._status', rowData).text();
            if ('future' != status)
                $('select[name="_status"] option[value="future"]', editRow).remove();

            if ('private' == status) {
                $('input[name="keep_private"]', editRow).prop('checked', true);
                $('input.inline-edit-password-input').val('').prop('disabled', true);
            }

            // remove the current page and children from the parent dropdown
            pageOpt = $('select[name="post_parent"] option[value="' + id + '"]', editRow);
            if (pageOpt.length > 0) {
                pageLevel = pageOpt[0].className.split('-')[1];
                nextPage = pageOpt;
                while (pageLoop) {
                    nextPage = nextPage.next('option');
                    if (nextPage.length == 0)
                        break;
                    nextLevel = nextPage[0].className.split('-')[1];
                    if (nextLevel <= pageLevel) {
                        pageLoop = false;
                    } else {
                        nextPage.remove();
                        nextPage = pageOpt;
                    }
                }
                pageOpt.remove();
            }

            $(editRow).attr('id', 'edit-' + id).addClass('inline-editor').show();
            $('.ptitle', editRow).focus();
            return false;
        },
        save: function (id) {

            var params, fields, page = $('.post_status_page').val() || '';

            if (typeof (id) == 'object')
                id = this.getId(id);

            $('table.widefat .inline-edit-save .waiting').show();

            params = {
                action: 'inline-save',
                post_type: typenow,
                post_ID: id,
                edit_date: 'true',
                post_status: page
            };

            fields = $('#edit-' + id + ' :input').serialize();
            params = fields + '&' + $.param(params);
            // make ajax request
            $.post(ajaxurl, params,
                function (r) {
                    $('table.widefat .inline-edit-save .waiting').hide();
                    if (r) {
                        if (-1 != r.indexOf('<tr')) {
                            location.reload();
                        } else {
                            r = r.replace(/<.[^<>]*?>/g, '');
                            $('#edit-' + id + ' .inline-edit-save .error').html(r).show();
                        }
                    } else {
                        $('#edit-' + id + ' .inline-edit-save .error').html(inlineEditL10n.error).show();
                    }
                }
                , 'html');
            return false;
        },
        revert: function () {
            var id = $('table.widefat tr.inline-editor').attr('id');

            if (id) {
                $('table.widefat .inline-edit-save .waiting').hide();

                if ('bulk-edit' == id) {
                    $('table.widefat #bulk-edit').removeClass('inline-editor').hide();
                    $('#bulk-titles').html('');
                    $('#inlineedit').append($('#bulk-edit'));
                } else {
                    $('#' + id).remove();
                    id = id.substr(id.lastIndexOf('-') + 1);
                    $(this.what + id).show();
                }
            }

            return false;
        },
        getId: function (o) {
            var id = $(o).closest('tr').attr('id'),
                parts = id.split('-');
            return parts[parts.length - 1];
        }
    };

    $(document).ready(function () {
        inlineEditPost.init();
    });
})(jQuery);

jQuery(document).ready(function () {
    var inbox_obj = '';
    jQuery(document).on('click', '.inline-edit-save .save', function () {
        return inlineEditPost.save(this);
    });
    jQuery(document).on('click', '.inline-edit-save .cancel', function () {
        return inlineEditPost.revert(this);
    });

    jQuery(document).on('click', '.quick_sign_off', function (e) {
        e.preventDefault();
        inbox_obj = this;
        task_user = '';
        if (jQuery('#inbox_filter').length > 0) {
            task_user = jQuery('#inbox_filter').val();
        }
        data = {
            action: 'get_step_signoff_page',
            oasiswf: jQuery(this).attr('wfid'),
            post: jQuery(this).attr('postid'),
            task_user: task_user,
            parent_page: 'inbox',
            security: jQuery('#owf_inbox_ajax_nonce').val()
        };
        jQuery(this).parent().children('.loading').show();
        jQuery.get(ajaxurl, data, function (response) {
            if (response == -1) {
                jQuery(inbox_obj).parent().children('.loading').hide(); // Remove Loader
                return false;
            }
            var content = html_decode(response.data);
            jQuery('#step_submit_content').html(content);
            setTimeout('call_modal()', 100);
        });
        call_modal = function () {
            jQuery(inbox_obj).parent().children('.loading').hide();
            jQuery('#new-step-submit-div').owfmodal({
                onShow: function (dlg) {
                    jQuery('#simplemodal-container').css({
                        'max-height': '90%',
                        'top': '60px'
                    });
                    jQuery(dlg.wrap).css('overflow', 'auto'); // or try ;
                    // commented out, so that the above CSS can take effect
                    //jQuery.modal.update();
                }
            });
            jQuery('#due-date').datepicker({
                autoSize: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1950:2050',
                dateFormat: owf_workflow_inbox_vars.editDateFormat
            });
            if (jQuery('body > #ui-datepicker-div').length > 0) {
                jQuery('#ui-datepicker-div').wrap('<div class="ui-oasis" />');
            }
        };
    });

    jQuery(document).on('click', '.reassign', function (e) {
        e.preventDefault();
        inbox_obj = this;
        var task_user = '';
        if (jQuery('#inbox_filter').length > 0) {
            task_user = jQuery('#inbox_filter').val();
        }

        // if we are on post edit page
        if (task_user === '') {
            task_user = jQuery('#hi_task_user').val();
        }

        var wfid = jQuery(this).attr('wfid');
        var security = jQuery('#owf_inbox_ajax_nonce').val();
        var screen = 'inbox';
        // if we are on post edit page reset following parameters
        if (typeof wfid === 'undefined') {
            wfid = jQuery('#hi_oasiswf_id').val();
            screen = 'edit';
            security = jQuery('#owf_signoff_ajax_nonce').val();
        }

        if( jQuery(inbox_obj).hasClass('ow-elmentor-tool') && jQuery('#hi_oasiswf_id').length ) {
            wfid = jQuery('#hi_oasiswf_id').val();
            task_user = jQuery('#hi_task_user').val();
            screen = 'edit';
            security = jQuery('#owf_signoff_ajax_nonce').val();
        }

        data = {
            action: 'get_reassign_page',
            oasiswf: wfid,
            screen: screen,
            task_user: task_user,
            security: security
        };

        jQuery(this).parent().children('.loading').show();

        jQuery.post(ajaxurl, data, function (response) {
            if (response == -1) {
                jQuery(inbox_obj).parent().children('.loading').hide(); // Remove Loader
                return false;
            }
            if (response.success) {
                if (response.data.reassign_users == 0) {
                    jQuery(inbox_obj).parent().children('.loading').hide();
                    alert('No users found to reassign');
                    return false;
                } else {
                    var content = html_decode(response.data.reassign_users);
                    jQuery('#reassign-div').html(content);
                    setTimeout('call_reassign_modal()', 100);
                }
            }
        });

        call_reassign_modal = function () {
            jQuery(inbox_obj).parent().children('.loading').hide();
            jQuery('#reassgn-setting').owfmodal();
            jQuery('#simplemodal-container').css({ 'top': '60px' });
        };
    });

    jQuery(document).on('click', '.claim, .claim-and-edit', function (e) {
        e.preventDefault();
        var claim = jQuery(this);

        var elementId = claim.attr('id');
        var userId = claim.attr('userid');
        var actionid = claim.attr('actionid');
        
        if( claim.hasClass('elementor-title') ) {
            elementId = 'elementor-claim-top-bar';
            userId = owf_elementor_vars.current_user_id;
            actionid = owf_elementor_vars.current_history_id;
        }

        data = {
            action: 'claim_process',
            security: jQuery('#owf_claim_process_ajax_nonce').val(),
            buttonid: elementId,
            userid: userId,
            actionid: actionid
        };

        jQuery(this).parent().children('.loading').show();
        jQuery.post(ajaxurl, data, function (response) {
            if (response == -1) {
                claim.parent().children('.loading').hide();
                return false;
            }
            if (!response.success) {
                claim.parent().children('.loading').hide();
                var content = response.data.errorMessage;
                jQuery(content).owfmodal();
                jQuery('.simplemodal-close').hide(); // we do not want the user to close this modal window.
            }
            if (response.success) {
                if (elementId === 'claim-and-edit') {
                    window.location.href = response.data.url;
                } else {
                    // once claimed, move back to the mine inbox-filter page
                    window.location.href = window.location.href.replace('action=inbox-unclaimed', 'action=inbox-mine');
                    // location.reload();
                }
            }

        });
    });

    jQuery(document).on('click', '.claim-close', function () {
        jQuery.modal.close();
        location.reload();
    });

    jQuery('.post-com-count').click(function (e) {
        e.preventDefault();
        var inbox_obj = this;
        if (jQuery(this).children('span').html() == 0)
            return;
        var page_chk = jQuery(this).attr('real');
        data = {
            action: 'get_step_comment_page',
            actionid: jQuery(this).attr('actionid'),
            actionstatus: jQuery(this).attr('actionstatus'),
            comment: jQuery(this).attr('data-comment'),
            page_action: page_chk,
            post_id: jQuery(this).attr('post_id'),
            security: jQuery('#owf_inbox_ajax_nonce').val()
        };

        jQuery(this).parent().children('.loading').show();
        jQuery(this).hide();
        jQuery.post(ajaxurl, data, function (response) {//alert(response);
            if (response == -1) {
                jQuery(inbox_obj).parent().children('.loading').hide(); // Remove Loader
                jQuery(document).find('.post-com-count').show(); // Show Post Count
                return false;
            }
            var content = html_decode(response.data);
            jQuery(content).appendTo('body');
            jQuery('.ow-overlay').show(); // Enable background overlay
            jQuery('#ow-comment-popup').show();
        });

        call_comment_modal = function () {
            jQuery(inbox_obj).parent().children('.loading').hide();
            //jQuery("#stepcomment-setting").owfmodal();
            jQuery('#ow-editorial-readonly-comment-popup').owfmodal();
        };
    });

    jQuery( document ).on('click', '.nudge_poker', function(e) {
        e.preventDefault();

        var inbox_obj = this;

        var history_id = jQuery(this).attr('wfid'),
            user_id =  jQuery(this).attr('userid'),
            post_id =  jQuery(this).attr('postid');

        var getData = {
            action: 'workflow_nudge',
            history_id: history_id,
            user_id: user_id,
            post_id: post_id,
            security: jQuery('#owf_inbox_ajax_nonce').val()
        };

        jQuery(this).parent().children('.loading').show();
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: getData,
            success: function(data) {
                jQuery('#wpbody-content').find('.wrap > h2').after('<div id="ow_reminder_email_message" class="notice notice-success"><p>'+owf_workflow_inbox_vars.reminderEmailMessage+'</p></div>');
                setTimeout(function(){
                    jQuery('#ow_reminder_email_message').slideUp(function(){
                        jQuery(this).remove();
                    });
                }, 5000);
                jQuery(inbox_obj).parent().children('.loading').hide();
            }
        });

        console.log('nudge');

        return false;
    });

    jQuery(document).on('click', '.abort_workflow', function (e) {
        e.preventDefault();
        var inbox_obj = this;

        var history_id = jQuery(this).attr('wfid');
        if( jQuery(inbox_obj).hasClass('ow-elmentor-tool') && jQuery('#hi_oasiswf_id').length ) {
            history_id = jQuery('#hi_oasiswf_id').val();
        }

        modal_data = {
            action: 'workflow_abort_comments',
            history_id: history_id,
            security: jQuery('#owf_inbox_ajax_nonce').val(),
        };

        jQuery.post(ajaxurl, modal_data, function (response) {
            if (response == -1) {
                return false; // Invalid nonce
            }

            if (response.success) {
                var content = html_decode(response.data);
                jQuery(content).owfmodal();
                jQuery('#simplemodal-container').css({ 'top': '130px' });

                jQuery('#abortSave').on('click', function(e) {
                    e.preventDefault();
                    
                    var comments = jQuery('#abortComments').val();

                    if (!comments && owf_workflow_inbox_vars.isCommentsMandotory === 'mandatory') {
                        alert(owf_workflow_inbox_vars.emptyComments);
                        return false;
                    }
                    data = {
                        action: 'workflow_abort',
                        history_id: history_id,
                        comment: comments,
                        security: jQuery('#owf_inbox_ajax_nonce').val()
                    };
                    jQuery(this).hide();
                    jQuery(this).parent().children('.loading').show();
                    jQuery.post(ajaxurl, data, function (response) {
                        if (response == -1) { // nonce cannot be validated
                            jQuery(inbox_obj).parent().children('.loading').hide(); // Remove Loader
                            jQuery(inbox_obj).show(); // Show Label
                            return false;
                        }
                        if (response.success) {
                            if( jQuery(inbox_obj).hasClass('ow-elmentor-tool') ) {
                                jQuery.modal.close();
                                //If elementor editor active
                                //Hide signoff button
                                jQuery('#elementor-panel-footer-sub-menu-item-abort-workflow').hide();
                                swal(owf_submit_step_vars.elementorAbortText, {
                                    button: {
                                        text: owf_submit_step_vars.elementorExitButtonText,
                                        className: 'owf-elementor-redirect'
                                    },
                                }).then(function () {
                                    // Redirect the user to inbox page
                                    window.location.href = 'admin.php?page=oasiswf-inbox';
                                });
                            
                                if( jQuery('button.save_order').length !== 0 ) {
                                    jQuery('button.save_order').trigger('click');
                                }
                                if( jQuery('#save-post').length !== 0 ) {
                                    jQuery('#save-post').click();
                                }
                            } else {
                                 jQuery(inbox_obj).parent().children('.loading').hide();
                                 location.reload();
                             }
                        }
                    });
                });
            }
        });
    });

    jQuery(document).on('click', '#abortCancel', function () {
        jQuery.modal.close();
    });

    jQuery(document).on('click', '.compare-post-revision', function (e) {
        e.preventDefault();
        var revision_link = jQuery(this).attr('href');
        // open a popup window with 90% height and width. We will use this window to show the revision compare results.
        var h = (screen.height * 90) / 100;
        var w = (screen.width * 90) / 100;

        // link to the revision compare page
        window.open(revision_link, 'Revision_Compare', 'height=' + h + ',width=' + w + ',scrollbars=yes');
    });
});

