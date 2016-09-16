YUI.add('smileezsb-sbapplugin', function (Y) {
    Y.namespace('smileEzSb.Plugin');

    Y.smileEzSb.Plugin.SbAppPlugin = Y.Base.create('smileezsbSbAppPlugin', Y.Plugin.Base, [], {
        initializer: function () {
            var app = this.get('host');

            app.views.smileezsbSbView = {
                type: Y.smileEzSb.SbView
            };

            app.route({
                name: "smileEzSbSb",
                path: "/smileezsb/sb",
                view: "smileezsbSbView",
                service: Y.smileEzSb.SbViewService,
                sideViews: {'navigationHub': true, 'discoveryBar': false},
                callbacks: ['open', 'checkUser', 'handleSideViews', 'handleMainView'],
            });
        }
    }, {
        NS: 'smileezsbTypeApp' // don't forget that
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.smileEzSb.Plugin.SbAppPlugin, ['platformuiApp']
    );
});
