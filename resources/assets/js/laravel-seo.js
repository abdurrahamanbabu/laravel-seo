/**
 * Laravel SEO Package – Admin Form Enhancements
 * Requires jQuery (bundled by the host app or published separately).
 */
(function ($) {
    'use strict';

    function updateCharCount($input) {
        var $counter = $('.seo-char-count[data-target="' + $input.attr('id') + '"]');
        if (!$counter.length) return;

        var len = ($input.val() || '').length;
        var min = parseInt($counter.data('min'), 10) || 0;
        var max = parseInt($counter.data('max'), 10) || 999;

        $counter.find('.count').text(len);

        $counter.removeClass('seo-count-good seo-count-ok seo-count-poor');
        if (len >= min && len <= max) {
            $counter.addClass('seo-count-good');
        } else if (len > 0) {
            $counter.addClass('seo-count-ok');
        } else {
            $counter.addClass('seo-count-poor');
        }
    }

    function updateGooglePreview() {
        var title = $('.seo-title-input').val() || 'Page Title';
        var desc = $('.seo-description-input').val() || 'Meta description will appear here.';
        $('.seo-preview-title').text(title);
        $('.seo-preview-description').text(desc);
    }

    function syncKeywords() {
        var tags = [];
        $('.seo-keywords-tags .seo-keyword-tag').each(function () {
            var kw = $(this).attr('data-keyword') || $(this).contents().filter(function () {
                return this.nodeType === 3;
            }).text().trim();
            if (kw) tags.push(kw);
        });
        $('.seo-keywords-hidden').val(tags.join(','));
    }

    function addKeyword(keyword) {
        keyword = $.trim(keyword);
        if (!keyword) return;

        var exists = false;
        $('.seo-keywords-tags .seo-keyword-tag').each(function () {
            var existing = $(this).attr('data-keyword') || '';
            if (existing.toLowerCase() === keyword.toLowerCase()) {
                exists = true;
                return false;
            }
        });
        if (exists) return;

        var focus = ($('.seo-focus-keyword').val() || '').toLowerCase();
        var isFocus = focus && keyword.toLowerCase() === focus;

        var $tag = $('<span class="badge bg-secondary seo-keyword-tag me-1 mb-1' + (isFocus ? ' is-focus' : '') + '" data-keyword="' + $('<div>').text(keyword).html() + '">' +
            $('<div>').text(keyword).html() +
            ' <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove"></button></span>');

        $('.seo-keywords-tags').append($tag);
        syncKeywords();
    }

    function clearKeywords() {
        $('.seo-keywords-tags').empty();
        syncKeywords();
    }

    function initKeywords() {
        var raw = $('.seo-keywords-hidden').val() || '';
        if (raw && !$('.seo-keywords-tags .seo-keyword-tag').length) {
            raw.split(',').forEach(function (k) {
                addKeyword(k);
            });
        }

        $('.seo-keywords-field').on('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                var val = $(this).val().replace(/,/g, '');
                addKeyword(val);
                $(this).val('');
            }
        });

        $(document).on('click', '.seo-keyword-tag .btn-close', function () {
            $(this).closest('.seo-keyword-tag').remove();
            syncKeywords();
        });
    }

    function initCharCounters() {
        $('.seo-title-input, .seo-description-input').each(function () {
            updateCharCount($(this));
        });

        $(document).on('input', '.seo-title-input, .seo-description-input', function () {
            updateCharCount($(this));
            updateGooglePreview();
        });
    }

    function initImagePreviews() {
        $(document).on('change', '.seo-og-image', function () {
            var url = $(this).val();
            var $preview = $(this).closest('.mb-3').find('.seo-image-preview');
            if (url) {
                if ($preview.length) {
                    $preview.attr('src', url).show();
                } else {
                    $(this).after('<div class="mt-2"><img src="' + url + '" alt="OG preview" class="img-thumbnail seo-image-preview" style="max-height:120px;"></div>');
                }
            } else {
                $preview.hide();
            }
        });
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content')
            || $('input[name="_token"]').val()
            || '';
    }

    function resolvePageContent(selectors) {
        var content = '';
        var list = selectors || [];

        for (var i = 0; i < list.length; i++) {
            var $el = $(list[i]).first();
            if (!$el.length) continue;

            if ($el.is('textarea, input')) {
                content = $.trim($el.val() || '');
            } else if (typeof $el.data === 'function' && $el.data('seo-content')) {
                content = $.trim(String($el.data('seo-content')));
            } else {
                content = $.trim($el.text() || $el.html() || '');
            }

            if (content) break;
        }

        // TinyMCE / CKEditor common globals
        if (!content && window.tinymce && window.tinymce.activeEditor) {
            content = $.trim(window.tinymce.activeEditor.getContent({ format: 'text' }) || '');
        }
        if (!content && window.CKEDITOR) {
            for (var name in window.CKEDITOR.instances) {
                if (window.CKEDITOR.instances.hasOwnProperty(name)) {
                    content = $.trim(window.CKEDITOR.instances[name].getData() || '');
                    if (content) break;
                }
            }
        }

        return content;
    }

    function setAiMessage(type, text) {
        var $msg = $('.seo-ai-message');
        $msg.removeClass('d-none text-danger text-success text-muted')
            .addClass(type === 'error' ? 'text-danger' : (type === 'success' ? 'text-success' : 'text-muted'))
            .text(text);
    }

    function setAiLoading($btn, loading) {
        if (loading) {
            $btn.prop('disabled', true);
            $btn.find('.seo-ai-btn-label').addClass('d-none');
            $btn.find('.seo-ai-btn-loading').removeClass('d-none');
        } else {
            $btn.prop('disabled', false);
            $btn.find('.seo-ai-btn-label').removeClass('d-none');
            $btn.find('.seo-ai-btn-loading').addClass('d-none');
        }
    }

    function applyAiResult(data) {
        if (data.title) {
            $('.seo-title-input').val(data.title).trigger('input');
        }
        if (data.description) {
            $('.seo-description-input').val(data.description).trigger('input');
        }
        if (data.focus_keyword) {
            $('.seo-focus-keyword').val(data.focus_keyword);
        }
        if (data.keywords && data.keywords.length) {
            clearKeywords();
            data.keywords.forEach(function (kw) {
                addKeyword(kw);
            });
        }
        updateGooglePreview();
        $('.seo-title-input, .seo-description-input').each(function () {
            updateCharCount($(this));
        });
    }

    function initAiGenerate() {
        $(document).on('click', '.seo-ai-generate-btn', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var url = $btn.data('url');
            var selectors = $btn.data('selectors') || [];

            if (typeof selectors === 'string') {
                try { selectors = JSON.parse(selectors); } catch (err) { selectors = []; }
            }

            var content = resolvePageContent(selectors);
            if (!content || content.length < 20) {
                setAiMessage('error', 'Page content is empty or too short. Fill the content/body field first.');
                return;
            }

            setAiLoading($btn, true);
            setAiMessage('info', 'Generating SEO fields…');

            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                data: {
                    content: content,
                    title: $('.seo-title-input').val() || $('input[name="title"]').val() || '',
                    _token: csrfToken()
                }
            }).done(function (res) {
                if (res && res.success && res.data) {
                    applyAiResult(res.data);
                    setAiMessage('success', 'SEO fields generated successfully. Review and save when ready.');
                } else {
                    setAiMessage('error', (res && res.message) || 'Generation failed.');
                }
            }).fail(function (xhr) {
                var msg = 'Generation failed.';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message
                        || (xhr.responseJSON.errors && xhr.responseJSON.errors.content && xhr.responseJSON.errors.content[0])
                        || msg;
                }
                setAiMessage('error', msg);
            }).always(function () {
                setAiLoading($btn, false);
            });
        });
    }

    $(function () {
        if (!$('#laravel-seo-form').length) return;

        initCharCounters();
        initKeywords();
        initImagePreviews();
        initAiGenerate();
        updateGooglePreview();
    });
})(jQuery);
