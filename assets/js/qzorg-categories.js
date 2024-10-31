/*================================================================================
 * @name: Manage categories at admin
 * @author: Quiz Organizer Team
 * @demo: https://quizorganizer.com/
 * @requires jQuery 
 ================================================================================*/
var QZORG_Category;
(function ($) {
    var QZORG_Category = {
        get_categories: function () {
            $('.qzorg-default-loader').show();
            var formData = new FormData($('#qzorg-category-form')[0]);
            var qP = new URLSearchParams(formData).toString();
            $.ajax({
                url: WP_API_SETTINGS.root + 'quizorganizer/v1/categories?' + qP,
                headers: { 'X-WP-Nonce': WP_API_SETTINGS.nonce },
                success: function (categories) {
                    var $body = $('.categories-the-list');
                    $('.qzorg-default-loader').hide();
                    if ('object' == typeof categories) {
                        var template = _.template(jQuery('#tmpl-cl').html());
                        $body.html(template({ categories: categories }));
                    } else {
                        $body.append('<tr class="qzorg-no-category"><td colspan="4">' + qa_cat_text.empty_categories + '</td></tr>');
                        $('.categories-the-list tr:last-child').css({ 'text-align': 'center' });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) { alert(textStatus); }
            });
        },
        get_category: function (id) {
            $('.qzorg-update-loader').show();
            $.ajax({
                url: WP_API_SETTINGS.root + 'quizorganizer/v1/categories/' + encodeURIComponent(id),
                headers: { 'X-WP-Nonce': WP_API_SETTINGS.nonce },
                success: function (data) {
                    $('.qzorg-update-loader').hide();
                    if (data) {
                        qzorg_setfield('cat_id', data.id);
                        qzorg_setfield('category_name', data.category_name);
                        qzorg_setfield('category_description', data.category_description);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) { alert(textStatus); }
            });
        },
        quick_cu: function () {
            let $input = $("#category_name");
            $input.removeClass('qzorg-required-b');
            var cat_nonce = $('#qzorg_create_update_cat_nonce').val();
            var id = $('#cat_id').val();
            let full_obj = {
                category_name: $input.val(),
                id: id,
                nonce: cat_nonce,
                category_description: $('textarea#category_description').val(),
            };
            if ($input.val().trim().length > 0) {
                $.ajax({
                    type: WP_API_SETTINGS.p,
                    data: full_obj,
                    url: WP_API_SETTINGS.root + 'quizorganizer/v1/categories/' + id,
                    headers: { 'X-WP-Nonce': WP_API_SETTINGS.nonce },
                    success: function (data) {
                        if (id == "") {
                            full_obj.id = data.data.id;
                            $body = $('.categories-the-list');
                            var categories = _.template(jQuery('#tmpl-cn').html());
                            $body.append(categories(full_obj));
                            $('.wrap .wp-header-end').after(qzorg_show_note(data.data.message, 'success'));
                        } else {
                            $('.category_name_' + id).text($input.val());
                            $('.category_description_' + id).text($('textarea#category_description').val());
                            $('.wrap .wp-header-end').after(qzorg_show_note(data.data));
                        }
                        $('.categories-the-list').find(".qzorg-no-category").remove();
                    },
                    error: function (jqXHR, textStatus, errorThrown) { alert(textStatus); }
                });
                $('#qzorg_cat_update').bPopup().close();
            } else {
                $input.val('');
                $input.addClass('qzorg-required-b');
            }
            setTimeout(function () { $input.removeClass('qzorg-required-b'); }, 4000);
        },
        remove_category: function ($this) {
            $.ajax({
                type: WP_API_SETTINGS.d,
                url: WP_API_SETTINGS.root + 'quizorganizer/v1/categories/delete/' + $this.data('id'),
                headers: { 'X-WP-Nonce': WP_API_SETTINGS.nonce },
                success: function (data) {
                    if (data.success) {
                        $this.parents('tr').remove();
                        $('.wrap .wp-header-end').after(qzorg_show_note(data.data, "success"));
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) { alert(textStatus); }
            });
        }
    }

    QZORG_Category.get_categories();

    $('.qzorg-custom').on('click', '.qzorg-cat-new-btn', function (e) {
        qzorg_pd(e);
        qzorg_setfield('category_name'); qzorg_setfield('cat_id'); qzorg_setfield('category_description');
        $('.update-category-btn').text(qa_cat_text.new_category);
        $('.qzorg-cat-modal-top-text').text(qa_cat_text.new_category + ' ' + qa_cat_text.category_btn);
        $('#qzorg_cat_update').bPopup();
    });

    $('.qzorg-custom').on('click', '.qzorg-category-edit', function (e) {
        $('.update-category-btn').text(qa_cat_text.edit_category);
        $('.qzorg-cat-modal-top-text').text(qa_cat_text.edit_category + ' ' + qa_cat_text.category_btn);
        qzorg_category_update($(this));
    });

    $('.qzorg-custom').on('click', '.qzorg-category-remove', function (e) {
        qzorg_pd(e);
        if (confirm(qa_cat_text.confirmation) == true) {
            QZORG_Category.remove_category($(this));
        }
    });

    $(document).on('click', '.update-category-btn', function (e) {
        qzorg_pd(e);
        QZORG_Category.quick_cu();
    });

    $('.qzorg-custom').on('click', '.qzorg-filter-cat-btn', function (e) {
        qzorg_pd(e);
        QZORG_Category.get_categories();
    });

    function qzorg_category_update($this) {
        $('#qzorg_cat_update').bPopup({
            onOpen: function () { QZORG_Category.get_category($this.data('id')); },
            onClose: function () { qzorg_setfield('category_name'); qzorg_setfield('cat_id'); qzorg_setfield('category_description'); }
        });
    }

})(jQuery);
