/*================================================================================
 * @name: Manage admin quiz operations
 * @author: Quiz Organizer Team
 * @demo: https://quizorganizer.com/
 * @requires jQuery 
 ================================================================================*/
var QZORG_CNQ, QZORG_QSTN, qzorgFilterTimeout;
(function ($) {

    var QZORG_CNQ = {
        quiz_name: function () {
            $this = $("#quiz_name");
            $this.parent().find('.qzorg-field-clue').css("color", "#646970");
            if ($this.val() == "") {
                $this.parent().find('.qzorg-field-clue').css("color", "red");
                $('.qzorg-each-tab-content').removeClass('active');
                $('.qzorg-each-tab-content#general').addClass('active');
                $('.qzorg-tab-menu-wrapper .qzorg-tab-button').removeClass('active');
                $('.qzorg-tab-menu-wrapper .qzorg-tab-button').first().addClass('active');
                $this.focus();
                return false;
            } else {
                return true;
            }
        },
        send_email_from: function () {
            $this = $("#send_email_from");
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            $this.parent().find('.qzorg-field-clue').css("color", "#646970");
            if ($this.val() == "" || !emailRegex.test($this.val())) {
                $this.parent().find('.qzorg-field-clue').css("color", "red");
                $('.qzorg-each-tab-content').removeClass('active');
                $('.qzorg-each-tab-content#emailconfiguration').addClass('active');
                $('.qzorg-tab-menu-wrapper .qzorg-tab-button').removeClass('active');
                $('.qzorg-tab-menu-wrapper .qzorg-tab-button').first().addClass('active');
                $this.focus();
                return false;
            } else {
                return true;
            }
        },
        quiz_list: function () {
            $('.qzorg-default-loader').show();
            var formData = new FormData($('#quiz')[0]);
            var qP = new URLSearchParams(formData).toString();
            $.ajax({
                url: WP_API_SETTINGS.root + 'quizorganizer/v1/quiz/index?' + qP,
                headers: { 'X-WP-Nonce': WP_API_SETTINGS.nonce },
                success: function (response) {
                    var $body = $('.quizzes-the-list');
                    $('.qzorg-default-loader').hide();
                    var template = _.template(jQuery('#tmpl-quizzes-list').html());
                    $body.html(template({ quizzes: response.quizzes }));
                    if (response.quizzes.length <= 0) {
                        $body.append('<tr><td colspan="7">' + qa_quiz_text.empty_quizzes + '</td></tr>');
                        $('.quizzes-the-list tr:last-child').css({ 'text-align': 'center' });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) { console.log(jqXHR, textStatus, errorThrown) }
            });
        },
        remove_quiz: function () {
            $('.qzorg-delete-spinner').show();
            let ids = [];
            $('.quizzes-the-list tr .qzorg-quiz-checkbox:checked').each(function () {
                ids.push($(this).data('id'));
            });
            if (ids.length === 0) { } else {
                $.post(
                    ajaxurl, {
                    data: ids,
                    action: 'qzorg_delete_quiz_text',
                    nonce: qa_quiz_text.remove_nonce,
                }, function (r) {
                    $('.qzorg-delete-spinner').hide();
                    $(".qzorg-delete-multiple-quiz-btn").prop("disabled", true);
                    if (r.success) {
                        $('.quizzes-the-list tr .qzorg-quiz-checkbox').each(function () {
                            if ($(this).is(':checked')) { $(this).parents('tr').remove(); }
                        });
                        $('.quizzes-the-list tr .qzorg-quiz-checkbox').prop('checked', false);
                        $('.wrap .wp-header-end').after(qzorg_show_note(r.data.message, 'success'));
                    } else {
                        $('.wrap .wp-header-end').after(qzorg_show_note(r.data.message, 'error'));
                    }
                }
                );
            }
        },
    };

    /* ----------------------------------------------------------- */
    var QZORG_QSTN = {

        new_answer: function (e) {
            var renderedTemplate = _.template(jQuery('#tmpl-qzorg-new-answer').html());
            e.parents('.qzorg-question-answers').find('.answer_list').append(renderedTemplate(qzorgModify.is_true));
            e.parents('.qzorg-question-answers').find('.answer_list .answer-fields').length === 1 ? QZORG_QSTN.prepend_label() : "";
        },
        remove_answer: function (obj) {
            let $index = obj.parents('.answer-fields').index();
            obj.parents('.answer-fields').remove();
            $index === 0 ? QZORG_QSTN.prepend_label() : "";
        },
        update_data: function ($this = "") {
            let new_obj = [];
            let question_obj = [];
            let $answers = $this == "" ? $('.answer_list') : $this.parents('.qzorg-question-answers').find('.answer_list');
            $answers.each(function (i, outer) {
                var eachobj = [];
                $accordion = $(this).parents('.qzorg-accordion-content');
                $(this).children().each(function (j, inner) {
                    let data_obj = {
                        answer: QZORG_QSTN.escapeHtml($(this).find('.answer-input').val().trim()),
                        points: $(this).find('.answer-point').val().trim() ? $(this).find('.answer-point').val().trim() : 0,
                        is_correct: $(this).find('.answer-correct').prop('checked') ? 1 : 0,
                    }
                    eachobj.push(data_obj)
                });
                new_obj = {
                    question_title_qzorgmessage: QZORG_QSTN.escapeHtml($accordion.find('.question_title').val()),
                    question_id: $accordion.data('id'),
                    question_type: $accordion.find('.qzorg-question-type').val(),
                    question_image: $accordion.find('.qzorg-question-image').val(),
                    answers: eachobj,
                    categories: [$accordion.find('.question_category').val()],
                    display_flex: $accordion.find('.qzorg-display-flex').prop('checked'),
                    required_question: $accordion.find('.qzorg-required-question').prop('checked'),
                    right_info_qzorgmessage: QZORG_QSTN.escapeHtml($accordion.find('.qzorg-right-info').val()),
                    wrong_info_qzorgmessage: QZORG_QSTN.escapeHtml($accordion.find('.qzorg-wrong-info').val()),
                    extra_info_qzorgmessage: QZORG_QSTN.escapeHtml($accordion.find('.qzorg-extra-info').val()),
                }
                question_obj.push(new_obj);
            });
            var encoded_data = JSON.stringify(new_obj);
            full_obj = {
                nonce: $('#qzorg_update_quiz_nonce').val(),
                questions: encoded_data,
                quiz_id: $('#quiz_id').val(),
            };
            $.post(
                ajaxurl, {
                data: full_obj,
                action: 'qzorg_update_questions_for_quiz'
            }, function (r) {
                $answers.each(function (d, outer) {
                    // $accordion.slideUp();
                    $(this).parents('.qzorg-accordion-parent').find('.question-title-span').text($accordion.find('.question_title').val());
                });
                r = JSON.parse(r);
                $('.update-data').siblings("span").remove();
                $accordion.find('.qzorg-save-question-text').text(r.message);
                setTimeout(function () { $accordion.find('.qzorg-save-question-text').text(''); }, 8000);
                if (r.success === 1) {
                    $('.wrap .wp-header-end').after(qzorg_show_note(r.message, 'success'));
                } else {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                    $('.wrap .wp-header-end').after(qzorg_show_note(r.message, 'warning'));
                }
            }
            );
        },
        remove_question: function ($wrapper) {
            $.post(
                ajaxurl, {
                data: [$wrapper.data('id')],
                nonce: qzorgModify.remove_question,
                action: 'qzorg_delete_question'
            }, function (r) {
                r = JSON.parse(r);
                $('.update-data').siblings("span").remove();
                if (r.success === 1) {
                    $wrapper.remove();
                    QZORG_QSTN.set_order();
                    $('.wrap .wp-header-end').after(qzorg_show_note(r.message, 'success'));
                } else {
                    $('.wrap .wp-header-end').after(qzorg_show_note(r.message, 'warning'));
                }
            }
            );
        },
        new_question: function ($this) {
            $.post(
                ajaxurl, {
                data: $('#quiz_id').val(),
                nonce: qzorgModify.question_nonce,
                action: 'qzorg_register_new_question',
            }, function (r) {
                $('.submit-data').siblings("span").remove();
                r = JSON.parse(r);
                if (r.success === 1) {
                    var renderedTemplate = _.template(jQuery('#tmpl-qzorg-new-question').html());
                    var add = {
                        id: r.id,
                        cs: r.categories,
                        qt: r.question_types,
                        ph: qzorgModify.new_question,
                        qtl: '',
                        sqt: r.selected_q_type,
                        qtls: [],
                        qii: '',
                        qiu: r.image_url,
                        qa: r.default_fields
                    };
                    $this.parents('.qzorg-page').find('.qzorg-accordion').append(renderedTemplate(add));
                    $this.parents('.qzorg-page').find('.qzorg-accordion .qzorg-accordion-parent').last().each(function () {
                        $(this).find('.qzorg-select-box').each(function () {
                            $(this).select2({});
                        });
                        $(this).find('.question-title-span').before('<span class="qzorg-drag-icon-inner dashicons dashicons-move"></span>');
                    });
                    QZORG_QSTN.set_order();
                    QZORG_QSTN.prepend_label();
                } else {
                    $('.submit-data').after(
                        '<span class="global_error">' + r.message + "</span>"
                    );
                }
            });
        },
        existing_question: function (val = "") {
            val = $(".qzorg-popup-filter-question").val();
            var $body = $('.questions-the-list');
            $body.parents('.qzorg-modal-content').find('.qzorg-default-loader').show();
            $.ajax({
                url: qzorgModify.root + 'quizorganizer/v1/question/index',
                headers: { 'X-WP-Nonce': qzorgModify.noncewp },
                data: {
                    filter: val,
                    perpage: $('#qzorg_qpp option:selected').val(),
                },
                success: function (response) {
                    if (response.length > 0) {
                        var template = _.template(jQuery('#tmpl-qn').html());
                        $body.html(template({ questions: response }));
                        setTimeout(function () {
                            $('#qzorg_questions_popup').bPopup({
                                transition: 'slideIn',
                                speed: 450,
                                modalClose: true,
                            });
                        }, 500);
                    } else {
                        $body.html('<tr><td colspan="2">' + qzorgModify.empty_questions + '</td></tr>');
                        $('.questions-the-list tr:last-child').css({ 'text-align': 'center' });
                    }
                    $body.parents('.qzorg-modal-content').find('.qzorg-default-loader').hide();
                },
                error: function (jqXHR, textStatus, errorThrown) { console.log(jqXHR, textStatus, errorThrown) }
            });
        },
        duplicate_question: function ($wrapper, $page, quiz_id) {
            $.post(
                ajaxurl, {
                data: $wrapper,
                quiz: quiz_id,
                nonce: qzorgModify.copy_question,
                action: 'qzorg_duplicate_question'
            }, function (r) {
                var renderedTemplate = _.template(jQuery('#tmpl-qzorg-new-question').html());
                var add = {
                    id: r.data.id,
                    cs: r.data.categories,
                    qt: r.data.question_types,
                    ph: r.data.question_title != "" ? r.data.question_title : qzorgModify.new_question,
                    qtl: r.data.question_title,
                    qa: r.data.question_answer,
                    qtls: r.data.question_tools,
                    sqt: r.data.selected_q_type,
                    qii: r.data.question_image,
                    qiu: r.data.image_url,
                };
                $page.find('.qzorg-accordion').append(renderedTemplate(add));
                $page.find('.qzorg-accordion .qzorg-accordion-parent').last().each(function () {
                    $(this).find('.qzorg-select-box').each(function () {
                        $(this).select2({});
                    });
                    $(this).find('.question-title-span').before('<span class="qzorg-drag-icon-inner dashicons dashicons-move"></span>');
                });
                var $response = $("#qzorg_questions_popup .qzorg-q-modal-response");
                $response.show();
                $response.html(qzorgModify.add_q_response);
                setTimeout(() => {
                    $response.hide();
                }, 3000);
                QZORG_QSTN.set_order();
                QZORG_QSTN.prepend_label();
            }
            );
        },
        set_order: function () {
            /* C */
            const p = $(".qzorg-accordion").toArray().map((j) => $(j).find(".qzorg-accordion-parent").toArray().map((element) => $(element).data("id")));
            return qzorgsaveOrder(JSON.stringify(p));
        },
        init_buttons: function () {
            $('.qzorg-remove-page').remove();
            $('.qzorg-new-page').remove();
            $('.qzorg-page-settings-top:not(:first)').append('<button class="qzorg-remove-page qzorg-global-submit" >' + qzorgModify.remove_page + '</button>');
            $('.qzorg-page-settings-bottom:last').append('<button class="qzorg-primary-bg qzorg-new-page qzorg-global-submit">' + qzorgModify.new_page + '</button>');
            QZORG_QSTN.prepend_label();
        },
        remove_page: function ($page) {
            /* C */
            const $ids = $page.find('.qzorg-accordion-parent').map((index, element) => $(element).data('id')).get();
            $.post(ajaxurl, { data: $ids, action: 'qzorg_delete_question', nonce: qzorgModify.remove_question }, (r) => {
                const { success } = JSON.parse(r);
                $page.remove();
                if (success === 1 && QZORG_QSTN.set_order() === 1) {
                    location.reload();
                }
            });
        },
        prepend_label: function () {
            $('.qzorg-ans-checkbox-label').remove();
            $('.qzorg-page .qzorg-question-answers .answer-fields:first-child').find('.qzorg-ans-checkbox').prepend('<label class="field-label qzorg-ans-checkbox-label">' + qzorgModify.check_if_correct + '</label>');
        },
        qzorg_manage_settings: function () {
            if ($('.qzorg-wrapper-modify .display_contact_form input:checked').val() == "yes") {
                $('.qzorg-wrapper-modify .contact_form_to_show').fadeIn();
            } else {
                $('.qzorg-wrapper-modify .contact_form_to_show').fadeOut();
            }

            if ($('.qzorg-wrapper-modify .login-require input:checked').val() == "yes") {
                $('.qzorg-wrapper-modify .login_require_qzorgmessage').fadeIn();
            } else {
                $('.qzorg-wrapper-modify .login_require_qzorgmessage').fadeOut();
            }
        },
        escapeHtml: function (text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };

            return text.replace(/[&<>"']/g, function (m) {
                return map[m];
            });
        }
    };

    if ($('.qzorg-unique-modify-page').length) {
        jQuery(document).ready(function ($) {
            if (typeof qzorgModify !== 'undefined' && qzorgModify !== null && qzorgModify !== '') {
                QZORG_QSTN.init_buttons();
                $('.qzorg-accordion .question-title-span').before('<span class="qzorg-drag-icon-inner dashicons dashicons-move"></span>');
                $('.qzorg-page .qzorg-page-settings-top strong').prepend('<span class="qzorg-drag-page-icon-inner">&#9776;</span>');
                $('.qzorg-accordion .qzorg-select-box, .qzorg-accordion .qzorg-question-input').each(function () {
                    const $this = $(this);
                    const $parent = $this.parents('.qzorg-accordion-parent');
                    if ($this.hasClass('qzorg-select-box')) {
                        $this.select2({});
                    } else if ($this.hasClass('qzorg-question-input') && $this.val() === '') {
                        $parent.find('.qzorg-accordion-header .question-title-span').text(qzorgModify.new_question);
                    }
                    if ($parent.find('.qzorg-question-type').val() == 'radio' || $parent.find('.qzorg-question-type').val() == 'checkbox') {
                        $parent.find('.qzorg-display-flex').parent().show();
                    } else {
                        $parent.find('.qzorg-display-flex').parent().hide();
                    }
                });
            } else if (typeof qzorgSettings !== 'undefined' && qzorgSettings !== null && qzorgSettings !== '') {
                $('.qzorg-color-picker').wpColorPicker();
            }
        });
    }

    if ($('.quiz-create-setting-form').length) {
        jQuery(document).ready(function ($) {
            if (typeof qzorgCreate !== 'undefined' && qzorgCreate !== null && qzorgCreate !== '') {
                $('.qzorg-color-picker').wpColorPicker();
            }
        });
    }

    if ($('.qzorg-quiz-list').length) {
        QZORG_CNQ.quiz_list();
    }

    /* C */
    $(".qzorg-create-quiz").click(function (e) {
        tinyMCE.triggerSave();
        qzorg_pd(e);
        if (!QZORG_CNQ.quiz_name() && !QZORG_CNQ.send_email_from()) { return false; }
        var formData = $("#quiz-create-setting-form").serializeArray();
        formData.forEach(function (item) {
            if (item.name.endsWith('_qzorgmessage')) {
                item.value = QZORG_QSTN.escapeHtml(item.value);
            }
        });
        var encoded_data = JSON.stringify(formData);
        $.post(ajaxurl, { data: { data: encoded_data, nonce: $('#qzorg_register_quiz_nonce').val() }, action: 'qzorg_update_quiz_text' }, function (r) {
            r = JSON.parse(r);
            $('.qzorg-create-quiz').siblings("span").remove();
            if (r.success === 1) {
                $('.wrap .wp-header-end').after(qzorg_show_note(r.message, "success"));
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                setTimeout(() => { window.location.href = r.redirect; }, 1500);
            } else {
                $('.qzorg-create-quiz').after('<span class="global_error">' + r.message + "</span>");
            }
        });
    });

    /* C */
    $(".qzorg-update-quiz").click(function (e) {
        tinyMCE.triggerSave();
        qzorg_pd(e);
        if (!QZORG_CNQ.quiz_name() || !QZORG_CNQ.send_email_from()) { return false; }
        var formData = $("#quiz-update-setting-form").serializeArray();
        formData.forEach(function (item) {
            if (item.name.endsWith('_qzorgmessage')) {
                item.value = QZORG_QSTN.escapeHtml(item.value);
            }
        });
        var encoded_data = JSON.stringify(formData);
        $.post(ajaxurl, { data: { data: encoded_data, nonce: $('#qzorg_update_quiz_nonce').val() }, action: 'qzorg_update_quiz_text' }, function (r) {
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            $('.qzorg-update-quiz').siblings("span").remove();
            if (r.success) {
                $('.custom-notice-parent').remove();
                $('.wrap .wp-header-end').after(qzorg_show_note(r.data.message, 'success'));
            } else {
                $('.qzorg-update-quiz').after('<span class="global_error">' + r.error.message + "</span>");
            }
        });
    });

    /* C */
    $(document).on('click', '.qzorg-accordion button.accordion-arrow', function (e) {
        qzorg_pd(e);
        const accordionContent = $(this).parents('.qzorg-accordion-header').next('.qzorg-accordion-content');
        $('.qzorg-accordion-content').not(accordionContent).slideUp();
        accordionContent.slideToggle();
    });

    $(document).on('click', '.qzorg-page .qzorg-page-settings-top .qzorg-remove-page', function (e) {
        qzorg_pd(e);
        if (confirm(qzorgModify.confirmation) == true) {
            QZORG_QSTN.remove_page($(this).parents('.qzorg-page'));
        }
    });

    /* C */
    $(document).on('click', '.qzorg-page .qzorg-page-settings-bottom .qzorg-new-page', function (e) {
        qzorg_pd(e);
        const lastAccordionDataPage = $('.qzorg-accordion:last').data('page');
        const renderedTemplate = _.template(jQuery('#tmpl-new-page').html());
        const add = { id: lastAccordionDataPage + 2 };
        $('.qzorg-page-wrapper').append(renderedTemplate(add));
        $(".qzorg-accordion").sortable({ handle: ".qzorg-drag-icon-inner", connectWith: ".qzorg-accordion", items: ".qzorg-accordion-parent" });
        $(this).remove();
    });


    $('.qzorg-question-type').change(function () {
        if ($(this).val() == 'radio' || $(this).val() == 'checkbox') {
            $(this).parents('.qzorg-accordion-parent').find('.qzorg-display-flex').parent().show();
        } else {
            $(this).parents('.qzorg-accordion-parent').find('.qzorg-display-flex').parent().hide();
        }
    });

    $('.qzorg-wrapper-modify').on('click', '.new-answer-button', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.new_answer($(this));
    });

    $('.qzorg-wrapper-modify').on('click', '.qzorg-remove-answer', function () {
        QZORG_QSTN.remove_answer($(this));
    });

    $('.qzorg-wrapper-modify').on('click', '.qzorg-question-delete', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.remove_question($(this).parents('.qzorg-accordion-parent'));
    });

    $('.qzorg-wrapper-modify').on('click', '.qzorg-duplicate-question', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.duplicate_question($(this).parents('.qzorg-accordion-parent').data('id'), $(this).parents('.qzorg-page'), $("#quiz_id").val());
    });

    $('.qzorg-custom').on('click', '.update-data', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.update_data();
    });

    $('.qzorg-custom').on('click', '.qzorg-new-question', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.new_question($(this));
    });

    $('.qzorg-custom').on('click', '.qzorg-existing-question', function (e) {
        qzorg_pd(e);
        $('input.qzorg-question-page').val($(this).parents('.qzorg-page').data('page'))
        QZORG_QSTN.existing_question();
    });

    $('.qzorg-bpopup-wrapper').on('input', '.qzorg-popup-filter-question', function (e) {
        var val = $(this).val();
        clearTimeout(qzorgFilterTimeout);
        qzorgFilterTimeout = setTimeout(function () {
            QZORG_QSTN.existing_question(val);
        }, 200);
    });

    $('.qzorg-bpopup-wrapper').on('change', '#qzorg_qpp', function (e) {
        QZORG_QSTN.existing_question();
    });

    $('.qzorg-quizzes-table').on('mouseenter', '.qzorg-each-tr', function (e) {
        $(this).find('.qzorg-row-sublinks').show();
    });

    $('.qzorg-quizzes-table').on('mouseleave', '.qzorg-each-tr', function (e) {
        $(this).find('.qzorg-row-sublinks').hide();
    });

    $('.qzorg-custom').on('click', '.qzorg-filter-quiz-btn', function (e) {
        qzorg_pd(e);
        QZORG_CNQ.quiz_list();
    });

    $('.qzorg-bpopup-wrapper').on('click', '.questions-the-list .qzorg-question-add', function (e) {
        qzorg_pd(e);
        QZORG_QSTN.duplicate_question($(this).data('id'), $('.qzorg-page[data-page="' + $('input.qzorg-question-page').val() + '"]'), $("#quiz_id").val());
    });

    $('.qzorg-accordion .qzorg-select-box').change(function () {

    });

    $(document).on('click', '.qzorg-save-single-question', function (e) {
        qzorg_pd(e);
        if ($(this).parents('.qzorg-accordion-content').find('.question_title').val().trim() == "") {
            alert(qzorgModify.blank_title);
            $(this).parents('.qzorg-accordion-content').find('.question_title').focus();
            return;
        }
        QZORG_QSTN.update_data($(this));
    });

    $(".qzorg-view-quiz").click(function () {
        var range = document.createRange();
        range.selectNodeContents(this);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    });

    /**
     * UPDATE QUIZ START
     */

    $(".qzorg-accordion").sortable({
        handle: ".qzorg-drag-icon-inner",
        connectWith: ".qzorg-accordion",
        items: ".qzorg-accordion-parent",
        update: function (event, ui) {
            var sourcePage = ui.item.closest(".qzorg-accordion").data("page");
            var targetPage = $(this).data("page");
            QZORG_QSTN.set_order();
        },
        start: function (event, ui) {
            ui.item.find(".qzorg-drag-icon-inner").css("cursor", "grabbing");
        },
        stop: function (event, ui) {
            ui.item.find(".qzorg-drag-icon-inner").css("cursor", "grab");
        }
    });

    // $('.qzorg-drag-page-icon-inner').hide();
    $(".qzorg-page-wrapper").sortable({
        handle: ".qzorg-drag-page-icon-inner",
        connectWith: ".qzorg-page",
        items: ".qzorg-page",
        update: function (event, ui) {
            QZORG_QSTN.set_order();
            QZORG_QSTN.init_buttons();
        },
        start: function (event, ui) {
            ui.item.find(".qzorg-drag-page-icon-inner").css("cursor", "grabbing");
        },
        stop: function (event, ui) {
            ui.item.find(".qzorg-drag-page-icon-inner").css("cursor", "grab");
        }
    });

    function qzorgsaveOrder(order) {
        $.ajax({
            url: qzorgModify.ajax_url,
            type: "POST",
            data: {
                action: "qzorg_set_question_order",
                order: order,
                nonce: qzorgModify.nonce,
                id: $('#quiz_id').val(),
            },
            success: function (r) { },
            error: function (jqXHR, textStatus, errorThrown) { console.log(textStatus); }
        });
        return 1;
    }

    // Trigger image upload
    $(document).on('click', '.qzorg-select-image-button', function (e) {
        qzorg_pd(e);

        let $preview = $(this).parents('.image-upload-wrapper').find('.qzorg-image-preview');

        let media_frame = wp.media({ multiple: false });

        media_frame.on('select', function () {
            var attachment = media_frame.state().get('selection').first().toJSON();
            $.ajax({
                url: qzorgModify.ajax_url,
                type: 'POST',
                data: {
                    action: 'qzorg_q_image_upload',
                    security: qzorgModify.qzorg_image_nonce,  // Nonce for security
                    attachment_id: attachment.id
                },
                success: function (response) {
                    if (response.success) {
                        $preview.parents('.image-upload-wrapper').find('.qzorg-question-image').val(response.data[1]);
                        $preview.attr('src', response.data[0]);
                    }
                },
                error: function () {
                    console.log('Image upload error.');
                }
            });
        });

        media_frame.open();
    });

    $("#qzorg_filter_question_title").on("input", function () {
        var userInput = $(this).val().toLowerCase().trim();
        var resultsFound = false; // Flag to check if any results are found
        var noResultFound = $("#no-results-message");

        $(".qzorg-page").each(function () {
            var page = $(this);
            var pageVisible = false; // Flag to check if any qzorg-accordion-parent is visible in the page

            page.find(".qzorg-accordion-parent").each(function () {
                var questionTitle = $(this).find(".question-title-span").text().toLowerCase();

                if (questionTitle.includes(userInput)) {
                    $(this).show();
                    pageVisible = true;
                    resultsFound = true;
                } else {
                    $(this).hide();
                }
            });

            // Show/hide the page based on visibility of qzorg-accordion-parent
            if (pageVisible) {
                page.show();
            } else {
                page.hide();
            }
        });

        // Show the no results message if no results are found
        if (!resultsFound) {
            noResultFound.show();
        } else {
            noResultFound.hide();
        }

    });

    $('#qzorg_filter_variables').on('input', function () {
        var searchTerm = $(this).val().toLowerCase();

        $('.qzorg-variables-table tbody tr').each(function () {
            var rowMatch = false;

            $(this).find('td').each(function () {
                var cellText = $(this).text().toLowerCase();

                if (cellText.includes(searchTerm)) {
                    rowMatch = true;
                    return false; // Break out of the inner loop
                }
            });

            if (rowMatch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });   

    $('.qzorg-wrapper-modify .url-field .quiz-backgroundimage').on('click', function (e) {
        qzorg_pd(e);
        let media_frame = wp.media({ multiple: false });
        let $parent_input = $(this).parents('.url-field').find("input");
        console.log($parent_input);
        media_frame.on('select', function () {
            var attachment = media_frame.state().get('selection').first().toJSON();
            $.ajax({
                url: qzorgSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'qzorg_q_image_upload',
                    security: qzorgSettings.qzorg_image_nonce,  // Nonce for security
                    attachment_id: attachment.id
                },
                success: function (response) {
                    if (response.success) {
                        $parent_input.val(response.data[0]);
                    }
                },
                error: function () {
                    console.log('Image upload error.');
                }
            });
        });
        media_frame.open();
    });

    /**
     * UPDATE QUIZ OVER
     */

    /**
     * QUIZ LIST PAGE START
     */

    $('.quizzes-the-list').on('click', '.qzorg-delete-quiz', function (e) {
        qzorg_pd(e);
        if (confirm(qa_quiz_text.confirmation) == true) {
            $(this).parents('tr').find('.qzorg-quiz-checkbox').prop('checked', true);
            QZORG_CNQ.remove_quiz();
        }
    });

    $('.qzorg-quizzes-table').on('click', 'tr .qzorg-check-all-quiz', function (e) {
        let enable = $(this).is(':checked') ? false : true;
        console.log(enable)
        $(".qzorg-delete-multiple-quiz-btn").prop("disabled", enable);
    })

    $('.qzorg-quizzes-table').on('click', '.quizzes-the-list tr .qzorg-quiz-checkbox', function (e) {
        var enable = true;
        if ($('.quizzes-the-list tr .qzorg-quiz-checkbox:checked').length === 0) { } else {
            enable = false;
        }
        $(".qzorg-delete-multiple-quiz-btn").prop("disabled", enable);
    });

    $('.qzorg-quiz-list').on('click', '.qzorg-delete-multiple-quiz-btn', function (e) {
        qzorg_pd(e);
        if (confirm(qa_quiz_text.confirmation) == true) {
            QZORG_CNQ.remove_quiz();
        }
    });

    $('.qzorg-question-form').submit(function (event) {
        event.preventDefault();
    });

    $('#qzorg_filter_question_title').on('keydown', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            return false;
        }
    });

    $('.qzorg-wrapper-modify').on('change', '.display_contact_form input[type="radio"]', function (e) {
        QZORG_QSTN.qzorg_manage_settings();
    });

    $('.qzorg-wrapper-modify').on('change', '.login-require input[type="radio"]', function (e) {
        QZORG_QSTN.qzorg_manage_settings();
    });

    $('.qzorg-quizzes-table').on('click', '.qzorg-quiz-shortcode-span', function (e) {
        var range = document.createRange();
        range.selectNodeContents(this);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        /*
        let original = $(this).text();
        let tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(original).select();
        document.execCommand('copy');
        tempInput.remove();
        $(this).text(qa_quiz_text.shortcode_cpy);
        let id = $(this).parents('tr').data('id');
        $(this).prop("disabled", true);
        */
        // setTimeout(function () {
        //     $('.shortcode_' + id).text(original);
        //     $('.shortcode_' + id).prop("disabled", false);
        // }, 1000);
    });

    QZORG_QSTN.qzorg_manage_settings();
    /**
     * QUIZ LIST PAGE OVER
     */

})(jQuery);