(function($){
    // Generic AJAX submit for CBC Form Manager forms only.
    $(function(){
        var ns = window.cbcFormAjax || {};
        var ajaxUrl = ns.ajax_url || (window.ajaxurl || '/wp-admin/admin-ajax.php');
        var action = ns.action || 'cbc_form_submit';

        function ensureAlert($form, cls){
            var $el = $form.find('.' + cls);
            if (!$el.length) {
                $el = $('<div/>', { 'class': 'cbc-form-alert ' + cls, css: { display: 'none' } });
                $form.prepend($el);
            }
            return $el;
        }

        function ensureFieldError($row){
            var $err = $row.find('.cbc-form-error');
            if (!$err.length) {
                $err = $('<div/>', { 'class': 'cbc-form-error', css: { display: 'none' } });
                $row.append($err);
            }
            return $err;
        }

        $(document).on('submit', '.cbc-form', function(e){
            var $form = $(this);
            var isManagedForm = $form.is('[data-cbc-form-manager="1"]') || $form.find('input[name="_cbc_form_key"]').length > 0;

            if (!isManagedForm) {
                return;
            }

            if ($form.attr('data-cbc-ajax') === 'off') {
                return; // opt-out
            }

            // Allow overrides to cancel generic handling
            var ev = $.Event('cbcForm:beforeAjax');
            $form.trigger(ev, [{ form: $form[0] }]);
            if (ev.isDefaultPrevented()) {
                return; // custom handler took over
            }

            e.preventDefault();

            var $errorAlert = ensureAlert($form, 'cbc-form-alert-danger');
            var $successAlert = ensureAlert($form, 'cbc-form-alert-success');
            $errorAlert.hide().text('');
            $successAlert.hide().text('');
            $form.find('.cbc-form-error').hide().text('');

            var fd = new FormData($form[0]);
            fd.append('action', action);

            var $btn = $form.find('button[type="submit"]');
            var originalBtnText = $btn.text();
            $btn.prop('disabled', true).text(originalBtnText + '...');

            $.ajax({
                url: ajaxUrl,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false
            }).done(function(resp){
                var payload = { form: $form[0], response: resp };
                // WordPress returns { success: true|false, data: {...} }
                if (resp && resp.success) {
                    var msg = (resp.data && resp.data.success) ? resp.data.success : 'Submitted successfully.';
                    $successAlert.text(msg).show();
                    // Clear the form inputs except hidden fields
                    $form.find('input[type="text"], input[type="email"], input[type="number"], input[type="date"], input[type="url"], textarea').val('');
                    $form.trigger('cbcForm:afterAjax', [payload]);
                } else {
                    var data = resp ? resp.data : null;
                    if (data && data.errors) {
                        if (data.errors._global) {
                            $errorAlert.text(data.errors._global).show();
                        }
                        Object.keys(data.errors).forEach(function(name){
                            if (name === '_global') return;
                            var msg = data.errors[name];
                            var $field = $form.find('[name="' + name.replace(/"/g,'\\"') + '"]');
                            if ($field.length) {
                                var $row = $field.closest('.cbc-form-row, .cbc-row');
                                if (!$row.length) {
                                    $row = $field.parent();
                                }
                                var $err = ensureFieldError($row);
                                $err.text(msg).show();
                            }
                        });
                    } else if (data && data.message) {
                        $errorAlert.text(data.message).show();
                    } else {
                        $errorAlert.text('Something went wrong. Please try again.').show();
                    }
                    $form.trigger('cbcForm:afterAjax', [payload]);
                }
            }).fail(function(){
                var payload = { form: $form[0], response: null };
                $errorAlert.text('Request failed. Please check your connection and try again.').show();
                $form.trigger('cbcForm:afterAjax', [payload]);
            }).always(function(){
                $btn.prop('disabled', false).text(originalBtnText);
            });
        });
    });
})(jQuery);

