/*================================================================================
 * @name: Manage quiz Front
 * @author: Quiz Organizer Team
 * @demo: https://quizorganizer.com/
 * @requires jQuery 
 ================================================================================
*/
var QZORG_SPIRIT;
var QZORG_QUIZ;
var qzorgTimeout;
var qzorgQuestions;

(function ($) {

    QZORG_SPIRIT = {
        timerIntervals: {},

        load: function () {
            var qzorgWrappers = $('.quiz-organizer-wrapper');
            qzorgWrappers.each(function () {
                var qzorgWrapper = $(this);
                var qzorgPageWrappers = qzorgWrapper.find('.qzorg-page-wrapper');
                var qzorgPrevButton = qzorgWrapper.find('.qzorg-previous-button');
                var qzorgNextButton = qzorgWrapper.find('.qzorg-next-button');
                var qzorgSubmitButton = qzorgWrapper.find('.qzorg-submit-btn');
                var qzorgQuizIntroWrapper = qzorgWrapper.find('.qzorg-quiz-intro-wrapper');
                var qzorgStartQuizButton = qzorgWrapper.find('.qzorg-start-quiz-button');
                var qzorgQuizDuration = qzorg_quiz[qzorgWrapper.find('.qzorg-quiz-form').data('id')].quiz_duration;
                var qzorgCurrentPageIndex = 0;

                function loadCurrentPage() {
                    qzorgPageWrappers.hide();
                    qzorgPageWrappers.eq(qzorgCurrentPageIndex).addClass(qzorg_quiz[qzorgWrapper.find('.qzorg-quiz-form').data('id')].page_animation);
                    qzorgPageWrappers.eq(qzorgCurrentPageIndex).show();

                    QZORG_SPIRIT.updateButtons(qzorgWrapper);
                }

                if (qzorgQuizIntroWrapper.length > 0) {
                    qzorgPageWrappers.hide();
                    qzorgStartQuizButton.show();
                    qzorgNextButton.hide();
                    qzorgSubmitButton.hide();
                    qzorgPrevButton.hide();
                } else if (qzorgPageWrappers.length > 1) {
                    QZORG_QUIZ.loading(qzorgWrapper.find('.qzorg-quiz-form').data('id'));
                    if (qzorgQuizDuration !== 0 && qzorgQuizDuration > 0) {
                        QZORG_QUIZ.loading2(qzorgWrapper.find('.qzorg-quiz-form').data('id'));
                    }
                    qzorgPrevButton.hide();
                    loadCurrentPage();
                    QZORG_SPIRIT.previewButtons(qzorgWrapper);
                    QZORG_SPIRIT.manageButtons(qzorgWrapper);
                } else {
                    QZORG_QUIZ.loading(qzorgWrapper.find('.qzorg-quiz-form').data('id'));
                    if (qzorgQuizDuration !== 0 && qzorgQuizDuration > 0) {
                        QZORG_QUIZ.loading2(qzorgWrapper.find('.qzorg-quiz-form').data('id'));
                    }
                    qzorgPrevButton.hide();
                    qzorgNextButton.hide();
                    qzorgSubmitButton.show();
                    loadCurrentPage();
                    qzorgWrapper.find('.qzorg-pagination-wrapper').css({ 'justify-content': 'end' });
                }
                QZORG_SPIRIT.updateProgressBar(qzorgWrapper);
            });
        },

        qzorgNextPageBefore: function ($page) {
            let spirit = $page.data("spirit");
            let spirit_container = jQuery('.qzorg-quiz-wrapper' + spirit);
            return QZORG_SPIRIT.qzorgVerifyBeforeSubmit(spirit_container);
        },

        manageQuestions: function ($obj) {
            const questionId = $obj.parents('.qzorg-question-wrapper').data('id');
            if (!qzorgQuestions.get('questions').includes(questionId)) {
                qzorgQuestions.set('questions', [...qzorgQuestions.get('questions'), questionId]);
            }
        },

        displayInstantResults: function ($object, check, extra, value, submit = 'no') {
            this.manageQuestions($object);
            var $instant_parents = $object.parents('.qzorg-question-wrapper');
            $instant_parents.find('.qzorg-question-m').css({ 'border': '2px solid #FFFFFF' });
            var instant_answer = qzorg_quiz[$object.parents('.qzorg-quiz-form').data('id')].instant_answer;
            var add_new_class = qzorg_quiz[$object.parents('.qzorg-quiz-form').data('id')].add_answer_class;
            if (instant_answer == 'yes' || submit == 'yes' || add_new_class == 'yes') {
                let userop = value;
                clearTimeout(qzorgTimeout);
                qzorgTimeout = setTimeout(function () {
                    $.ajax({
                        url: spirit_form.ajaxurl,
                        type: 'POST',
                        data: {
                            action: spirit_form.instant_result,
                            userop: userop,
                            question_id: $instant_parents.data('id'),
                            check: check,
                            d_message: instant_answer,
                            other: qzorg_quiz[$object.parents('.qzorg-quiz-form').data('id')].all_correct_answer,
                            nonce: spirit_form.nonce,
                        },
                        success: function (r) {
                            if (instant_answer == "yes") {
                                QZORG_SPIRIT.displayMessages(r, $instant_parents.find('.qzorg-question-m'));
                            }
                            if (submit == 'yes' && r.data.status == 'false') {
                                if (check == 0 || check == 4) {
                                    var incorrect_s = $object.parents(".qzorg-quiz-container").data("spirit");
                                    qzorgShowQuizResult($('.qzorg-quiz-wrapper' + incorrect_s));
                                }
                            }
                            if ($object.hasClass('qzorg-default-input-radio') && qzorg_quiz[$object.parents('.qzorg-quiz-form').data('id')].add_answer_class == "yes") {
                                QZORG_SPIRIT.incorrectCorrectClass(r, $instant_parents);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(textStatus);
                        }
                    });
                }, parseInt(extra * 1000));
            }
        },

        displayMessages: function (r, parent) {
            parent.html(r.data.info);
            if (r.data.status == 'true') {
                parent.removeClass('qzorg-wrong-answer');
                parent.addClass('qzorg-right-answer');
                parent.css({ 'border': '2px solid' });
            } else {
                parent.addClass('qzorg-wrong-answer');
                parent.removeClass('qzorg-right-answer');
                parent.css({ 'border': '2px solid' });
            }
        },

        disableProcess: function (input, id) {
            if (qzorg_quiz[id].disable_answer_options == "yes") {
                input.closest('.qzorg-class-radio-wrapper').find('.qzorg-default-input-radio').not(input).prop('disabled', true);
            }
        },

        qzorgVerifyBeforeSubmit: function (container) {
            var $validForm = container.find('.qzorg-quiz-form');
            var validFormId = $validForm.data('id');
            var $isValidate = true;
            var $pageElements = container.find("[class^='qzorg-page-']");
            var $currentQueue = $pageElements.filter(":visible");
            $currentQueue.find(".qzorg-question-wrapper").each(function () {
                var dataId = $(this).attr('data-id');
                if (qzorg_quiz[validFormId].more_settings.hasOwnProperty(dataId)) {
                    if (qzorg_quiz[validFormId].more_settings[dataId][0]) {
                        console.log(jQuery('.qzorg-question-wrapper.qzorg-class-' + dataId));
                        var $isLooping = jQuery('.qzorg-question-wrapper.qzorg-class-' + dataId);
                        var $qzorg_md = $isLooping.find('.qzorg-question-m');
                        var isFilled = false;
                        if ($isLooping.find("input[type='text']").is("*")) {
                            if ($isLooping.find("input[type='text']").val() !== "") {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("textarea").is("*")) {
                            if ($isLooping.find("textarea").val() !== "") {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("input[type='number']").is("*")) {
                            if ($isLooping.find("input[type='number']").val() !== "") {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("input[type='date']").is("*")) {
                            if ($isLooping.find("input[type='date']").val() !== "") {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("input[type='radio']").is("*")) {
                            if ($isLooping.find("input[type='radio']:checked").is("*")) {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("input[type='checkbox']").is("*")) {
                            if ($isLooping.find("input[type='checkbox']:checked").is("*")) {
                                isFilled = true;
                            }
                        } else if ($isLooping.find("select").is("*")) {
                            if ($isLooping.find("select").val() !== "") {
                                isFilled = true;
                            }
                        }
                        if (isFilled) {
                            $isLooping.removeClass('qzorg-is-required');
                            $qzorg_md.removeClass('qzorg-is-required-text');
                            $qzorg_md.html('');
                        } else {
                            $isLooping.addClass('qzorg-is-required');
                            $qzorg_md.addClass('qzorg-is-required-text');
                            $qzorg_md.html(qzorg_quiz[validFormId].required_field_text)
                            $isValidate = false;
                        }
                    }
                }
            });
            if ($isValidate == false) {
                $('html, body').animate({
                    scrollTop: $validForm.offset().top - 100
                }, 500);
            }
            return $isValidate;
        },

        incorrectCorrectClass: function (r, parent) {
            if (r.data.status == 'false') {
                parent.removeClass('qzorg-correct-answer');
                parent.addClass('qzorg-incorrect-answer');
            } else {
                parent.addClass('qzorg-correct-answer');
                parent.removeClass('qzorg-incorrect-answer');
            }
        },

        showFirstPage: function ($wrapper) {
            qzorgCurrentPageIndex = 0;
            this.loadCurrentPage($wrapper, qzorgCurrentPageIndex);
        },

        loadCurrentPage: function ($wrapper, qzorgCurrentPageIndex) {
            var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
            qzorgPageWrappers.hide();
            if (qzorgCurrentPageIndex < 0) {
                qzorgCurrentPageIndex = 0;
            }
            qzorgPageWrappers.eq(qzorgCurrentPageIndex).addClass(qzorg_quiz[$wrapper.find('.qzorg-quiz-form').data('id')].page_animation);
            qzorgPageWrappers.eq(qzorgCurrentPageIndex).show();

            this.updateButtons($wrapper);
        },

        updateButtons: function ($wrapper) {
            var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
            var qzorgNextButton = $wrapper.find('.qzorg-next-button');
            var qzorgSubmitButton = $wrapper.find('.qzorg-submit-btn');
            qzorgCurrentPageIndex = qzorgPageWrappers.index($wrapper.find('.qzorg-page-wrapper:visible'));
            if (qzorgCurrentPageIndex < 0) {
                qzorgCurrentPageIndex = 0;
            }
            if (qzorgCurrentPageIndex === qzorgPageWrappers.length - 1) {
                qzorgNextButton.hide();
                qzorgSubmitButton.show();
            } else {
                qzorgNextButton.show();
                qzorgSubmitButton.hide();
            }
        },

        previewButtons: function ($wrapper) {
            var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
            var qzorgQuizIntroWrapper = $wrapper.find('.qzorg-quiz-intro-wrapper');
            var qzorgPrevButton = $wrapper.find('.qzorg-previous-button');
            qzorgCurrentPageIndex = qzorgPageWrappers.index($wrapper.find('.qzorg-page-wrapper:visible'));
            if (qzorgQuizIntroWrapper.length == 0) {
                if (qzorgCurrentPageIndex == 0) {
                    qzorgPrevButton.hide();
                } else {
                    qzorgPrevButton.show();
                }
            }
        },

        manageButtons: function ($wrapper) {
            var qzorgQuizIntroWrapper = $wrapper.find('.qzorg-quiz-intro-wrapper');
            if (!qzorgQuizIntroWrapper.length > 0) {
                if ($wrapper.find('.qzorg-pagination-wrapper .qzorg-pagination-button:visible').length <= 1) {
                    $wrapper.find('.qzorg-pagination-wrapper').css({ 'justify-content': 'end' });
                } else {
                    $wrapper.find('.qzorg-pagination-wrapper').css({ 'justify-content': 'space-between' });
                }
            }
        },

        updateProgressBar: function ($wrapper) {
            var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
            var qzorgQuizIntroWrapper = $wrapper.find('.qzorg-quiz-intro-wrapper');
            qzorgCurrentPageIndex = qzorgPageWrappers.index($wrapper.find('.qzorg-page-wrapper:visible'));
            var totalPages = qzorgPageWrappers.length;
            let $qzorgPercentage = $wrapper.find('.qzorg-progress-line');
            if ($qzorgPercentage.length) {
                if (qzorgQuizIntroWrapper.length > 0 && qzorgQuizIntroWrapper.is(':visible')) {
                    var progressLine = 0;
                } else {
                    var progressLine = Math.floor(((qzorgCurrentPageIndex + 1) / totalPages) * 100);
                }
                $qzorgPercentage.css('width', progressLine + '%');
                $qzorgPercentage.attr('per', progressLine + '%');
            }
        },
        questionModel: Backbone.Model.extend({
            defaults: {
                questions: []
            }
        }),

    };

    QZORG_QUIZ = {
        timerInterval: [],
        countDown: [],
        loading: function (quizId) {
            // Check if there's a timer value in local storage
            const storedSeconds = this.getTimerSeconds(quizId);
            this.updateTimerDisplay(quizId, storedSeconds);
            this.beginningTimer(quizId, storedSeconds);
        },

        beginningTimer: function (quizId, seconds) {
            if (!this.timerInterval[quizId]) {
                this.timerInterval[quizId] = setInterval(() => {
                    seconds++;
                    this.updateTimerDisplay(quizId, seconds);
                    // Store the updated seconds in local storage
                    localStorage.setItem('timerSeconds' + quizId, seconds);
                }, 1000);
            }
        },

        getTimerSeconds: function (quiz_id) {
            var stredSeconds = localStorage.getItem('timerSeconds' + quiz_id);
            if (stredSeconds === null) {
                stredSeconds = 0;
            }
            return parseInt(stredSeconds);
        },

        updateTimerDisplay: function (quiz_id, seconds) {
            $input = $("input#qzorgquiztimer" + quiz_id);
            $input.val(seconds);
        },

        resumeTimer: function ($form) {
            const getSeconds = this.getTimerSeconds($form.data('spirit'));
            this.beginningTimer($form, getSeconds);
        },

        stopTimer: function (quiz_id) {
            if (this.timerInterval[quiz_id]) {
                clearInterval(this.timerInterval[quiz_id]);
                this.timerInterval[quiz_id] = null;
            }
        },

        resetTimer: function ($form) {
            clearInterval(this.timerInterval[$form.data('spirit')]);
            this.timerInterval[$form.data('spirit')] = null;
            localStorage.removeItem('timerSeconds' + $form.data('spirit'));
        },

        loading2: function (quizId) {
            const storedSeconds = this.getRemainSeconds(quizId);
            this.updateRemainDisplay(quizId, storedSeconds);
            this.startCountdown(quizId, storedSeconds);
        },

        getRemainSeconds: function (quiz_id) {
            var stredSeconds = localStorage.getItem('countdownSeconds' + quiz_id);
            if (stredSeconds === null) {
                var qzorgQuizDurationM = qzorg_quiz[quiz_id].quiz_duration;
                var stredSeconds = qzorgQuizDurationM * 60;
            }
            return parseInt(stredSeconds);
        },

        updateRemainDisplay: function (quiz_id, seconds) {
            $input = $("input#qzorgquizcountdown" + quiz_id);
            $input.val(seconds);
            var qzorgTimerE = $("#qzorgtimer" + quiz_id);
            var qzorgH = Math.floor(seconds / 3600);
            var qzorgM = Math.floor((seconds % 3600) / 60);
            var qzorgRemainingS = seconds % 60;
            qzorgTimerE.find('.qzorg-time-hour').text(("0" + qzorgH).slice(-2) + ":");
            qzorgTimerE.find('.qzorg-time-minute').text(("0" + qzorgM).slice(-2) + ":");
            qzorgTimerE.find('.qzorg-time-second').text(("0" + qzorgRemainingS).slice(-2));
        },

        resumeRemainTimer: function ($form) {
            const getSeconds = this.getRemainSeconds($form.data('spirit'));
            this.startCountdown($form.data('spirit'), getSeconds);
        },

        stopRemianTimer: function (quiz_id) {
            if (this.countDown[quiz_id]) {
                clearInterval(this.countDown[quiz_id]);
                this.countDown[quiz_id] = null;
            }
        },

        resetRemainTimer: function ($form) {
            clearInterval(this.countDown[$form.data('spirit')]);
            this.countDown[$form.data('spirit')] = null;
            localStorage.removeItem('countdownSeconds' + $form.data('spirit'));
        },

        startCountdown: function (quiz_id, seconds) {
            if (!this.countDown[quiz_id]) {
                this.countDown[quiz_id] = setInterval(() => {
                    if (seconds <= 0) {
                        clearInterval(this.countDown[quiz_id]);
                        alert('Time is over!');
                        if (qzorg_quiz[quiz_id].quiz_auto_submit === "yes") {
                            qzorgShowQuizResult($('.qzorg-quiz-wrapper' + quiz_id));
                        }
                    } else {
                        seconds--;
                        this.updateRemainDisplay(quiz_id, seconds);
                        localStorage.setItem('countdownSeconds' + quiz_id, seconds);
                    }
                }, 1000);
            }
        }

    };

    QZORG_SPIRIT.load();
    $('.restart-quiz-button').hide();

    $('.quiz-organizer-wrapper').on('change', '.qzorg-default-input-radio, .qzorg-default-select', function (e) {
        if ($(this).hasClass('qzorg-default-input-radio')) {
            QZORG_SPIRIT.disableProcess($(this), $(this).parents(".qzorg-page-wrapper").data("extra"));
        }
        QZORG_SPIRIT.displayInstantResults(jQuery(this), 0, 0, jQuery(this).val(), qzorg_quiz[$(this).parents(".qzorg-page-wrapper").data("extra")].submit_quiz_if_incorrect);
    });

    jQuery('.quiz-organizer-wrapper').on('input', '.qzorg-default-input-pragraph, qzorg-default-input-text', function (e) {
        QZORG_SPIRIT.displayInstantResults(jQuery(this), 1, 2, jQuery(this).val());
    });

    jQuery('.quiz-organizer-wrapper').on('change', '.qzorg-default-input-checkbox', function (e) {
        var userOptions = [];
        jQuery(this).parents('.qzorg-class-checkbox-wrapper').find('.qzorg-class-checkbox:checked').each(function () {
            userOptions.push(jQuery(this).val());
        });
        QZORG_SPIRIT.displayInstantResults(jQuery(this), 4, 1, userOptions, qzorg_quiz[$(this).parents(".qzorg-page-wrapper").data("extra")].submit_quiz_if_incorrect);
    });

    jQuery('.quiz-organizer-wrapper').on('change', '.qzorg-default-input-date', function (e) {
        QZORG_SPIRIT.displayInstantResults(jQuery(this), 2, 2, jQuery(this).val());
    });

    jQuery('.quiz-organizer-wrapper').on('input', '.qzorg-default-input-number', function (e) {
        QZORG_SPIRIT.displayInstantResults(jQuery(this), 3, 2, jQuery(this).val());
    });

    jQuery('.quiz-organizer-wrapper').find('.qzorg-check-results').on('click', function (e) {
        qzorgPd(e);
        let spirit = jQuery(this).parents(".qzorg-quiz-container").data("spirit");
        let spirit_container = jQuery('.qzorg-quiz-wrapper' + spirit);
        if (QZORG_SPIRIT.qzorgVerifyBeforeSubmit(spirit_container)) {
            qzorgShowQuizResult(spirit_container);
        }
    });

    jQuery('.quiz-organizer-wrapper').find('.qzorg-previous-button').on('click', function (e) {
        var $wrapper = jQuery(this).parents(".quiz-organizer-wrapper");
        qzorgPd(e);
        $(document).trigger('qzorg_before_prevbuttonclick', [$wrapper.find('.qzorg-quiz-form').data('id')]);
        jQuery('html, body').animate({
            scrollTop: jQuery('.qzorg-quiz-wrapper' + $wrapper.find('.qzorg-quiz-form').data('id')).offset().top - 120
        }, 500);
        var qzorgQuizIntroWrapper = $wrapper.find('.qzorg-quiz-intro-wrapper');
        var qzorgPrevButton = $wrapper.find('.qzorg-previous-button');
        var qzorgStartQuizButton = $wrapper.find('.qzorg-start-quiz-button');
        var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
        var qzorgNextButton = $wrapper.find('.qzorg-next-button');
        var qzorgSubmitButton = $wrapper.find('.qzorg-submit-btn');
        var qzorgCurrentPageIndex = qzorgPageWrappers.index($wrapper.find('.qzorg-page-wrapper:visible'));
        if (qzorgCurrentPageIndex > 0) {
            qzorgCurrentPageIndex--;
            QZORG_SPIRIT.loadCurrentPage($wrapper, qzorgCurrentPageIndex);
            QZORG_SPIRIT.previewButtons($wrapper);
        } else if (qzorgQuizIntroWrapper.length > 0) {
            qzorgPrevButton.hide();
            qzorgQuizIntroWrapper.show();
            qzorgStartQuizButton.show();
            qzorgPageWrappers.hide();
            qzorgNextButton.hide();
            qzorgSubmitButton.hide();
            QZORG_QUIZ.stopTimer($wrapper.find('.qzorg-quiz-form').data('id'));
            QZORG_QUIZ.stopRemianTimer($wrapper.find('.qzorg-quiz-form').data('id'));
        }
        QZORG_SPIRIT.manageButtons($wrapper);
        QZORG_SPIRIT.updateProgressBar($wrapper);
        $(document).trigger('qzorg_after_prevbuttonclick', [$wrapper.find('.qzorg-quiz-form').data('id')]);
    });

    jQuery('.quiz-organizer-wrapper').find('.qzorg-next-button').on('click', function (e) {
        qzorgPd(e);
        var $wrapper = jQuery(this).parents(".quiz-organizer-wrapper");
        if (!QZORG_SPIRIT.qzorgNextPageBefore($wrapper)) { return false; }
        jQuery('html, body').animate({
            scrollTop: jQuery('.qzorg-quiz-wrapper' + $wrapper.find('.qzorg-quiz-form').data('id')).offset().top - 120
        }, 500);
        var qzorgPageWrappers = $wrapper.find('.qzorg-page-wrapper');
        var qzorgCurrentPageIndex = qzorgPageWrappers.index($wrapper.find('.qzorg-page-wrapper:visible'));
        $(document).trigger('qzorg_before_nextbuttonclick', [$wrapper.find('.qzorg-quiz-form').data('id')]);
        qzorgPageWrappers.removeClass(qzorg_quiz[$wrapper.find('.qzorg-quiz-form').data('id')].page_animation);
        if (qzorgCurrentPageIndex < qzorgPageWrappers.length - 1) {
            qzorgCurrentPageIndex++;
            QZORG_SPIRIT.loadCurrentPage($wrapper, qzorgCurrentPageIndex);
        }
        QZORG_SPIRIT.previewButtons($wrapper);
        QZORG_SPIRIT.manageButtons($wrapper);
        QZORG_SPIRIT.updateProgressBar($wrapper);
        $(document).trigger('qzorg_after_nextbuttonclick', [$wrapper.find('.qzorg-quiz-form').data('id')]);
    });

    jQuery('.quiz-organizer-wrapper').find('.qzorg-start-quiz-button').on('click', function (e) {
        qzorgPd(e);
        var $wrapper = jQuery(this).parents(".quiz-organizer-wrapper");
        var qzorgQuizIntroWrapper = $wrapper.find('.qzorg-quiz-intro-wrapper');
        var qzorgPrevButton = $wrapper.find('.qzorg-previous-button');
        var quizId = $wrapper.find('.qzorg-quiz-form').data('id');
        var qzorgQuizDuration = qzorg_quiz[quizId].quiz_duration;
        QZORG_QUIZ.loading($wrapper.find('.qzorg-quiz-form').data('id'));
        if (qzorgQuizDuration !== 0 && qzorgQuizDuration > 0) {
            QZORG_QUIZ.loading2($wrapper.find('.qzorg-quiz-form').data('id'));
        }
        qzorgQuizIntroWrapper.hide();
        qzorgPrevButton.show();
        QZORG_SPIRIT.showFirstPage($wrapper);
        QZORG_SPIRIT.updateProgressBar($wrapper);
    });

    jQuery('.quiz-organizer-wrapper').find('.qzorg-restart-quiz').on('click', function (e) {
        qzorgPd(e);
        location.reload();
    });

}(jQuery));
var qzorgQuestions = new QZORG_SPIRIT.questionModel({});

function qzorgPd(e) {
    e.preventDefault();
}

function qzorg_fd() {
    return new FormData();
}

function qzorgShowQuizResult($spirit) {
    $ = jQuery;
    $form = $spirit.find('.qzorg-quiz-form');
    $form.find('.qzorg-check-results').prop('disabled', true);
    $spirit.find('.qzorg-quiz-default-loader-parent').show();
    $formId = $form.data('id');
    serializeArray = $form.serializeArray();
    var formData = qzorg_fd();
    formData.append('action', spirit_form.action);
    formData.append('spirit', $formId);
    formData.append('nonce', jQuery("#qzorg_quizform_" + $formId).val());
    formData.append('model_questions', qzorgQuestions.get('questions'));
    formData.append('quiz_time', localStorage.getItem('timerSeconds' + $formId));
    formData.append('qzorg-username', $('#qzorg-username').length ? $('#qzorg-username').val() : "");
    formData.append('qzorg-useremail', $('#qzorg-useremail').length ? $('#qzorg-useremail').val() : "");
    jQuery.each(serializeArray, function (j, i) {
        formData.append(i.name, i.value);
    });
    jQuery.ajax({
        url: spirit_form.ajaxurl,
        data: formData,
        type: 'POST',
        contentType: false,
        processData: false,
        success: function (response) {
            QZORG_QUIZ.resetTimer($spirit);
            QZORG_QUIZ.resetRemainTimer($spirit);
            $spirit.find('.qzorg-quiz-default-loader-parent').hide();
            if (typeof response.data.url === 'undefined' && response.data.display) {
                $spirit.find(".display_results-" + $formId).html(response.data.display);
                $form.remove();
                if ("yes" == qzorg_quiz[$formId].required_math_js) { MathJax.typesetPromise(); }
                if ("yes" == qzorg_quiz[$formId].display_restart_button) { $spirit.find('.restart-quiz-button').show(); }
            } else {
                if (typeof response.data.url !== 'undefined' && response.data.url && response.data.url !== '') { window.location.href = response.data.url; }
            }
            jQuery('html, body').animate({
                scrollTop: jQuery('.display_results-' + $formId).offset().top - 120
            }, 1000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $spirit.find('.qzorg-quiz-default-loader-parent').hide();
            if (jqXHR.responseJSON.data.message && jqXHR.responseJSON.data.overlimit == 1) {
                $spirit.find(".display_results-" + $formId).html(jqXHR.responseJSON.data.message);
            }
            if (jqXHR.responseJSON.data.message && jqXHR.responseJSON.data.nonce == 1) {
                $spirit.find(".display_results-" + $formId).html(jqXHR.responseJSON.data.message);
            }
            $form.remove();
        }
    });
}