async function workflowSubmitWithACF(fnParam = '') {
    if (acf.validation.active == 0) {
        normalWorkFlowSubmit(fnParam);
        return 'valid';
    }

    var $form = jQuery('#post');
    if ($form.length === 0) {
        $form = jQuery('#editor');
    }

    acf.doAction('submit', $form);
    var data = acf.serialize($form);
    data.action = 'acf/validate_save_post';
    data = acf.prepareForAjax(data);

    /**
     * Replace the nonce value from the `data` variable with the global acf.data.nonce value. 
     * Otherwise, if the WPML classic editor sidebar has two nonces, 
     * they will overwrite this nonce value and this acf validation AJAX response issue, 
     * preventing ACF validating the post acf fields even if there any require field exists.
     * 
     * @since 9.9
     */
    data.nonce = acf.data.nonce;

    $form.busy = 1;

    try {
        const response = await jQuery.ajax({
            url: acf.get('ajaxurl'),
            type: 'POST',
            data: data,
            async: true,
        });

        /**
         * Check for an empty response. If the ACF validation AJAX returns an empty response, 
         * immediately return valid to avoid unnecessary issues with showing our popup to the front.
         * But with this, ACF validation will also be ignored.
         * 
         * @since 9.9
         */
        if (!response || response === "") {
            console.log('response', response);
            console.log('Empty response received');
            acf.validation.valid = true;

            if ($form.is("#post")) {
                normalWorkFlowSubmit(fnParam);
            }
            
            return 'valid';
        }

        /**
         * Check if response is string and response is zero
         * immediately return valid to avoid unnecessary issues with showing our popup to the front.
         * But with this, ACF validation will also be ignored.
         * 
         * @since 9.9
         */
        const json = typeof response === 'string' ? JSON.parse(response) : response;

        if (typeof json === 'string' && json == 0) {
            console.log('response', response);
            console.log("Received response 0");
            acf.validation.valid = true;

            if ($form.is("#post")) {
                normalWorkFlowSubmit(fnParam);
            }

            return 'valid';
        }

        if (json == -1 || !acf.isAjaxSuccess(json)) {
            acf.validation.valid = false;
            return 'invalid';
        }

        var jsonData = json.data;
        jsonData = acf.applyFilters('validation_complete', jsonData, $form);

        if (!jsonData || jsonData.valid || !jsonData.errors) {
            acf.validation.valid = true;

            if ($form.is("#post")) {
                normalWorkFlowSubmit(fnParam);
            }

            return 'valid';
        }

        acf.validation.valid = false;
        acf.validation.$trigger = null;

        var $scrollTo = false;
        var errorCount = 0;

        if (jsonData.errors && jsonData.errors.length > 0) {
            for (var i in jsonData.errors) {
                var error = jsonData.errors[i];
                if (!error.input) {
                    message += '. ' + error.message;
                    continue;
                }
                var $input = $form.find('[name="' + error.input + '"]').first();
                if (!$input.exists()) {
                    $input = $form.find('[name^="' + error.input + '"]').first();
                }
                if (!$input.exists()) {
                    continue;
                }
                errorCount++;
                var field = acf.getClosestField($input);
                field.showError(error.message);

                var errorMessage = acf.__('Validation failed');
                if (errorCount == 1) {
                    errorMessage += '. ' + acf.__('1 field requires attention');
                } else if (errorCount > 1) {
                    errorMessage += '. ' + acf.__('%d fields require attention').replace('%d', errorCount);
                }
                if (!$scrollTo) {
                    $scrollTo = field.$el;
                }
            }

            var errorMessage = acf.__('Validation failed');
            if (errorCount == 1) {
                errorMessage += '. ' + acf.__('1 field requires attention');
            } else if (errorCount > 1) {
                errorMessage += '. ' + acf.__('%d fields require attention').replace('%d', errorCount);
            }

            if ($form.notice) {
                $form.notice.update({
                    type: 'error',
                    text: errorMessage
                });
            } else {
                $form.notice = acf.newNotice({
                    type: 'error',
                    text: errorMessage,
                    target: $form
                });
            }

            if (!$scrollTo) {
                $scrollTo = $form.notice.$el;
            }

            setTimeout(function () {
                var scrollelm = jQuery('html, body');
                if ($form.is("#editor") && jQuery('.interface-interface-skeleton__content').length !== 0) {
                    scrollelm = jQuery('.interface-interface-skeleton__content');
                }
                scrollelm.animate({ scrollTop: $scrollTo.offset().top - (jQuery(window).height() / 2) }, 500);
            }, 10);
        }

        return 'invalid';
    } catch (error) {
        console.log('AJAX request failed', error);
        acf.validation.valid = false;
        return 'invalid';
    }
}