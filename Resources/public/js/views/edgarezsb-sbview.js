YUI.add('edgarezsb-sbview', function (Y) {
    Y.namespace('edgarEzSb');

    var CONTENT_REST_ID = 'data-content-rest-id',
        CONTENT_NAME = 'data-content-name';

    Y.edgarEzSb.SbView = Y.Base.create('edgarezsbSbView', Y.eZ.ServerSideView, [], {
        events: {
            '.edgarezsb-sb-location': {
                // tap is 'fast click' (touch friendly)
                'tap': '_navigateToLocation'
            },
            '.edgarezsb-install-assign-content-button': {
                'tap': '_pickContentLocation'
            },

        },

        initializer: function () {
            this.containerTemplate = '<div class="ez-view-edgarezsbsbview"/>';
        },

        _navigateToLocation: function (e) {
            var link = e.target;

            e.preventDefault(); // don't want the normal link behavior

            this.fire('navigateTo', {
                routeName: link.getData('route-name'),
                routeParams: {
                    id: link.getData('route-id'),
                    languageCode: link.getData('route-languagecode'),
                }
            });
        },

        _pickContentLocation: function (e) {
            var button = e.target,
                unsetLoading = Y.bind(this._uiUnsetUDWButtonLoading, this, button),
                that = this;

            e.preventDefault();
            this._uiSetUDWButtonLoading(button);
            this.fire('contentDiscover', {
                config: {
                    title: button.getAttribute('data-universaldiscovery-content-selection-title'),
                    cancelDiscoverHandler: unsetLoading,
                    multiple: true,
                    contentDiscoveredHandler: function(e) {
                        that._setContentLocation(button, this, e);
                    }
                },
            });
        },

        _uiSetUDWButtonLoading: function (button) {
            button.addClass('is-loading').set('disabled', true);
        },

        _uiUnsetUDWButtonLoading: function (button) {
            button.removeClass('is-loading').set('disabled', false);
        },

        _setContentLocation: function (button, udView, e) {
            var unsetLoading = Y.bind(this._uiUnsetUDWButtonLoading, this, button),
                selectedLocationsIds = Y.Array.map(e.selection, function(struct) {
                    return struct.location.get('id');
                }),
                udwConfigData = {
                    contentId: button.getAttribute(CONTENT_REST_ID),
                    contentName: button.getAttribute(CONTENT_NAME),
                    afterUpdateCallback: unsetLoading,
                    selectionType: 'Content',
                    subtreeIds: selectedLocationsIds,
                },
                udwAfterActiveChangeEvent = udView.onceAfter('activeChange', function() {
                    udwAfterActiveChangeEvent.detach();
                    setTimeout(Y.bind(function() {
                        this._fireContentDiscover(button, unsetLoading, udwConfigData);
                    }, this), 0);
                }, this);
        },

        _fireContentDiscover: function (button, unsetLoading, data) {
            this.fire('contentDiscover', {
                config: {
                    title: button.getAttribute('data-universaldiscovery-title'),
                    cancelDiscoverHandler: unsetLoading,
                    multiple: true,
                    data: data,
                },
            });
        },
    });
});
