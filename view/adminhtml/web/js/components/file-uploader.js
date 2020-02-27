/**
 * File: file-uploader.js
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2019 Lizard Media (http://lizardmedia.pl)
 */

'use strict';

define([
    'jquery',
    'underscore',
    'Magento_Downloadable/js/components/file-uploader'
], function ($, _, Component) {
    return Component.extend({
        /**
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
            this._super();
            this.$fileInput = fileInput;

            //We only add one more callback, other
            //are set up in vendor/magento/module-ui/view/base/web/js/form/element/file-uploader.js
            _.extend(this.uploaderConfig, {
                fail: this.onFail.bind(this)
            });

            $(fileInput).fileupload(this.uploaderConfig);

            return this;
        },

        /**
         * @param {Event} event
         * @param {Object} data
         * @return {VoidFunction}
         */
        onFail: function(event, data) {
            console.error(data.jqXHR.responseText);
            console.error(data.jqXHR.status);
        },
    });
});
