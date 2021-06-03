require.config({
    paths: {
        knob: "../addons/btpanel/js/jquery.knob",
        codemirror: "../addons/btpanel/js/codemirror",
        loading: "../addons/btpanel/js/loading",
    },
    shim: {
        knob: ['jquery'],
        codemirror: ['css!../addons/btpanel/js/codemirror.css'],
        loading: ['jquery']
    }
});
