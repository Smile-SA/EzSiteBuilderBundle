YUI.add('edgarezsb-sbapplugin', function (Y) {
    Y.namespace('edgarEzSb.Plugin');

    Y.edgarEzSb.Plugin.SbAppPlugin = Y.Base.create('edgarezsbSbAppPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            var app = this.get('host'); // the plugged object is called host

            app.views.edgarezsbSbView = {
                type: Y.edgarEzSb.SbView,
            };

            app.route({
                name: "edgarEzSbSb",
                path: "/edgarezsb/sb",
                view: "edgarezsbSbView",
                service: Y.edgarEzSb.SbViewService, // the service will be used to load the necessary data
                // we want the navigationHub (top menu) but not the discoveryBar
                // (left bar), we can try different options
                sideViews: {'navigationHub': true, 'discoveryBar': false},
                callbacks: ['open', 'checkUser', 'handleSideViews', 'handleMainView'],
            });
        },
    }, {
        NS: 'edgarezsbTypeApp' // don't forget that
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.edgarEzSb.Plugin.SbAppPlugin, ['platformuiApp']
    );
});
