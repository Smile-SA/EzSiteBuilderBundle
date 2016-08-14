YUI.add('edgarezsb-dashboardviewservice', function (Y) {
    Y.namespace('edgarEzSb');

    Y.edgarEzSb.DashboardViewService = Y.Base.create('edgarezsbDashboardViewService', Y.eZ.ServerSideViewService, [], {
        initializer: function () {
            this.on('*:navigateTo', function (e) {
                this.get('app').navigateTo(
                    e.routeName,
                    e.routeParams
                );
            });
        },

        _load: function (callback) {
            uri = this.get('app').get('apiRoot') + 'dashboard';

            Y.io(uri, {
                method: 'GET',
                on: {
                    success: function (tId, response) {
                        this._parseResponse(response);
                        callback(this);
                    },
                    failure: this._handleLoadFailure,
                },
                context: this,
            });
        },
    });
});
