YUI.add('edgarezsb-sbview', function (Y) {
    Y.namespace('edgarEzSb');

    var REST_ID = 'data-rest-id',
        NAME = 'data-name';

    Y.edgarEzSb.SbView = Y.Base.create('edgarezsbSbView', Y.eZ.ServerSideView, [], {
        events: {
            '.edgarezsb-sb-location': {
                'tap': '_navigateToLocation'
            },
            '.edgarezsb-install-pick-location-content-button': {
                'tap': '_pickLocationLimitation'
            },
            '.edgarezsb-install-pick-location-media-button': {
                'tap': '_pickLocationLimitation'
            },
            '.edgarezsb-install-pick-location-user-button': {
                'tap': '_pickLocationLimitation'
            }
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

        _pickLocationLimitation: function (e) {
            var button = e.target,
                unsetLoading = Y.bind(this._uiUnsetUDWButtonLoading, this, button);

            e.preventDefault();
            this._uiSetUDWButtonLoading(button);
            this.fire('contentDiscover', {
                config: {
                    title: button.getAttribute('data-universaldiscovery-title'),
                    cancelDiscoverHandler: unsetLoading,
                    multiple: false,
                    contentDiscoveredHandler: Y.bind(this._setLocationLimitation, this, button),
                },
            });
        },

        _setLocationLimitation: function (button, e) {
            this._emptyLocationList(button);
            this._emptyLocationIdHiddenInput(button);

            this._addLocationToDisplayList(button, e.selection.contentInfo);
            this._addLocationIdToHiddenInput(button, e.selection.location.get('locationId'));

            this._uiUnsetUDWButtonLoading(button);
        },

        _emptyLocationList: function (button) {
            var selectedLocationList = this.get('container').one(button.getAttribute('data-selected-location-list-selector'));

            selectedLocationList.empty();
        },

        _emptyLocationIdHiddenInput: function (button) {
            var locationInput = this.get('container').one(button.getAttribute('data-location-input-selector'));

            locationInput.setAttribute('value', '');
        },

        _addLocationToDisplayList: function (button, contentInfo) {
            var selectedLocationList = this.get('container').one(button.getAttribute('data-selected-location-list-selector'));

            selectedLocationList.appendChild(Y.Node.create('<li>' + Y.Escape.html(contentInfo.get('name')) + '</li>'));
        },

        _addLocationIdToHiddenInput: function (button, locationId) {
            var locationInput = this.get('container').one(button.getAttribute('data-location-input-selector')),
                existingLocationsStr = locationInput.getAttribute('value');

            if (existingLocationsStr.length > 0) {
                locationInput.setAttribute('value', existingLocationsStr.concat(',', locationId));
            } else {
                locationInput.setAttribute('value', locationId);
            }
        },

        _uiSetUDWButtonLoading: function (button) {
            button.addClass('is-loading').set('disabled', true);
        },

        _uiUnsetUDWButtonLoading: function (button) {
            button.removeClass('is-loading').set('disabled', false);
        },
    });
});
